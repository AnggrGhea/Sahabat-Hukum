<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('lawyer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('case_id')->nullable()->constrained('cases')->onDelete('set null');
            $table->string('title')->nullable();
            $table->enum('status', ['active', 'archived'])->default('active');
            $table->timestamp('last_message_at')->nullable()->index();
            $table->timestamps();

            $table->index(['client_id', 'lawyer_id']);
            $table->index(['case_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
