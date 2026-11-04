<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentVersion extends Model
{
    use HasFactory;

    protected $table = 'document_versions';

    protected $fillable = [
        'document_id',
        'version_number',
        'file_path',
        'original_filename',
        'file_size',
        'mime_type',
        'uploaded_by',
        'revision_notes',
        'status',
        'is_final',
    ];

    protected $casts = [
        'version_number' => 'integer',
        'file_size'      => 'integer',
        'is_final'       => 'boolean',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class, 'document_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function comments()
    {
        return $this->hasMany(DocumentComment::class, 'document_version_id');
    }

    public function getFormattedFileSizeAttribute(): string
    {
        if (!$this->file_size) {
            return '—';
        }
        $bytes = $this->file_size;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1) . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 0) . ' KB';
        }
        return $bytes . ' B';
    }
}
