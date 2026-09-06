<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Checkout collected a phone number and had nowhere to put it, so it
     * appended it to the street with a comment saying the reason was avoiding
     * a migration:
     *
     *     "Rainbow Street 12 (Phone: 0791234567)"
     *
     * Every order written that way corrupted an address row, made the number
     * unsearchable, and would print that string on a delivery label. This adds
     * the column and lifts the numbers back out of the rows already affected.
     */
    public function up(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->string('phone', 32)->nullable()->after('street');
        });

        $this->repairExistingRows();
    }

    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropColumn('phone');
        });
    }

    /**
     * Move the appended number into the new column and restore the street.
     *
     * Rows that never carried the suffix are untouched, and a row whose suffix
     * cannot be parsed is left exactly as it is — a half-repaired address is
     * worse than an obviously broken one.
     */
    private function repairExistingRows(): void
    {
        DB::table('addresses')
            ->where('street', 'like', '% (Phone: %)')
            ->orderBy('id')
            ->chunkById(200, function ($addresses) {
                foreach ($addresses as $address) {
                    if (! preg_match('/^(.*) \(Phone: (.+)\)$/', $address->street, $matches)) {
                        continue;
                    }

                    DB::table('addresses')->where('id', $address->id)->update([
                        'street' => trim($matches[1]),
                        'phone' => mb_substr(trim($matches[2]), 0, 32),
                    ]);
                }
            });
    }
};
