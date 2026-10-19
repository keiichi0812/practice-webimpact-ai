<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('licenses', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name');
            $table->string('product_code', 32);
            $table->unsignedInteger('seats');
            $table->integer('seats_used')->default(0);
            $table->date('starts_on');
            $table->date('expires_on');
            $table->string('status', 16)->default('active');
            $table->timestamps();

            $table->index(['status', 'expires_on']);
        });

        Schema::create('license_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('license_id')->constrained()->cascadeOnDelete();
            $table->string('user_email');
            $table->timestamp('assigned_at');
            $table->timestamp('released_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('license_assignments');
        Schema::dropIfExists('licenses');
    }
};
