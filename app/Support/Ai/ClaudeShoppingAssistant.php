<?php

namespace App\Support\Ai;

use Anthropic\Client;
use Anthropic\Core\Exceptions\APIException;
use Illuminate\Support\Facades\Log;

/**
 * «Ask Diwan», answered by Claude with two read-only tools over the live
 * catalogue (CatalogueTools).
 *
 * The loop runs at most MAX_STEPS requests. The last answer is held to a
 * JSON schema: a short reply and the ids of the pieces to show. Only ids the
 * tools actually returned in this turn are kept, so a piece can never be
 * shown that the catalogue did not offer. Earlier turns are sent as plain
 * text; within a turn, each response goes back unchanged, as the API needs.
 */
class ClaudeShoppingAssistant implements ShoppingAssistant
{
    private const MAX_STEPS = 4;

    private const INSTRUCTIONS = <<<'TXT'
You are «Ask Diwan», the shopping help of Diwan, a furniture shop in Jordan that sells in Arabic and English. Its promise is honesty: every number on its pages is real, and what it does not know it does not claim.

How to help:
- Use the search_catalogue tool before suggesting any piece, and get_piece when you need one piece's details. Only the tools tell you what exists, what it costs, its colours and its recorded size.
- If the visitor gives a room size, pass it to the tool in centimetres (3 by 4 metres is room_width_cm 300, room_length_cm 400). A piece's "fit" is the shop's own verdict: "fits", "tight", "no", or "unknown" when the piece has not been measured. Say "unknown" honestly as "not measured yet"; never guess a size.
- When a piece carries "gets_in", it is the shop's verdict on whether it can reach the visitor's room through their door, lift or stairs: "passes", "tight" (goes in with care), "hoist" (only through the balcony, lifted from outside), "no", or "unknown" (not checked). Prefer pieces that pass. If you recommend one that is "no" or "hoist", say so plainly.
- Recommend at most 4 pieces, by id, in product_ids, best first. The page shows each with its real picture, price and fit, so do not repeat prices or dimensions in your reply unless the visitor asks for a comparison.
- Never state a price, size, material, stock level, delivery time, warranty or discount that the tools did not return. If nothing matches, say so plainly and suggest what to loosen (budget, colour, size).
- Shop facts you may state: payment is cash on delivery; the delivery cost depends on the address and is confirmed by phone before anything is sent; no account is needed to order.
- For anything that is not about furniture or this shop, say briefly that you can only help with choosing furniture here.

Write the reply in the visitor's language (the locale is given; follow the language the visitor writes in if it differs). Two to four short sentences, warm and precise, no exclamation marks, Western digits only.
TXT;

    public function __construct(
        private readonly ?string $key,
        private readonly string $model,
        private readonly ?Client $client = null,
    ) {}

    public function available(): bool
    {
        return filled($this->key);
    }

    public function reply(array $history, string $question, ?array $room, string $locale): array
    {
        if (! $this->available()) {
            throw new DraftFailed('unavailable');
        }

        $messages = [];
        foreach ($history as $turn) {
            $messages[] = ['role' => $turn['role'], 'content' => $turn['text']];
        }
        $context = 'Locale: '.$locale.'. '.($room ? "Room saved on the site: {$room['width']} × {$room['length']} cm." : 'No room saved on the site.');
        $messages[] = ['role' => 'user', 'content' => $context."\n\nVisitor: ".$question];

        $offered = [];

        try {
            for ($step = 1; $step <= self::MAX_STEPS; $step++) {
                $response = $this->client()->beta->messages->create(
                    model: $this->model,
                    maxTokens: 4000,
                    system: [['type' => 'text', 'text' => self::INSTRUCTIONS, 'cacheControl' => ['type' => 'ephemeral']]],
                    tools: CatalogueTools::definitions(),
                    messages: $messages,
                    outputConfig: [
                        'effort' => 'low',
                        'format' => ['type' => 'json_schema', 'schema' => self::schema()],
                    ],
                    fallbacks: 'default',
                    betas: ['server-side-fallback-2026-07-01'],
                );

                Log::info('Assistant step', ['step' => $step, 'stop' => $response->stopReason, 'in' => $response->usage->inputTokens, 'out' => $response->usage->outputTokens]);

                if ($response->stopReason === 'refusal') {
                    throw new DraftFailed('declined');
                }

                if ($response->stopReason !== 'tool_use') {
                    return $this->answer($response->content, $offered);
                }

                $results = [];
                foreach ($response->content as $block) {
                    if (($block->type ?? null) !== 'tool_use') {
                        continue;
                    }
                    $output = $this->run($block->name, (array) $block->input, $room);
                    foreach ($output['results'] ?? [$output] as $row) {
                        if (isset($row['id'])) {
                            $offered[(int) $row['id']] = true;
                        }
                    }
                    $results[] = ['type' => 'tool_result', 'toolUseID' => $block->id, 'content' => json_encode($output, JSON_UNESCAPED_UNICODE)];
                }

                $messages[] = ['role' => 'assistant', 'content' => $response->content];
                $messages[] = ['role' => 'user', 'content' => $results];
            }
        } catch (APIException $e) {
            Log::warning('Assistant failed', ['message' => $e->getMessage()]);

            throw new DraftFailed('failed', previous: $e);
        }

        throw new DraftFailed('failed', 'The assistant did not finish within its steps.');
    }

    private function run(string $tool, array $input, ?array $room): array
    {
        return match ($tool) {
            'search_catalogue' => CatalogueTools::search($input, $room),
            'get_piece' => CatalogueTools::piece((int) ($input['id'] ?? 0), $room) ?? ['error' => 'No live piece has that id.'],
            default => ['error' => 'No such tool.'],
        };
    }

    /** @param  array<int, true>  $offered */
    private function answer(array $content, array $offered): array
    {
        foreach ($content as $block) {
            if (($block->type ?? null) === 'text') {
                $data = json_decode($block->text, true);
                if (is_array($data) && filled($data['reply'] ?? null)) {
                    $ids = collect($data['product_ids'] ?? [])->map(fn ($id) => (int) $id)
                        ->filter(fn ($id) => isset($offered[$id]))->unique()->take(4)->values()->all();

                    return ['reply' => trim($data['reply']), 'product_ids' => $ids];
                }
            }
        }

        throw new DraftFailed('failed', 'The reply held no answer.');
    }

    private static function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'reply' => ['type' => 'string'],
                'product_ids' => ['type' => 'array', 'items' => ['type' => 'integer']],
            ],
            'required' => ['reply', 'product_ids'],
            'additionalProperties' => false,
        ];
    }

    private function client(): Client
    {
        return $this->client ?? new Client(apiKey: $this->key, requestOptions: ['timeout' => 60, 'maxRetries' => 1]);
    }
}
