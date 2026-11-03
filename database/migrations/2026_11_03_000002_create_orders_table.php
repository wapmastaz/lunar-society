<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('tier_id')->nullable()->constrained();
            $table->foreignId('product_id')->nullable()->constrained();
            $table->string('status')->default('pending')->index();
            $table->unsignedInteger('amount_cents');
            $table->string('email');
            $table->string('plaque_name')->nullable();
            $table->boolean('couple_addon')->default(false);
            $table->string('listener_handle')->nullable();
            $table->boolean('show_full_name')->default(false);
            $table->text('consent_text')->nullable();
            $table->timestamp('consented_at')->nullable();
            $table->string('payer_first_name')->nullable();
            $table->string('payer_last_name')->nullable();
            $table->json('shipping')->nullable();
            $table->unsignedInteger('slot_number')->nullable()->unique();
            $table->timestamp('hold_expires_at')->nullable();
            $table->string('paypal_order_id')->nullable()->unique();
            $table->string('paypal_capture_id')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('locks_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['tier_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
