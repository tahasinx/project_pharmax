<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Services\Platform\CompanyProvisioner;
use Illuminate\Console\Command;

class ProvisionCompanyCommand extends Command
{
    protected $signature = 'platform:provision {company} {--host-only}';

    protected $description = 'Provision a pharmacy database and hostname';

    public function handle(CompanyProvisioner $provisioner): int
    {
        $company = Company::query()->findOrFail($this->argument('company'));
        $provisioner->provision($company, (bool) $this->option('host-only'));
        $this->info($company->fresh()->provision_status);

        return self::SUCCESS;
    }
}
