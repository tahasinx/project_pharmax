<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;

trait HasSettingsPagination
{
    /**
     * Get items per page from settings
     */
    protected function getItemsPerPage(): int
    {
        $requested = (int) request('per_page');
        if (in_array($requested, [10, 25, 50, 100], true)) {
            return $requested;
        }

        try {
            $settings = Storage::get('settings.json');
            if ($settings) {
                $decoded = json_decode($settings, true);
                return (int) ($decoded['items_per_page'] ?? 15);
            }
        } catch (\Exception $e) {
        }

        return 15;
    }
}
