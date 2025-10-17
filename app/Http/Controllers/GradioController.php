<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class GradioController extends Controller
{
    private const MAX_IMAGE_SIZE = 10240; // 10MB in KB
    private const TEMP_DISK = 'local';
    private const TEMP_DIRECTORY = 'temp';
    private const PYTHON_TIMEOUT = 180; // seconds

    public function classify(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'image' => [
                'required',
                'file',
                'mimes:jpeg,jpg,png,gif,webp,bmp',
                'max:' . self::MAX_IMAGE_SIZE
            ],
        ], [
            'image.required' => 'Image file is required',
            'image.file' => 'Invalid file upload',
            'image.mimes' => 'Image must be jpeg, jpg, png, gif, webp, or bmp',
            'image.max' => 'Image size must not exceed 10MB',
        ]);

        $tempPath = null;

        try {
            $tempPath = $request->file('image')->store(
                self::TEMP_DIRECTORY, 
                self::TEMP_DISK
            );
            $fullPath = Storage::disk(self::TEMP_DISK)->path($tempPath);

            $result = $this->executePythonScript($fullPath);

            return response()->json($result);

        } catch (ProcessFailedException $e) {
            Log::error('Python script execution failed', [
                'error' => $e->getMessage(),
                'output' => $e->getProcess()->getOutput(),
                'errorOutput' => $e->getProcess()->getErrorOutput(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to process image classification',
                'details' => config('app.debug') ? $e->getMessage() : null,
            ], 500);

        } catch (\Exception $e) {
            Log::error('Image classification error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'An unexpected error occurred',
                'details' => config('app.debug') ? $e->getMessage() : null,
            ], 500);

        } finally {
            if ($tempPath && Storage::disk(self::TEMP_DISK)->exists($tempPath)) {
                Storage::disk(self::TEMP_DISK)->delete($tempPath);
            }
        }
    }

    private function executePythonScript(string $imagePath): array
    {
        // Run Python directly instead of through batch file
        $pythonExe = 'C:\\Users\\chand\\AppData\\Local\\Programs\\Python\\Python310\\python.exe';
        $scriptPath = base_path('scripts/gradio_api.py');

        if (!file_exists($scriptPath)) {
            throw new \RuntimeException('Python script not found at: ' . $scriptPath);
        }

        if (!file_exists($pythonExe)) {
            throw new \RuntimeException('Python executable not found at: ' . $pythonExe);
        }

        // Normalize paths for Windows
        $scriptPath = str_replace('/', DIRECTORY_SEPARATOR, $scriptPath);
        $imagePath = str_replace('/', DIRECTORY_SEPARATOR, $imagePath);

        // Complete environment setup for Windows
        $env = array_merge($_ENV, [
            'SYSTEMROOT' => getenv('SYSTEMROOT') ?: 'C:\\Windows',
            'SystemRoot' => getenv('SystemRoot') ?: 'C:\\Windows',
            'WINDIR' => getenv('WINDIR') ?: 'C:\\Windows',
            'PYTHONHASHSEED' => '0',
            'PYTHONUNBUFFERED' => '1',
            'PYTHONIOENCODING' => 'utf-8',
            'PATH' => getenv('PATH'),
            'TEMP' => getenv('TEMP') ?: sys_get_temp_dir(),
            'TMP' => getenv('TMP') ?: sys_get_temp_dir(),
            'HOMEDRIVE' => getenv('HOMEDRIVE') ?: 'C:',
            'HOMEPATH' => getenv('HOMEPATH') ?: '\\Users\\chand',
        ]);

        // Create process
        $process = new Process(
            [$pythonExe, $scriptPath, $imagePath],
            base_path(),
            $env,
            null,
            self::PYTHON_TIMEOUT
        );

        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $output = trim($process->getOutput());
        
        if (empty($output)) {
            throw new \RuntimeException('Empty output from Python script');
        }

        // Extract JSON from output (in case there's extra text before it)
        // Look for the last line that starts with { or the entire output if it's JSON
        $lines = explode("\n", $output);
        $jsonOutput = null;
        
        // Try to find JSON in the output (search from end backwards)
        for ($i = count($lines) - 1; $i >= 0; $i--) {
            $line = trim($lines[$i]);
            if (strpos($line, '{') === 0) {
                $jsonOutput = $line;
                break;
            }
        }
        
        // If no JSON line found, try the whole output
        if ($jsonOutput === null) {
            $jsonOutput = $output;
        }

        $result = json_decode($jsonOutput, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException(
                'Invalid JSON output from Python script: ' . json_last_error_msg() . 
                ' | Output: ' . substr($output, 0, 200)
            );
        }

        return $result;
    }
}