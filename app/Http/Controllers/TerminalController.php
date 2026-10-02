<?php

namespace App\Http\Controllers;

use App\Services\SimpleCommandService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TerminalController extends Controller
{
    private SimpleCommandService $commandService;

    public function __construct(SimpleCommandService $commandService)
    {
        $this->commandService = $commandService;
    }

    /**
     * Show terminal interface
     */
    public function index()
    {
        return inertia('Terminal');
    }

    /**
     * Execute a command
     */
    public function execute(Request $request): JsonResponse
    {
        $request->validate([
            'command'           => 'required|string|max:100',
            'arguments'         => 'array',
            'arguments.*'       => 'string|max:50',
            'working_directory' => 'nullable|string|max:500',
        ]);

        $command          = $request->input('command');
        $arguments        = $request->input('arguments', []);
        $workingDirectory = $request->input('working_directory');

        $result = $this->commandService->execute($command, $arguments, $workingDirectory);

        return response()->json($result);
    }

    /**
     * Get available commands
     */
    public function getAvailableCommands(): JsonResponse
    {
        return response()->json([
            'success'  => true,
            'commands' => $this->commandService->getAvailableCommands(),
        ]);
    }

    /**
     * Get system information
     */
    public function getSystemInfo(): JsonResponse
    {
        $info                       = $this->commandService->getEnvironmentInfo();
        $info['available_commands'] = $this->commandService->getAvailableCommands();

        return response()->json([
            'success'     => true,
            'system_info' => $info,
        ]);
    }

    /**
     * Refresh command paths (clear cache and re-detect)
     */
    public function refreshCommandPaths(): JsonResponse
    {
        $paths = $this->commandService->refreshCommandPaths();

        return response()->json([
            'success' => true,
            'message' => 'Command paths refreshed successfully',
            'paths'   => $paths,
        ]);
    }
}
