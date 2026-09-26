<?php

namespace App\Domain\Organization;

use App\Models\User;

class BranchContext
{
    public static function id(): ?int
    {
        $user = auth()->user();
        if (!$user instanceof User) {
            return null;
        }

        $session = session('branch_id');
        if ($session && self::canUse((int) $session, $user)) {
            return (int) $session;
        }

        return $user->branch_id ? (int) $user->branch_id : null;
    }

    public static function seesAll(): bool
    {
        $user = auth()->user();

        return $user instanceof User
            && ($user->hasRole('admin') || $user->can('view-all-branches'))
            && !session('branch_id');
    }

    public static function canUse(int $branchId, User $user): bool
    {
        if ($user->hasRole('admin') || $user->can('view-all-branches') || $user->can('manage-branches')) {
            return true;
        }

        return (int) $user->branch_id === $branchId;
    }
}
