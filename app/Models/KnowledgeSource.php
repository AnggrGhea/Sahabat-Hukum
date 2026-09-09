<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KnowledgeSource extends Model
{
    use HasFactory;

    protected $table = 'knowledge_sources';

    protected $fillable = [
        'title',
        'description',
        'source_type',
        'file_path',
        'content',
        'status',
        'verified_by',
    ];

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
