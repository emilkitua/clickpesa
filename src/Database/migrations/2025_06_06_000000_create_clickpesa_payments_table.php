<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClickpesaPaymentsTable extends Migration
{
    public function up()
    {
        Schema::create('clickpesa_payments', function (Blueprint $table) {
            $table->id();

            // Transaction reference details
            $table->string('reference_id')->unique(); // internal reference
            $table->string('external_id')->nullable(); // ClickPesa transaction ID

            // Payment method & metadata
            $table->enum('payment_method', ['ussd', 'card'])->index();
            $table->string('channel')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('card_number_masked')->nullable(); // only if card
            $table->decimal('amount', 15, 2);
            $table->string('currency', 10)->default('TZS');

            // Status tracking
            $table->enum('status', ['pending', 'processing', 'successful', 'failed', 'cancelled'])->default('pending')->index();
            $table->string('status_detail')->nullable(); // ClickPesa message or error reason

            // Full payloads (optional but helpful for debugging/logging)
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();

            // Response timestamp
            $table->timestamp('paid_at')->nullable();

            $table->timestamps(); // created_at = requested_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('clickpesa_payments');
    }
}
