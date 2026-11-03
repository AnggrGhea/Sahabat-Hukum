<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'lawyer_id',
        'consultation_id',
        'case_id',
        'title',
        'status',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
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

    public function legalCase()
    {
        return $this->belongsTo(LegalCase::class, 'case_id');
    }

    /**
     * Get the formatted context label (Perkara or Konsultasi)
     */
    public function getContextTitleAttribute(): string
    {
        if ($this->legalCase) {
            $caseNum = $this->legalCase->case_number;
            $caseTitle = $this->legalCase->title;
            return $caseNum ? "Perkara: {$caseNum} — {$caseTitle}" : "Perkara: {$caseTitle}";
        }

        if ($this->consultation) {
            return "Konsultasi: " . $this->consultation->title;
        }

        return $this->title ?? 'Percakapan';
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'conversation_id')->orderBy('created_at', 'asc');
    }

    public function latestMessage()
    {
        return $this->hasOne(Message::class, 'conversation_id')->latestOfMany();
    }

    /**
     * Get unread messages count for a specific user
     */
    public function unreadCountFor(int $userId): int
    {
        return $this->messages()
            ->where('sender_id', '!=', $userId)
            ->where('is_read', false)
            ->count();
    }

    /**
     * Get the other participant relative to the current user
     */
    public function getOtherParticipant(int $userId): ?User
    {
        if ($this->client_id === $userId) {
            return $this->lawyer;
        }

        return $this->client;
    }
}
