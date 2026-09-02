<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaseProgress extends Model
{
    use HasFactory;

    protected $table = 'case_progress';

    protected $fillable = [
        'case_id',
        'title',
        'description',
        'progress_date',
        'created_by',
    ];

    protected $casts = [
        'progress_date' => 'date',
    ];

    public function case()
    {
        return $this->belongsTo(LegalCase::class, 'case_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
