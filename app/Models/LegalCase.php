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
        return $this->hasMany(Document::class, 'case_id')->orderBy('created_at', 'desc');
    }

    public function documentRequests()
    {
        return $this->hasMany(DocumentRequest::class, 'case_id')->orderBy('created_at', 'desc');
    }

    public function conversations()
    {
        return $this->hasMany(Conversation::class, 'case_id')->orderBy('last_message_at', 'desc');
    }

    public function caseLawyers()
    {
        return $this->hasMany(CaseLawyer::class, 'case_id');
    }

    public function teamMembers()
    {
        return $this->belongsToMany(User::class, 'case_lawyers', 'case_id', 'user_id')
            ->withPivot('role_in_case', 'assigned_by')
            ->withTimestamps();
    }

    public function allLawyerIds(): array
    {
        $ids = [$this->lawyer_id];
        $teamIds = $this->caseLawyers()->pluck('user_id')->toArray();
        return array_values(array_unique(array_filter(array_merge($ids, $teamIds))));
    }

    public function isLawyerAuthorized(?User $user): bool
    {
        if (!$user) return false;
        if ($user->isAdmin()) return true;
        if (!$user->isAdvokat()) return false;
        if ($this->lawyer_id === $user->id) return true;

        return $this->caseLawyers()->where('user_id', $user->id)->exists();
    }

    public function isLeadLawyer(?User $user): bool
    {
        if (!$user) return false;
        if ($user->isAdmin()) return true;
        return $this->lawyer_id === $user->id;
    }
}
