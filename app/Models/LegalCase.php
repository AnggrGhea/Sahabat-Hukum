<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegalCase extends Model
{
    use HasFactory;

    protected $table = 'cases';

    protected $fillable = [
        'client_id',
        'lawyer_id',
        'consultation_id',
        'case_number',
        'case_type',
        'title',
        'summary',
        'status',
        'started_at',
    ];

    protected $casts = [
        'started_at' => 'date',
    ];

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function lawyer()
    {
        return $this->belongsTo(User::class, 'lawyer_id');
    }

    public function consultation()
    {
        return $this->belongsTo(Consultation::class, 'consultation_id');
    }

    public function progress()
    {
        return $this->hasMany(CaseProgress::class, 'case_id')->orderBy('progress_date', 'desc');
    }

    public function documents()
    {
        return $this->hasMany(Document::class, 'case_id');
    }
}
