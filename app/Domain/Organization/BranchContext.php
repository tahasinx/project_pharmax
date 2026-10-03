<?php

namespace App\Domain\Organization;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class BranchContext
{
    /**
     * Concrete branch for writes (session selection, else home branch, else head office).
     */
    public static function id(): ?int
    {
        $user = auth()->user();
        if (! $user instanceof User) {
            return null;
        }

        $session = session('branch_id');
        if ($session && self::canUse((int) $session, $user)) {
            return (int) $session;
        }

        if ($user->branch_id) {
            return (int) $user->branch_id;
        }

        return Branch::query()->where('is_head_office', true)->value('id')
            ?? Branch::query()->orderBy('id')->value('id');
    }

    /**
     * Session-selected branch only (null when "All branches" is active).
     */
    public static function selectedId(): ?int
    {
        $session = session('branch_id');
        if (! $session) {
            return null;
        }

        $user = auth()->user();
        if (! $user instanceof User || ! self::canUse((int) $session, $user)) {
            return null;
        }

        return (int) $session;
    }

    public static function seesAll(): bool
    {
        $user = auth()->user();

        return $user instanceof User
            && ($user->hasRole('admin') || $user->can('view-all-branches'))
            && ! session('branch_id');
    }

    public static function canUse(int $branchId, User $user): bool
    {
        if ($user->hasRole('admin') || $user->can('view-all-branches') || $user->can('manage-branches')) {
            return true;
        }

        return (int) $user->branch_id === $branchId;
    }

    public static function canSwitch(User $user): bool
    {
        return $user->hasRole('admin')
            || $user->can('view-all-branches')
            || $user->can('manage-branches');
    }

    /**
     * Constrain a query to the active branch scope (no-op when seeing all).
     */
    public static function constrain(Builder $query, string $column = 'branch_id'): Builder
    {
        if (self::seesAll()) {
            return $query;
        }

        $id = self::id();
        if (! $id) {
            return $query;
        }

        return $query->where($column, $id);
    }

    /**
     * Stock rows may still be unassigned (null branch) — include those when scoped.
     */
    public static function constrainStock(Builder $query): Builder
    {
        if (self::seesAll()) {
            return $query;
        }

        $id = self::id();
        if (! $id) {
            return $query;
        }

        return $query->where(function ($inner) use ($id) {
            $inner->where('branch_id', $id)->orWhereNull('branch_id');
        });
    }

    /**
     * Shared Inertia payload for the header switcher.
     *
     * @return array{current: ?string, label: string, sees_all: bool, canSwitch: bool, options: list<array{id: string, name: string}>}
     */
    public static function shared(User $user): array
    {
        $options = Branch::query()
            ->where(function ($q) {
                $q->where('is_active', true)->orWhereNull('is_active');
            })
            ->orderBy('name')
            ->get(['id', 'branch_id', 'name'])
            ->map(fn (Branch $branch) => [
                'id'   => $branch->publicId(),
                'name' => $branch->name,
            ])
            ->values()
            ->all();

        $canSwitch = self::canSwitch($user);
        $seesAll   = self::seesAll();

        if ($seesAll) {
            return [
                'current'   => null,
                'label'     => 'All branches',
                'sees_all'  => true,
                'canSwitch' => $canSwitch,
                'options'   => $options,
            ];
        }

        $localId = self::selectedId() ?: ($user->branch_id ? (int) $user->branch_id : self::id());
        $branch  = $localId ? Branch::query()->find($localId) : null;

        return [
            'current'   => $branch?->publicId(),
            'label'     => $branch?->name ?: 'Branch',
            'sees_all'  => false,
            'canSwitch' => $canSwitch,
            'options'   => $options,
        ];
    }
}
