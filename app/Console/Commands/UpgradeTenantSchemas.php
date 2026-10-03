<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Services\Platform\SchemaCompare;
use Illuminate\Console\Command;
use Throwable;

class UpgradeTenantSchemas extends Command
{
    protected $signature = 'tenants:migrate
        {--company= : Public id or numeric id of one company}
        {--central : Also run central registry migrations}
        {--all : Upgrade every company database}';

    protected $description = 'Run pending tenant (and optional central) schema migrations on cloud/local installs';

    public function handle(SchemaCompare $compare): int
    {
        $ok = true;

        if ($this->option('central')) {
            try {
                $output = $compare->upgradeCentral();
                $this->info('[central] '.$output);
            } catch (Throwable $e) {
                $ok = false;
                $this->error('[central] '.$e->getMessage());
            }
        }

        $companies = collect();
        if ($this->option('all')) {
            $companies = Company::query()->whereNotNull('database_name')->where('database_name', '!=', '')->get();
        } elseif ($id = $this->option('company')) {
            $query = Company::query()->where('id', $id);
            if (is_string($id) && ! ctype_digit($id)) {
                $query->orWhere('slug', $id);
            }
            $company = $query->first();
            $companies = collect([$company])->filter();
            if ($companies->isEmpty()) {
                $this->error('Company not found: '.$id);

                return self::FAILURE;
            }
        } else {
            $this->warn('Pass --all or --company=ID (optional --central).');

            return self::INVALID;
        }

        foreach ($companies as $company) {
            try {
                $output = $compare->upgradeCompany($company);
                $this->info('['.$company->slug.'] '.$output);
            } catch (Throwable $e) {
                $ok = false;
                $this->error('['.$company->slug.'] '.$e->getMessage());
            }
        }

        return $ok ? self::SUCCESS : self::FAILURE;
    }
}
