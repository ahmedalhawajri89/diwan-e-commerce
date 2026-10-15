<?php

use Illuminate\Support\Facades\DB;

/**
 * A deployment with no lasting disk keeps sessions in the database. The table
 * must be one Laravel's driver can write, or no one can sign in.
 */
it('keeps a session in the database, the way a host without a disk needs', function () {
    config(['session.driver' => 'database']);

    $handler = app('session')->driver('database')->getHandler();
    $handler->write($id = str_repeat('s', 40), 'payload');

    expect(DB::table('sessions')->where('id', $id)->exists())->toBeTrue()
        ->and($handler->read($id))->toBe('payload');
});
