<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'case_id',
        'requested_by',
        'client_id',
        'title',
        'document_type',
        'description',
        'due_date',
        'priority',
        'status',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function case()
    {
        return $this->belongsTo(LegalCase::class, 'case_id');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function lawyer()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function documents()
    {
        return $this->hasMany(Document::class, 'document_request_id');
    }
}
