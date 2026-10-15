<?php

namespace App\Support\Ai;

use Anthropic\Client;
use Anthropic\Core\Exceptions\APIException;
use Illuminate\Support\Facades\Log;

/**
 * Description drafts written by Claude, from ProductFacts and the piece's
 * photograph.
 *
 * The instructions are fixed and cached; the facts and the picture follow
 * them. The reply is constrained to a JSON schema, so it is read, never
 * guessed at. Server-side fallback is on: should a request be declined,
 * Anthropic re-runs it on a fallback model inside the same call.
 */
class ClaudeDescriptionDrafter implements DescriptionDrafter
{
    /** The rules, in English for the model; the drafts come back in both languages. */
    private const INSTRUCTIONS = <<<'TXT'
You write product descriptions for Diwan, a furniture shop that sells in Arabic and English and whose promise is honesty: every number on its pages is real, and what it does not know it does not claim.

You receive the facts the shop has recorded about one piece, and sometimes its photograph. Write a short description of it in English and in Arabic.

Rules:
- Use only the facts given and what is plainly visible in the photograph. Do not state a material, finish, wood, fabric, weight, warranty, delivery time, origin, or comfort claim that the facts do not give or the photograph does not clearly show. When unsure, leave it out.
- If dimensions are given, you may mention them in centimetres exactly as given. If they are not given, do not mention size in numbers at all.
- Never mention price, stock, discounts or offers.
- Calm, precise and warm. No exclamation marks, no superlatives ("best", "perfect", "luxurious"), no marketing clichés.
- English: 50 to 90 words. Arabic: 50 to 90 words of modern standard Arabic, written for an Arabic reader rather than translated word for word, with Western digits (0-9) for any number.
- name_ar: a short natural Arabic name for the piece if the facts give none; if an Arabic name is given, return it unchanged.
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

    public function draft(array $facts): array
    {
        if (! $this->available()) {
            throw new DraftFailed('unavailable');
        }

        try {
            $message = $this->client()->beta->messages->create(
                model: $this->model,
                maxTokens: 4000,
                system: [
                    ['type' => 'text', 'text' => self::INSTRUCTIONS, 'cacheControl' => ['type' => 'ephemeral']],
                ],
                messages: [['role' => 'user', 'content' => $this->content($facts)]],
                outputConfig: [
                    'effort' => 'medium',
                    'format' => ['type' => 'json_schema', 'schema' => self::schema()],
                ],
                fallbacks: 'default',
                betas: ['server-side-fallback-2026-07-01'],
            );
        } catch (APIException $e) {
            Log::warning('Description draft failed', ['message' => $e->getMessage()]);

            throw new DraftFailed('failed', previous: $e);
        }

        if ($message->stopReason === 'refusal') {
            throw new DraftFailed('declined');
        }

        Log::info('Description drafted', [
            'model' => $message->model,
            'input_tokens' => $message->usage->inputTokens,
            'output_tokens' => $message->usage->outputTokens,
            'cache_read' => $message->usage->cacheReadInputTokens,
        ]);

        foreach ($message->content as $block) {
            if (($block->type ?? null) === 'text') {
                $data = json_decode($block->text, true);
                if (is_array($data) && filled($data['en'] ?? null) && filled($data['ar'] ?? null)) {
                    return [
                        'en' => trim($data['en']),
                        'ar' => trim($data['ar']),
                        'name_ar' => filled($data['name_ar'] ?? null) ? trim($data['name_ar']) : null,
                    ];
                }
            }
        }

        throw new DraftFailed('failed', 'The reply held no draft.');
    }

    /** The facts as text, and the photograph before them when there is one. */
    private function content(array $facts): array
    {
        $content = [];
        $image = $facts['image'] ?? null;
        unset($facts['image']);

        if ($image && is_file($image) && filesize($image) < 4_000_000) {
            $content[] = [
                'type' => 'image',
                'source' => ['type' => 'base64', 'mediaType' => self::mediaType($image), 'data' => base64_encode(file_get_contents($image))],
            ];
        }

        $content[] = ['type' => 'text', 'text' => "Facts about the piece (JSON):\n".json_encode($facts, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)];

        return $content;
    }

    private static function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'en' => ['type' => 'string'],
                'ar' => ['type' => 'string'],
                'name_ar' => ['type' => 'string'],
            ],
            'required' => ['en', 'ar', 'name_ar'],
            'additionalProperties' => false,
        ];
    }

    private static function mediaType(string $path): string
    {
        return match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'png' => 'image/png',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            default => 'image/jpeg',
        };
    }

    private function client(): Client
    {
        return $this->client ?? new Client(apiKey: $this->key, requestOptions: ['timeout' => 60, 'maxRetries' => 1]);
    }
}
