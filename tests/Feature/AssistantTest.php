<?php

use Anthropic\Client;
use App\Http\Controllers\AssistantController;
use App\Http\Controllers\RoomController;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Support\Ai\CatalogueTools;
use App\Support\Ai\ClaudeShoppingAssistant;
use App\Support\Ai\DraftFailed;
use App\Support\Ai\ShoppingAssistant;
use App\Support\Money;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * «Ask Diwan»: answers from the live catalogue, pieces drawn from rows,
 * bounded per visitor and per day, absent without a key. No test here
 * reaches the network.
 */
function sofa(string $name, float $price, array $dims = [200, 90, 80], string $status = 'active', string $colour = 'Grey'): Product
{
    $category = Category::firstOrCreate(['slug' => 'living-room'], ['name' => 'Living room']);
    $p = Product::factory()->create(['category_id' => $category->id, 'name' => $name, 'status' => $status]);
    ProductVariant::factory()->create(['product_id' => $p->id, 'price' => $price, 'stock' => 4, 'color' => $colour,
        'width_cm' => $dims[0], 'depth_cm' => $dims[1], 'height_cm' => $dims[2]]);

    return $p;
}

/** An assistant that answers at once with the ids given. */
function fakeAssistant(array $ids = [], string $reply = 'Here are two.', ?string $fails = null): object
{
    $fake = new class($ids, $reply, $fails) implements ShoppingAssistant
    {
        public array $calls = [];

        public function __construct(private array $ids, private string $reply, private ?string $fails) {}

        public function available(): bool
        {
            return true;
        }

        public function reply(array $history, string $question, ?array $room, string $locale): array
        {
            $this->calls[] = compact('history', 'question', 'room', 'locale');
            if ($this->fails) {
                throw new DraftFailed($this->fails);
            }

            return ['reply' => $this->reply, 'product_ids' => $this->ids];
        }
    };
    app()->instance(ShoppingAssistant::class, $fake);

    return $fake;
}

/** A PSR-18 client that answers with each body in turn and keeps every request. */
function scriptedTransport(array $bodies): object
{
    return new class($bodies) implements ClientInterface
    {
        public array $requests = [];

        public function __construct(private array $bodies) {}

        public function sendRequest(RequestInterface $request): ResponseInterface
        {
            $this->requests[] = json_decode((string) $request->getBody(), true);

            return new Response(200, ['Content-Type' => 'application/json'], json_encode(array_shift($this->bodies)));
        }
    };
}

function apiMessage(array $content, string $stop): array
{
    return ['id' => 'msg_'.uniqid(), 'type' => 'message', 'role' => 'assistant', 'model' => 'claude-opus-5-5', 'content' => $content,
        'stop_reason' => $stop, 'stop_sequence' => null, 'usage' => ['input_tokens' => 800, 'output_tokens' => 120]];
}

it('searches the live catalogue by words, price and room, and says what fits', function () {
    $small = sofa('Compact sofa', 600, [180, 85, 80]);
    $huge = sofa('Grand sectional', 2400, [420, 300, 85]);
    sofa('Hidden draft sofa', 500, status: 'draft');

    $found = CatalogueTools::search(['words' => 'sofa', 'category' => null, 'colour' => null, 'max_price' => 1000, 'min_price' => null,
        'room_width_cm' => 300, 'room_length_cm' => 400, 'only_fitting' => false], null);

    expect(collect($found['results'])->pluck('id')->all())->toBe([$small->id])
        ->and($found['results'][0]['price'])->toBe(600.0)
        ->and($found['results'][0]['fit'])->toBe('fits')
        ->and($found['room'])->toBe(['width' => 300, 'length' => 400]);

    $fitting = CatalogueTools::search(['words' => 'sofa', 'category' => null, 'colour' => null, 'max_price' => null, 'min_price' => null,
        'room_width_cm' => 300, 'room_length_cm' => 400, 'only_fitting' => true], null);
    expect(collect($fitting['results'])->pluck('id')->all())->not->toContain($huge->id);
});

it('tells the model whether a piece can reach the room, once the visitor has given the way in', function () {
    $deep = sofa('Deep sofa', 900, [220, 95, 85]);

    expect(CatalogueTools::piece($deep->id, null)['gets_in'])->toBeNull();

    session([RoomController::ROUTE_KEY => ['access' => 'lift', 'door' => ['w' => 95, 'h' => 210],
        'lift' => ['door_w' => 80, 'door_h' => 200, 'w' => 110, 'd' => 140, 'h' => 220], 'stairs' => null, 'balcony' => null]]);

    // Not said how it arrives: it may come apart, so not a "no".
    expect(CatalogueTools::piece($deep->id, null)['gets_in'])->toBe('unknown');

    $deep->variants()->update(['arrives' => 'assembled']);
    expect(CatalogueTools::piece($deep->id, null)['gets_in'])->toBe('no');
});

it('answers a question with pieces drawn from the catalogue, in the answer\'s order', function () {
    $a = sofa('Compact sofa', 600);
    $b = sofa('Linen loveseat', 450);
    $gone = sofa('Withdrawn sofa', 300, status: 'inactive');
    $fake = fakeAssistant([$b->id, $gone->id, $a->id]);

    $response = $this->postJson(route('assistant.ask'), ['message' => 'A sofa for a small room'])->assertOk()->json();

    expect($response['reply'])->toBe('Here are two.')
        ->and($response['html'])->toContain('Linen loveseat')->toContain('Compact sofa')->toContain(Money::format(450))
        ->not->toContain('Withdrawn sofa')
        ->and(strpos($response['html'], 'Linen loveseat'))->toBeLessThan(strpos($response['html'], 'Compact sofa'))
        ->and($fake->calls[0]['question'])->toBe('A sofa for a small room');
});

