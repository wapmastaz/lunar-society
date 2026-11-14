<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('address_line1', 150)->nullable()->after('buyer_name');
            $table->string('address_line2', 150)->nullable()->after('address_line1');
            $table->string('state', 100)->nullable()->change();
            $table->string('postal_code', 20)->nullable()->change();
            $table->string('country', 2)->nullable()->after('postal_code');
            $table->string('phone', 30)->nullable()->after('country');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['address_line1', 'address_line2', 'country', 'phone']);
        });
    }
};
