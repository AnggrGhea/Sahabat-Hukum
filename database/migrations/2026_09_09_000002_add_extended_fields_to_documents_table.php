<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('documents', function (Blueprint $table) {
            if (!Schema::hasColumn('documents', 'document_request_id')) {
                $table->foreignId('document_request_id')->nullable()->after('lawyer_id')->constrained('document_requests')->onDelete('set null');
            }
            if (!Schema::hasColumn('documents', 'uploaded_by')) {
                $table->foreignId('uploaded_by')->nullable()->after('document_request_id')->constrained('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('documents', 'parent_id')) {
                $table->foreignId('parent_id')->nullable()->after('uploaded_by')->constrained('documents')->onDelete('set null');
            }
            if (!Schema::hasColumn('documents', 'version')) {
                $table->unsignedInteger('version')->default(1)->after('parent_id');
            }
            if (!Schema::hasColumn('documents', 'document_type')) {
                $table->string('document_type')->default('Dokumen Lainnya')->after('name');
            }
            if (!Schema::hasColumn('documents', 'original_filename')) {
                $table->string('original_filename')->nullable()->after('file_path');
            }
            if (!Schema::hasColumn('documents', 'mime_type')) {
                $table->string('mime_type')->nullable()->after('original_filename');
            }
            if (!Schema::hasColumn('documents', 'file_size')) {
                $table->unsignedBigInteger('file_size')->nullable()->after('mime_type');
            }
            if (!Schema::hasColumn('documents', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('status');
            }
            if (!Schema::hasColumn('documents', 'verified_by')) {
                $table->foreignId('verified_by')->nullable()->after('rejection_reason')->constrained('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('documents', 'verified_at')) {
                $table->timestamp('verified_at')->nullable()->after('verified_by');
            }
            if (!Schema::hasColumn('documents', 'is_from_lawyer')) {
                $table->boolean('is_from_lawyer')->default(false)->after('verified_at');
            }
        });

        // Migrate legacy statuses if needed
        DB::table('documents')->where('status', 'Menunggu Pemeriksaan')->update(['status' => 'Menunggu Verifikasi']);
        DB::table('documents')->where('status', 'Sudah Diterima')->update(['status' => 'Terverifikasi']);
        DB::table('documents')->where('status', 'Perlu Diperbaiki')->update(['status' => 'Ditolak']);
    }

    public function down()
    {
        Schema::table('documents', function (Blueprint $table) {
            $cols = [
                'document_request_id', 'uploaded_by', 'parent_id', 'version',
                'document_type', 'original_filename', 'mime_type', 'file_size',
                'rejection_reason', 'verified_by', 'verified_at', 'is_from_lawyer'
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('documents', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
