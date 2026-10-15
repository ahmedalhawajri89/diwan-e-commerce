<?php

use Anthropic\Client;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Role;
use App\Models\User;
use App\Support\Ai\ClaudeDescriptionDrafter;
use App\Support\Ai\DescriptionDrafter;
use App\Support\Ai\DraftFailed;
use App\Support\Ai\ProductFacts;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Description drafts: written from recorded facts only, put in the form for
 * the operator to read, and absent from the panel until a key is set. No
 * test here reaches the network.
 */
function draftAdmin(): User
{
    $user = User::factory()->create();
    $user->roles()->attach(Role::firstOrCreate(['name' => 'admin']));

    return $user;
}

/** A drafter that records the facts it was given and answers at once. */
function fakeDrafter(?array $answer = null, ?string $fails = null): object
{
    $fake = new class($answer, $fails) implements DescriptionDrafter
    {
        public ?array $facts = null;

        public function __construct(private ?array $answer, private ?string $fails) {}

        public function available(): bool
        {
            return true;
        }

        public function draft(array $facts): array
        {
            $this->facts = $facts;
            if ($this->fails) {
                throw new DraftFailed($this->fails);
            }

            return $this->answer ?? ['en' => 'A low oak stool.', 'ar' => 'مقعد منخفض من البلوط.', 'name_ar' => 'مقعد'];
        }
    };
    app()->instance(DescriptionDrafter::class, $fake);

    return $fake;
}

/** A PSR-18 client standing in for the Anthropic API: it keeps the request and returns $body. */
function fakeTransport(array $body, int $status = 200): object
{
    return new class($body, $status) implements ClientInterface
    {
        public ?RequestInterface $request = null;

        public function __construct(private array $body, private int $status) {}

        public function sendRequest(RequestInterface $request): ResponseInterface
        {
            $this->request = $request;

            return new Response($this->status, ['Content-Type' => 'application/json'], json_encode($this->body));
        }
    };
}

function claudeReply(array $content, string $stop = 'end_turn'): array
{
    return [
        'id' => 'msg_test', 'type' => 'message', 'role' => 'assistant', 'model' => 'claude-opus-5-5',
        'content' => $content, 'stop_reason' => $stop, 'stop_sequence' => null,
        'usage' => ['input_tokens' => 900, 'output_tokens' => 300, 'cache_read_input_tokens' => 0, 'cache_creation_input_tokens' => 0],
    ];
}

it('gathers only the facts the shop has recorded, and never the price or stock', function () {
    $category = Category::factory()->create(['name' => 'Living room']);
    $product = Product::factory()->create(['category_id' => $category->id, 'name' => 'Oak stool', 'name_ar' => null]);
    ProductVariant::factory()->create(['product_id' => $product->id, 'color' => 'Walnut', 'price' => 120, 'stock' => 9, 'width_cm' => 40, 'depth_cm' => null, 'height_cm' => 45]);

    $facts = ProductFacts::fromProduct($product->fresh());

    expect($facts)->toMatchArray(['name' => 'Oak stool', 'category' => 'Living room', 'colours' => ['Walnut']])
        ->not->toHaveKeys(['price', 'stock', 'dimensions_cm', 'name_ar']);

    // All three measured: the dimensions are a fact too.
    $product->variants()->update(['depth_cm' => 38]);
    expect(ProductFacts::fromProduct($product->fresh())['dimensions_cm'])->toBe(['width' => 40, 'depth' => 38, 'height' => 45]);
});

it('drafts both descriptions for the form, from the saved piece or what was typed', function () {
    $admin = draftAdmin();
    $fake = fakeDrafter();
    $category = Category::factory()->create(['name' => 'Living room']);

    $this->actingAs($admin)->postJson(route('admin.products.draft'), ['name' => 'Oak stool', 'category_id' => $category->id, 'width_cm' => 40, 'depth_cm' => 38, 'height_cm' => 45])
        ->assertOk()
        ->assertExactJson(['en' => 'A low oak stool.', 'ar' => 'مقعد منخفض من البلوط.', 'name_ar' => 'مقعد']);

    expect($fake->facts)->toMatchArray(['name' => 'Oak stool', 'category' => 'Living room', 'dimensions_cm' => ['width' => 40, 'depth' => 38, 'height' => 45]]);
});

