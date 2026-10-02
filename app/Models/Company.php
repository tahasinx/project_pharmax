<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasPublicId;

    protected $connection = 'mysql_central';

    protected $fillable = [
        'name',
        'slug',
        'database_name',
        'email',
        'admin_email',
        'phone',
        'address',
        'status',
        'provision_status',
        'provision_step',
        'provision_error',
        'provision_log',
        'provisioned_at',
        'vhost_status',
        'ssl_status',
    ];

    protected $casts = [
        'provision_log'  => 'array',
        'provisioned_at' => 'datetime',
    ];

    public function isActive(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        $provision = $this->provision_status ?: 'active';

        return in_array($provision, ['active', 'degraded'], true);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(PlatformSubscription::class);
    }

    public function host(): string
    {
        return $this->slug.'.'.config('database.tenant.base_domain');
    }
}
