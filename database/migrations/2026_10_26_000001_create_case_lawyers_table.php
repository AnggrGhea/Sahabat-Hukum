<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('case_lawyers')) {
            Schema::create('case_lawyers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('case_id')->constrained('cases')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->string('role_in_case')->default('Anggota Tim'); // 'Ketua Tim', 'Anggota Tim'
                $table->foreignId('assigned_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamps();

                $table->unique(['case_id', 'user_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('case_lawyers');
    }
};
