<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Seed default configurations
        $defaults = [
            ['key' => 'app_name', 'value' => 'Kasir Zamiel', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'color_primary', 'value' => '#313118', 'created_at' => now(), 'updated_at' => now()], // --olivenite
            ['key' => 'color_secondary', 'value' => '#6D6844', 'created_at' => now(), 'updated_at' => now()], // --bancha
            ['key' => 'color_accent', 'value' => '#CCC6A9', 'created_at' => now(), 'updated_at' => now()], // --hillside
            ['key' => 'color_danger', 'value' => '#83482B', 'created_at' => now(), 'updated_at' => now()], // --terracotta
            ['key' => 'store_name', 'value' => 'Warung Sembako Zamiel', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'store_address', 'value' => 'Jl. Kebon Jeruk No. 12, Jakarta', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'store_phone', 'value' => '081234567890', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'business_type', 'value' => 'sembako', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'enable_barcode', 'value' => '1', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'enable_expiration', 'value' => '1', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'enable_stock', 'value' => '1', 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('settings')->insert($defaults);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
