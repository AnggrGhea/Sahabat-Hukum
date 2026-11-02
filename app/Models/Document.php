<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'case_id',
        'client_id',
        'lawyer_id',
        'document_request_id',
        'uploaded_by',
        'parent_id',
        'version',
        'name',
        'document_type',
        'description',
        'file_path',
        'original_filename',
        'mime_type',
        'file_size',
        'due_date',
        'priority',
        'status',
        'rejection_reason',
        'verified_by',
        'verified_at',
        'is_from_lawyer',
        'is_internal',
        'collaboration_status',
        'finalized_by',
        'finalized_at',
    ];

    protected $casts = [
        'due_date'             => 'date',
        'verified_at'          => 'datetime',
        'finalized_at'         => 'datetime',
        'is_from_lawyer'       => 'boolean',
        'is_internal'          => 'boolean',
        'version'              => 'integer',
        'file_size'            => 'integer',
    ];

    // Accessor / Mutator for title
    public function getTitleAttribute()
    {
        return $this->name;
    }

    public function setTitleAttribute($value)
    {
        $this->attributes['name'] = $value;
    }

    // Accessor for human-readable file size
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

    // Relationships
    public function case()
    {
        return $this->belongsTo(LegalCase::class, 'case_id');
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function lawyer()
    {
        return $this->belongsTo(User::class, 'lawyer_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function request()
    {
        return $this->belongsTo(DocumentRequest::class, 'document_request_id');
    }

    public function parent()
    {
        return $this->belongsTo(Document::class, 'parent_id');
    }

    public function versions()
    {
        return $this->hasMany(Document::class, 'parent_id')->orderBy('version', 'desc');
    }

    public function documentVersions()
    {
        return $this->hasMany(DocumentVersion::class, 'document_id')->orderBy('version_number', 'desc');
    }

    public function comments()
    {
        return $this->hasMany(DocumentComment::class, 'document_id')->orderBy('created_at', 'asc');
    }

    public function finalizer()
    {
        return $this->belongsTo(User::class, 'finalized_by');
    }

    public function auditLogs()
    {
        return $this->hasMany(DocumentAuditLog::class, 'document_id')->orderBy('created_at', 'desc');
    }

    public function latestVersionRecord()
    {
        return $this->hasOne(DocumentVersion::class, 'document_id')->latestOfMany('version_number');
    }

    public function isFinal(): bool
    {
        return $this->collaboration_status === 'Final' || $this->documentVersions()->where('is_final', true)->exists();
    }
}
