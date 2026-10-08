<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Support\Ai\DraftFailed;
use App\Support\Ai\ShoppingAssistant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * «Ask Diwan»: one question in, a short answer and the pieces it points to
 * out, drawn by the server from the catalogue (so every price and size on
 * them is a row's). The conversation lives in the visitor's session, the
 * last few turns only, and is forgotten with it.
 *
 * Each answer is a paid call, so it is bounded three ways: a per-minute
 * throttle on the route, a number of questions per visitor per day, and a
 * ceiling for the whole shop per day.
 */
class AssistantController extends Controller
{
    public const SESSION_KEY = 'diwan.assistant';

    /** Turns kept and sent back as context (a question and its answer are two). */
    private const KEEP = 8;

    public function ask(Request $request, ShoppingAssistant $assistant): JsonResponse
    {
        $validated = $request->validate(['message' => 'required|string|min:2|max:500']);

        if (! $assistant->available()) {
            return response()->json(['message' => __('diwan.assistant.unavailable')], 503);
        }

        // Per visitor: counted in their session, and, more loosely, by address,
        // so a client that drops its cookie cannot ask without end.
        $day = now()->toDateString();
        $perVisitor = (int) config('services.anthropic.assistant_per_visitor', 30);
        $sessionKey = self::SESSION_KEY.'_count.'.$day;
        $addressKey = 'assistant:ip:'.sha1((string) $request->ip()).':'.$day;
        $shopKey = 'assistant:shop:'.$day;

        if ((int) $request->session()->get($sessionKey, 0) >= $perVisitor
            || (int) Cache::get($addressKey, 0) >= $perVisitor * 3
            || (int) Cache::get($shopKey, 0) >= (int) config('services.anthropic.assistant_per_day', 300)) {
            return response()->json(['message' => __('diwan.assistant.limit')], 429);
        }

        $history = $request->session()->get(self::SESSION_KEY, []);

        try {
            $answer = $assistant->reply($history, trim($validated['message']), RoomController::current(), app()->getLocale());
        } catch (DraftFailed $e) {
            return response()->json(['message' => __('diwan.assistant.'.($e->reason === 'declined' ? 'declined' : 'failed'))], 502);
        }

        $request->session()->put($sessionKey, (int) $request->session()->get($sessionKey, 0) + 1);
        Cache::add($addressKey, 0, now()->endOfDay());
        Cache::increment($addressKey);
        Cache::add($shopKey, 0, now()->endOfDay());
        Cache::increment($shopKey);

        $history[] = ['role' => 'user', 'text' => trim($validated['message'])];
        $history[] = ['role' => 'assistant', 'text' => $answer['reply']];
        $request->session()->put(self::SESSION_KEY, array_slice($history, -self::KEEP));

        // The pieces, in the order the answer gave, live and with what a card
        // needs; anything no longer for sale simply drops out.
        $products = Product::with(['category', 'variants', 'images'])
            ->where('status', 'active')
            ->whereIn('id', $answer['product_ids'])
            ->get()
            ->sortBy(fn ($p) => array_search($p->id, $answer['product_ids'], true))
            ->values();

        return response()->json([
            'reply' => $answer['reply'],
            'html' => view('partials.assistant-pieces', ['products' => $products])->render(),
        ]);
    }

    /** Start again: forget the conversation. */
    public function forget(Request $request): JsonResponse
    {
        $request->session()->forget(self::SESSION_KEY);

        return response()->json(['ok' => true]);
    }
}
