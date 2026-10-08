<?php

namespace App\Support;

use App\Models\Notification;
use App\Models\User;

/**
 * Tells every administrator that something happened.
 *
 * Writes one row per administrator, so each has their own read state. Never
 * throws: telling the panel is a courtesy to the operator, and an order, a
 * review or a sign-up must not fail because the notice about it could not
 * be written. A failure goes to the log.
 */
final class AdminAlerts
{
    /** @param  array<string, scalar|null>  $data  the facts the sentence needs */
    public static function notify(string $type, array $data = []): void
    {
        try {
            $admins = User::whereHas('roles', fn ($q) => $q->where('name', 'admin'))->pluck('id');

            if ($admins->isEmpty()) {
                return;
            }

            // The required title and message columns get an English copy; the
            // panel itself always re-reads the sentence in its reader's language.
            $english = __('admin.alerts.'.$type, collect($data)->map(fn ($v) => (string) $v)->all(), 'en');
            $now = now();

            Notification::insert($admins->map(fn ($id) => [
                'notifiable_type' => User::class,
                'notifiable_id' => $id,
                'type' => $type,
                'title' => mb_substr($english, 0, 255),
                'message' => $english,
                'data' => json_encode($data, JSON_UNESCAPED_UNICODE),
                'created_at' => $now,
                'updated_at' => $now,
            ])->all());
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