it('remembers the last turns of the conversation, and forgets them on request', function () {
    $fake = fakeAssistant();

    $this->postJson(route('assistant.ask'), ['message' => 'Something grey']);
    $this->postJson(route('assistant.ask'), ['message' => 'Cheaper, please']);

    expect($fake->calls[1]['history'])->toBe([['role' => 'user', 'text' => 'Something grey'], ['role' => 'assistant', 'text' => 'Here are two.']]);

    $this->postJson(route('assistant.forget'))->assertOk();
    expect(session(AssistantController::SESSION_KEY))->toBeNull();
});

it('stops at the visitor\'s daily questions, and at the shop\'s', function () {
    fakeAssistant();
    config(['services.anthropic.assistant_per_visitor' => 2]);

    $this->postJson(route('assistant.ask'), ['message' => 'one'])->assertOk();
    $this->postJson(route('assistant.ask'), ['message' => 'two'])->assertOk();
    $this->postJson(route('assistant.ask'), ['message' => 'three'])->assertStatus(429)->assertJson(['message' => __('diwan.assistant.limit')]);

    config(['services.anthropic.assistant_per_visitor' => 99, 'services.anthropic.assistant_per_day' => 2]);
    $this->postJson(route('assistant.ask'), ['message' => 'four'])->assertStatus(429);
});

it('says plainly when it cannot answer, and is absent without a key', function () {
    fakeAssistant(fails: 'failed');
    $this->postJson(route('assistant.ask'), ['message' => 'A sofa'])->assertStatus(502)->assertJson(['message' => __('diwan.assistant.failed')]);

    app()->forgetInstance(ShoppingAssistant::class);
    config(['services.anthropic.key' => null]);
    $this->postJson(route('assistant.ask'), ['message' => 'A sofa'])->assertStatus(503);
    $this->get(route('furnish.index'))->assertDontSee('x-data="assistant"', false);

    config(['services.anthropic.key' => 'sk-ant-test']);
    $this->get(route('furnish.index'))->assertSee('x-data="assistant"', false)->assertSee(__('diwan.assistant.open'));
});

it('keeps the home hero clear: its way in is a line in the room card, present only with a key', function () {
    config(['services.anthropic.key' => null]);
    $this->get(route('furnish.index'))->assertDontSee('data-open-assistant', false);

    app()->forgetInstance(ShoppingAssistant::class);
    config(['services.anthropic.key' => 'sk-ant-test']);
    $this->get(route('furnish.index'))
        ->assertSee('data-hero', false)
        ->assertSee('data-open-assistant', false)
        ->assertSee(__('diwan.assistant.hero_prompt'))
        ->assertSee(':data-hidden="tucked"', false);

    $this->get(route('furnish.products'))->assertDontSee('data-open-assistant', false);
});

it('runs the tool loop: Claude searches, reads real rows, and may only show what it was given', function () {
    $sofa = sofa('Compact sofa', 600, [180, 85, 80]);

    $transport = scriptedTransport([
        apiMessage([['type' => 'tool_use', 'id' => 'toolu_1', 'name' => 'search_catalogue', 'input' => [
            'words' => 'sofa', 'category' => null, 'colour' => null, 'max_price' => 1000, 'min_price' => null,
            'room_width_cm' => 300, 'room_length_cm' => 400, 'only_fitting' => true,
        ]]], 'tool_use'),
        // It names the real piece, and one it was never shown (999), which must be dropped.
        apiMessage([['type' => 'text', 'text' => json_encode(['reply' => 'The compact sofa fits your room.', 'product_ids' => [$sofa->id, 999]])]], 'end_turn'),
    ]);

    $assistant = new ClaudeShoppingAssistant('sk-ant-test', 'claude-opus-5-5', new Client(apiKey: 'sk-ant-test', requestOptions: ['transporter' => $transport, 'maxRetries' => 0]));
    $answer = $assistant->reply([], 'A sofa for my 3 by 4 room under 1000', null, 'en');

    expect($answer)->toBe(['reply' => 'The compact sofa fits your room.', 'product_ids' => [$sofa->id]]);

    [$first, $second] = $transport->requests;
    expect($first['tools'][0]['name'])->toBe('search_catalogue')
        ->and($first['tools'][0]['strict'])->toBeTrue()
        ->and($first['output_config']['format']['type'])->toBe('json_schema')
        ->and($first['fallbacks'])->toBe('default');

    // The second request carries the first answer back unchanged, then the real rows.
    $result = json_decode($second['messages'][2]['content'][0]['content'], true);
    expect($second['messages'][1]['content'][0]['id'])->toBe('toolu_1')
        ->and($second['messages'][2]['content'][0]['tool_use_id'])->toBe('toolu_1')
        ->and($result['results'][0])->toMatchArray(['id' => $sofa->id, 'name' => 'Compact sofa', 'price' => 600, 'fit' => 'fits']);
});

it('gives up after its steps rather than looping', function () {
    $loop = apiMessage([['type' => 'tool_use', 'id' => 'toolu_x', 'name' => 'get_piece', 'input' => ['id' => 1]]], 'tool_use');
    $transport = scriptedTransport([$loop, $loop, $loop, $loop, $loop]);
    $assistant = new ClaudeShoppingAssistant('sk-ant-test', 'claude-opus-5-5', new Client(apiKey: 'sk-ant-test', requestOptions: ['transporter' => $transport, 'maxRetries' => 0]));

    expect(fn () => $assistant->reply([], 'Tell me about piece 1', null, 'en'))->toThrow(DraftFailed::class)
        ->and(count($transport->requests))->toBe(4);
});
