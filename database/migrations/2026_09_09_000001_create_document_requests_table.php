<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('document_requests')) {
            Schema::create('document_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('case_id')->constrained('cases')->onDelete('cascade');
                $table->foreignId('requested_by')->constrained('users')->onDelete('cascade');
                $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
                $table->string('title');
                $table->string('document_type')->nullable();
                $table->text('description')->nullable();
                $table->date('due_date')->nullable();
                $table->string('priority')->default('Normal'); // Normal, Tinggi
                $table->string('status')->default('Menunggu Upload'); // Menunggu Upload, Sudah Diupload, Selesai, Dibatalkan
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('document_requests');
    }
};
