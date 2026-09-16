<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentAuditLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'document_id',
        'case_id',
        'user_id',
        'action',
        'notes',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class, 'document_id');
    }

    public function case()
    {
        return $this->belongsTo(LegalCase::class, 'case_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function record(?int $documentId, ?int $caseId, ?int $userId, string $action, ?string $notes = null): self
    {
        return self::create([
            'document_id' => $documentId,
            'case_id'     => $caseId,
            'user_id'     => $userId,
            'action'      => $action,
            'notes'       => $notes,
            'ip_address'  => request()->ip(),
            'user_agent'  => request()->userAgent(),
            'created_at'  => now(),
        ]);
    }
}
