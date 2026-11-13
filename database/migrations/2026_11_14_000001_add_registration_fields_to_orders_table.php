<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('city', 100)->nullable()->after('buyer_name');
            $table->string('state', 2)->nullable()->after('city');
            $table->string('postal_code', 10)->nullable()->after('state');
            $table->boolean('marketing_opt_in')->default(false)->after('postal_code');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['city', 'state', 'postal_code', 'marketing_opt_in']);
        });
    }
};
