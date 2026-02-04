<?php

use App\Models\Studio;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update existing studios with null settings to have default settings
        DB::table('studios')
            ->whereNull('settings')
            ->orWhere('settings', '{}')
            ->orWhere('settings', '[]')
            ->update([
                'settings' => json_encode(Studio::DEFAULT_SETTINGS),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No rollback needed - we don't want to remove settings
    }
};