it('wants a name, and keeps customers out', function () {
    $admin = draftAdmin();
    fakeDrafter();

    $this->actingAs($admin)->postJson(route('admin.products.draft'), [])->assertUnprocessable()->assertJsonValidationErrors('name');

    $customer = User::factory()->create();
    $customer->roles()->attach(Role::firstOrCreate(['name' => 'customer']));
    $this->actingAs($customer)->post(route('admin.products.draft'), ['name' => 'Oak stool'])->assertRedirect(route('furnish.index'));
});

it('says plainly when drafting is not set up, or could not be done', function () {
    $admin = draftAdmin();
    config(['services.anthropic.key' => null]);

    $this->actingAs($admin)->postJson(route('admin.products.draft'), ['name' => 'Oak stool'])
        ->assertStatus(503)
        ->assertJson(['message' => __('admin.draft.unavailable')]);

    fakeDrafter(fails: 'declined');
    $this->actingAs($admin)->postJson(route('admin.products.draft'), ['name' => 'Oak stool'])
        ->assertStatus(502)
        ->assertJson(['message' => __('admin.draft.declined')]);
});

it('shows the draft button only once a key is set', function () {
    $admin = draftAdmin();
    config(['services.anthropic.key' => null]);

    $this->actingAs($admin)->get(route('admin.products.create'))->assertDontSee('x-data="descriptionDraft"', false);

    config(['services.anthropic.key' => 'sk-ant-test']);
    $this->actingAs($admin)->get(route('admin.products.create'))->assertSee('x-data="descriptionDraft"', false)->assertSee(__('admin.draft.button'));
});

it('asks Claude for a schema-shaped draft under the shop\'s fixed rules, and reads it', function () {
    $transport = fakeTransport(claudeReply([
        ['type' => 'text', 'text' => json_encode(['en' => 'A low stool.', 'ar' => 'مقعد منخفض.', 'name_ar' => 'مقعد'], JSON_UNESCAPED_UNICODE)],
    ]));
    $drafter = new ClaudeDescriptionDrafter('sk-ant-test', 'claude-opus-5-5', new Client(apiKey: 'sk-ant-test', requestOptions: ['transporter' => $transport, 'maxRetries' => 0]));

    $draft = $drafter->draft(['name' => 'Oak stool', 'colours' => ['Walnut']]);
    expect($draft)->toBe(['en' => 'A low stool.', 'ar' => 'مقعد منخفض.', 'name_ar' => 'مقعد']);

    $sent = json_decode((string) $transport->request->getBody(), true);
    expect($sent['model'])->toBe('claude-opus-5-5')
        ->and($sent['output_config']['effort'])->toBe('medium')
        ->and($sent['output_config']['format']['type'])->toBe('json_schema')
        ->and($sent['system'][0]['cache_control'])->toBe(['type' => 'ephemeral'])
        ->and($sent['system'][0]['text'])->toContain('Use only the facts given')
        ->and($sent['fallbacks'])->toBe('default')
        ->and($sent['messages'][0]['content'][0]['text'])->toContain('Oak stool')->not->toContain('price')
        ->and($transport->request->getHeaderLine('anthropic-beta'))->toContain('server-side-fallback-2026-07-01');
});

it('turns a refusal or an empty reply into a reason the panel can show', function () {
    $refused = new ClaudeDescriptionDrafter('sk-ant-test', 'claude-opus-5-5', new Client(apiKey: 'sk-ant-test', requestOptions: ['transporter' => fakeTransport(claudeReply([], 'refusal')), 'maxRetries' => 0]));
    expect(fn () => $refused->draft(['name' => 'Oak stool']))->toThrow(fn (DraftFailed $e) => expect($e->reason)->toBe('declined'));

    $empty = new ClaudeDescriptionDrafter('sk-ant-test', 'claude-opus-5-5', new Client(apiKey: 'sk-ant-test', requestOptions: ['transporter' => fakeTransport(claudeReply([['type' => 'text', 'text' => 'not json']])), 'maxRetries' => 0]));
    expect(fn () => $empty->draft(['name' => 'Oak stool']))->toThrow(fn (DraftFailed $e) => expect($e->reason)->toBe('failed'));

    $down = new ClaudeDescriptionDrafter('sk-ant-test', 'claude-opus-5-5', new Client(apiKey: 'sk-ant-test', requestOptions: ['transporter' => fakeTransport(['type' => 'error', 'error' => ['type' => 'overloaded_error', 'message' => 'Overloaded']], 529), 'maxRetries' => 0]));
    expect(fn () => $down->draft(['name' => 'Oak stool']))->toThrow(fn (DraftFailed $e) => expect($e->reason)->toBe('failed'));
});
