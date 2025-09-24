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
        try {
            $settings = Storage::get('settings.json');
            if ($settings) {
                $decoded = json_decode($settings, true);
                return (int) ($decoded['items_per_page'] ?? 15);
            }
        } catch (\Exception $e) {
            // Fallback to default if settings file doesn't exist or is invalid
        }

        return 15; // Default fallback
    }
}
