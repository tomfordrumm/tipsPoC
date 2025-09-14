<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stripe_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_id')->unique();
            $table->string('type')->index();
            $table->string('signature')->nullable();
            $table->json('payload')->nullable();
            $table->string('processing_status')->default('received');
            $table->timestamp('processed_at')->nullable();
            $table->string('payment_intent_id')->nullable();
            $table->string('checkout_session_id')->nullable();
            $table->unsignedBigInteger('tip_id')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stripe_events');
    }
};

