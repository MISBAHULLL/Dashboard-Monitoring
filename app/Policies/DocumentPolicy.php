<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;

class DocumentPolicy
{
    /**
     * Semua user yang login bisa lihat daftar dokumen.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isMember();
    }

    /**
     * Semua user yang login bisa lihat detail dokumen.
     */
    public function view(User $user, Document $document): bool
    {
        return $user->isAdmin() || $user->isMember();
    }

    /**
     * Hanya admin yang bisa membuat dokumen.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Hanya admin yang bisa mengupdate dokumen.
     */
    public function update(User $user, Document $document): bool
    {
        return $user->isAdmin();
    }

    /**
     * Hanya admin yang bisa menghapus dokumen.
     */
    public function delete(User $user, Document $document): bool
    {
        return $user->isAdmin();
    }

    public function restore(User $user, Document $document): bool
    {
        return $user->isAdmin();
    }

    public function restoreAny(User $user): bool
    {
        return $user->isAdmin();
    }
}
