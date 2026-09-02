<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('consultations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('lawyer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('problem_type');
            $table->string('title');
            $table->text('description');
            $table->dateTime('scheduled_at')->nullable();
            $table->string('status')->default('Menunggu'); // Menunggu, Dijadwalkan, Selesai, Dibatalkan
            $table->text('result_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('consultations');
    }
};
