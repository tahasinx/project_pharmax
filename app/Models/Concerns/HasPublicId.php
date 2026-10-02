<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Public, non-guessable UUID for routes and request payloads.
 * Internal bigint `id` remains the PK / FK target (ispx pattern).
 *
 * JSON/Inertia expose the public UUID as `id` so clients never see sequential ids.
 *
 * @mixin Model
 */
trait HasPublicId
{
    public static function bootHasPublicId(): void
    {
        static::creating(static function (Model $model): void {
            $column = $model->publicIdColumn();
            if (empty($model->{$column})) {
                $model->{$column} = (string) Str::uuid();
            }
        });
    }

    public function initializeHasPublicId(): void
    {
        $column = $this->publicIdColumn();
        if ($this->fillable !== [] && ! in_array($column, $this->fillable, true)) {
            $this->fillable[] = $column;
        }
    }

    public function publicIdColumn(): string
    {
        return Str::snake(class_basename($this)).'_id';
    }

    public function getRouteKeyName(): string
    {
        return $this->publicIdColumn();
    }

    public function publicId(): ?string
    {
        $column = $this->publicIdColumn();

        return $this->{$column} ? (string) $this->{$column} : null;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $array  = parent::toArray();
        $column = $this->publicIdColumn();
        if (array_key_exists('id', $array) && ! empty($array[$column] ?? null)) {
            $array['id'] = $array[$column];
        }

        return $array;
    }

    /**
     * Resolve a public UUID (or legacy numeric id) to the internal bigint primary key.
     */
    public static function localId(null|string|int $publicId): ?int
    {
        if ($publicId === null || $publicId === '') {
            return null;
        }

        if (is_numeric($publicId) && ! str_contains((string) $publicId, '-')) {
            $id = (int) $publicId;

            return static::query()->whereKey($id)->value('id');
        }

        $column = (new static)->publicIdColumn();
        $id     = static::query()->where($column, (string) $publicId)->value('id');

        return $id !== null ? (int) $id : null;
    }

    public static function localIdOrFail(null|string|int $publicId): int
    {
        $id = static::localId($publicId);
        if ($id === null) {
            abort(404);
        }

        return $id;
    }

    public static function findByPublicId(null|string|int $publicId): ?static
    {
        if ($publicId === null || $publicId === '') {
            return null;
        }

        if (is_numeric($publicId) && ! str_contains((string) $publicId, '-')) {
            return static::query()->find((int) $publicId);
        }

        return static::query()
            ->where((new static)->publicIdColumn(), (string) $publicId)
            ->first();
    }

    public static function findByPublicIdOrFail(null|string|int $publicId): static
    {
        return static::findByPublicId($publicId) ?? abort(404);
    }
}
