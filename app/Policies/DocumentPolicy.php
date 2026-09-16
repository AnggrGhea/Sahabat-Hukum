<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\LegalCase;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DocumentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the document preview/metadata.
     */
    public function view(User $user, Document $document): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isAdvokat()) {
            return $document->lawyer_id === $user->id
                || ($document->case && $document->case->lawyer_id === $user->id);
        }

        if ($user->isKlien()) {
            return $document->client_id === $user->id
                || ($document->case && $document->case->client_id === $user->id);
        }

        return false;
    }

    /**
     * Determine whether the user can download the document.
     */
    public function download(User $user, Document $document): bool
    {
        return $this->view($user, $document);
    }

    /**
     * Determine whether the user can upload a document to a case.
     */
    public function upload(User $user, LegalCase $case): bool
    {
        if ($user->isAdmin()) {
            return false; // Admin manages system, client & lawyer handle legal case documents
        }

        if ($user->isKlien()) {
            return $case->client_id === $user->id;
        }

        if ($user->isAdvokat()) {
            return $case->lawyer_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can verify a document.
     */
    public function verify(User $user, Document $document): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isAdvokat()) {
            return $document->lawyer_id === $user->id
                || ($document->case && $document->case->lawyer_id === $user->id);
        }

        return false;
    }

    /**
     * Determine whether the user can reject a document.
     */
    public function reject(User $user, Document $document): bool
    {
        return $this->verify($user, $document);
    }

    /**
     * Determine whether the user can re-upload a rejected document.
     */
    public function reupload(User $user, Document $document): bool
    {
        if (!$user->isKlien()) {
            return false;
        }

        $isOwner = ($document->client_id === $user->id)
            || ($document->case && $document->case->client_id === $user->id);

        return $isOwner && ($document->status === 'Ditolak' || $document->status === 'Perlu Diperbaiki');
    }

    /**
     * Determine whether the user can request documents for a case.
     */
    public function request(User $user, LegalCase $case): bool
    {
        if ($user->isAdvokat()) {
            return $case->lawyer_id === $user->id;
        }

        return false;
    }
}
