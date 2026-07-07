<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('vendors')) {
            return;
        }

        DB::table('vendors')
            ->where('shop_name', 'Almar Market Official Store')
            ->update([
                'shop_name' => 'Mikrotik Kenya Official Store',
                'slug' => 'mikrotik-kenya-official-store',
            ]);
    }

    public function down(): void
    {
        if (!Schema::hasTable('vendors')) {
            return;
        }

        DB::table('vendors')
            ->where('shop_name', 'Mikrotik Kenya Official Store')
            ->where('slug', 'mikrotik-kenya-official-store')
            ->update([
                'shop_name' => 'Almar Market Official Store',
                'slug' => 'almar-market-official-store',
            ]);
    }
};
