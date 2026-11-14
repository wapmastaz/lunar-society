<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Physical items ship (PayPal collects an address) and carry the processing fee.
            $table->boolean('ships')->default(true)->after('price_cents');
            $table->unsignedSmallInteger('sort')->default(0)->after('ships');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['ships', 'sort']);
        });
    }
};
