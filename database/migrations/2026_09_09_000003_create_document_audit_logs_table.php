<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('document_audit_logs')) {
            Schema::create('document_audit_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('document_id')->nullable()->constrained('documents')->onDelete('set null');
                $table->foreignId('case_id')->nullable()->constrained('cases')->onDelete('set null');
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
                $table->string('action'); // upload, reupload, view, download, verify, reject, request, cancel_request, delete
                $table->text('notes')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('document_audit_logs');
    }
};
