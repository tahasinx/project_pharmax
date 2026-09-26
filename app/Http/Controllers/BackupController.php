<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use ZipArchive;
use Carbon\Carbon;

class BackupController extends Controller
{
    public function __construct()
    {
    }

    /**
     * Display backup management page
     */
    public function index()
    {
        $backups = $this->getBackupList();

        return inertia('Backup/Index', [
            'backups' => $backups,
        ]);
    }

    /**
     * Create a new backup
     */
    public function create(Request $request)
    {
        try {
            $backupName = 'backup_' . Carbon::now()->format('Y_m_d_H_i_s');
            $backupPath = storage_path('app/backups/' . $backupName);

            // Create backup directory
            if (!file_exists($backupPath)) {
                mkdir($backupPath, 0755, true);
            }

            // Export database
            $this->exportDatabase($backupPath);

            // Export settings
            $this->exportSettings($backupPath);

            // Export uploaded files
            $this->exportUploads($backupPath);

            // Create zip archive
            $zipPath = $this->createZipArchive($backupPath, $backupName);

            // Clean up temporary directory
            $this->cleanupDirectory($backupPath);

            return response()->json([
                'success' => true,
                'message' => 'Backup created successfully',
                'backup_name' => $backupName,
                'download_url' => route('backup.download', $backupName),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create backup: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download backup file
     */
    public function download($backupName)
    {
        $backupPath = storage_path('app/backups/' . $backupName . '.zip');

        if (!file_exists($backupPath)) {
            abort(404, 'Backup file not found');
        }

        return response()->download($backupPath);
    }

    /**
     * Restore from backup
     */
    public function restore(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|mimes:zip',
        ]);

        try {
            $backupFile = $request->file('backup_file');
            $tempPath = storage_path('app/temp/restore_' . time());

            // Extract backup
            $this->extractBackup($backupFile, $tempPath);

            // Restore database
            $this->restoreDatabase($tempPath);

            // Restore settings
            $this->restoreSettings($tempPath);

            // Restore uploads
            $this->restoreUploads($tempPath);

            // Clean up
            $this->cleanupDirectory($tempPath);

            return response()->json([
                'success' => true,
                'message' => 'Backup restored successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to restore backup: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete backup file
     */
    public function destroy($backupName)
    {
        $backupPath = storage_path('app/backups/' . $backupName . '.zip');

        if (file_exists($backupPath)) {
            unlink($backupPath);
        }

        return response()->json([
            'success' => true,
            'message' => 'Backup deleted successfully',
        ]);
    }

    /**
     * Export database to SQL file
     */
    protected function exportDatabase($backupPath)
    {
        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port');

        $sqlFile = $backupPath . '/database.sql';

        $command = "mysqldump -h {$host} -P {$port} -u {$username} -p{$password} {$database} > {$sqlFile}";

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new \Exception('Database export failed');
        }
    }

    /**
     * Export settings files
     */
    protected function exportSettings($backupPath)
    {
        $settingsPath = $backupPath . '/settings';
        mkdir($settingsPath, 0755, true);

        // Copy settings.json
        if (file_exists(storage_path('app/settings.json'))) {
            copy(storage_path('app/settings.json'), $settingsPath . '/settings.json');
        }

        // Copy .env file
        if (file_exists(base_path('.env'))) {
            copy(base_path('.env'), $settingsPath . '/.env');
        }
    }

    /**
     * Export uploaded files
     */
    protected function exportUploads($backupPath)
    {
        $uploadsPath = $backupPath . '/uploads';
        $sourcePath = storage_path('app/public');

        if (is_dir($sourcePath)) {
            $this->copyDirectory($sourcePath, $uploadsPath);
        }
    }

    /**
     * Create ZIP archive
     */
    protected function createZipArchive($backupPath, $backupName)
    {
        $zipPath = storage_path('app/backups/' . $backupName . '.zip');

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE) !== TRUE) {
            throw new \Exception('Cannot create ZIP file');
        }

        $this->addDirectoryToZip($zip, $backupPath, '');
        $zip->close();

        return $zipPath;
    }

    /**
     * Add directory to ZIP recursively
     */
    protected function addDirectoryToZip($zip, $dir, $zipPath)
    {
        $files = scandir($dir);

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;

            $filePath = $dir . '/' . $file;
            $zipFilePath = $zipPath . '/' . $file;

            if (is_dir($filePath)) {
                $zip->addEmptyDir($zipFilePath);
                $this->addDirectoryToZip($zip, $filePath, $zipFilePath);
            } else {
                $zip->addFile($filePath, $zipFilePath);
            }
        }
    }

    /**
     * Extract backup file
     */
    protected function extractBackup($backupFile, $tempPath)
    {
        mkdir($tempPath, 0755, true);

        $zip = new ZipArchive();
        if ($zip->open($backupFile->getPathname()) !== TRUE) {
            throw new \Exception('Cannot open backup file');
        }

        $zip->extractTo($tempPath);
        $zip->close();
    }

    /**
     * Restore database from SQL file
     */
    protected function restoreDatabase($tempPath)
    {
        $sqlFile = $tempPath . '/database.sql';

        if (!file_exists($sqlFile)) {
            throw new \Exception('Database file not found in backup');
        }

        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port');

        $command = "mysql -h {$host} -P {$port} -u {$username} -p{$password} {$database} < {$sqlFile}";

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new \Exception('Database restore failed');
        }
    }

    /**
     * Restore settings files
     */
    protected function restoreSettings($tempPath)
    {
        $settingsPath = $tempPath . '/settings';

        if (is_dir($settingsPath)) {
            // Restore settings.json
            if (file_exists($settingsPath . '/settings.json')) {
                copy($settingsPath . '/settings.json', storage_path('app/settings.json'));
            }

            // Restore .env file
            if (file_exists($settingsPath . '/.env')) {
                copy($settingsPath . '/.env', base_path('.env'));
            }
        }
    }

    /**
     * Restore uploaded files
     */
    protected function restoreUploads($tempPath)
    {
        $uploadsPath = $tempPath . '/uploads';
        $targetPath = storage_path('app/public');

        if (is_dir($uploadsPath)) {
            $this->copyDirectory($uploadsPath, $targetPath);
        }
    }

    /**
     * Copy directory recursively
     */
    protected function copyDirectory($source, $destination)
    {
        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        $files = scandir($source);

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;

            $sourceFile = $source . '/' . $file;
            $destFile = $destination . '/' . $file;

            if (is_dir($sourceFile)) {
                $this->copyDirectory($sourceFile, $destFile);
            } else {
                copy($sourceFile, $destFile);
            }
        }
    }

    /**
     * Clean up directory
     */
    protected function cleanupDirectory($path)
    {
        if (is_dir($path)) {
            $files = array_diff(scandir($path), ['.', '..']);

            foreach ($files as $file) {
                $filePath = $path . '/' . $file;

                if (is_dir($filePath)) {
                    $this->cleanupDirectory($filePath);
                } else {
                    unlink($filePath);
                }
            }

            rmdir($path);
        }
    }

    /**
     * Get list of available backups
     */
    protected function getBackupList()
    {
        $backupDir = storage_path('app/backups');
        $backups = [];

        if (is_dir($backupDir)) {
            $files = scandir($backupDir);

            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'zip') {
                    $filePath = $backupDir . '/' . $file;
                    $backups[] = [
                        'name' => pathinfo($file, PATHINFO_FILENAME),
                        'size' => filesize($filePath),
                        'created_at' => date('Y-m-d H:i:s', filemtime($filePath)),
                        'download_url' => route('backup.download', pathinfo($file, PATHINFO_FILENAME)),
                    ];
                }
            }
        }

        // Sort by creation date (newest first)
        usort($backups, function ($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        return $backups;
    }
}
