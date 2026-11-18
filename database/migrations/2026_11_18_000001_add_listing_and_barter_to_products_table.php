<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Which public list a buyer joins: boosters, followers or supporters.
            $table->string('listing')->nullable()->after('ships');
            // Price paid outside PayPal through Barter Sales; PayPal only takes the fee.
            $table->boolean('barter')->default(false)->after('listing');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('list_name')->nullable()->after('plaque_name');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['listing', 'barter']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('list_name');
        });
    }
};
