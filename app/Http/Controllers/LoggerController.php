<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Yajra\DataTables\DataTables;

class LoggerController extends Controller
{
    public function index(Request $request)
    {
        $logDirectory = storage_path('logs');
        $validLogFiles = $this->getValidLogFiles($logDirectory);

        // Retrieve the selected file from the request, defaulting to 'laravel.log'
        $selectedFile = $request->get('file', 'laravel.log');
        $selectedFilePath = $logDirectory . '/' . $selectedFile;

        // Check if the selected file exists and read its contents
        $logFileContents = File::exists($selectedFilePath) ? File::get($selectedFilePath) : 'Log file not found.';

        return view('index', [
            'logFiles' => $validLogFiles,
            'selectedFile' => $selectedFile,
            'logFileContents' => $logFileContents, // Pass log file contents to the view
        ]);
    }

    public function fetchLogContents(Request $request)
    {
        $file = $request->get('file', 'laravel.log'); // Default to 'laravel.log'
        $logDirectory = storage_path('logs');
        $filePath = $logDirectory . '/' . $file;

        // Check if the file exists and get its contents
        if (File::exists($filePath)) {
            $contents = File::get($filePath);
        } else {
            $contents = 'Log file not found.';
        }

        return response()->json([
            'logFileContents' => $contents
        ]);
    }

    private function getValidLogFiles($logDirectory)
    {
        // Get all log files from the directory
        $logFiles = File::files($logDirectory);

        // Extract file names from the file objects
        return array_map(function ($file) {
            return basename($file);
        }, $logFiles);
    }

    public function data(Request $request)
    {
        $filter = $request->get('filter', '');
        $selectedFile = $request->get('file', 'laravel.log');

        $logDirectory = storage_path('logs');
        $validLogFiles = $this->getValidLogFiles($logDirectory);

        if (!in_array($selectedFile, $validLogFiles)) {
            $selectedFile = 'laravel.log';
        }

        $filePath = $logDirectory . '/' . $selectedFile;
        $logs = $this->parseLogFile($filePath, $filter);

        $dataTable = DataTables::of($logs)
            ->editColumn('level', function ($log) {
                $level = ucfirst($log['level']);
                return $this->getLevelHtml($log['level'], $level);
            })
            ->editColumn('time', function ($log) {
                return '<span class="text-muted">' . $this->formatLogDate($log['date']) . '</span>';
            })
            ->editColumn('env', function ($log) {
                $env = htmlspecialchars($log['env'], ENT_QUOTES, 'UTF-8');

                // Convert the environment value to lowercase for case-insensitive comparison
                $envLower = strtolower($env);

                // Determine the badge class based on the environment
                $badgeClass = 'bg-secondary'; // Default to secondary
                if ($envLower === 'production') {
                    $badgeClass = 'bg-warning'; // Set to warning for production
                } elseif ($envLower === 'local') {
                    $badgeClass = 'bg-secondary'; // Set to secondary for local
                }

                return '<span class="badge rounded-pill ' . $badgeClass . '">' . $env . '</span>';
            })
            ->editColumn('description', function ($log) {
                return $this->formatDescription($log['message']);
            })
            ->rawColumns([
                'level',
                'time',
                'env',
                'description'
            ])
            ->make(true);

        return $dataTable;
    }

    private function formatDescription($message)
    {
        // Escape quotes and backticks, and remove unwanted characters
        $sanitizedData = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
        $sanitizedData = str_replace(['<', '>', '`', '(', ')'], '', $sanitizedData);

        // data-bs-toggle="tooltip" data-bs-placement="top" title="' . $sanitizedData . '"
        return '<div class="d-flex justify-content-between">
                <div class="truncate">
                    ' . $sanitizedData . '
                    <button class="copy-btn" data-copy-text="' . $sanitizedData . '">
                        <i class="fa fa-copy"></i>
                    </button>
                </div>
            </div>';
    }

    private function getLevelHtml($level, $displayLevel)
    {
        $iconHtml = match (strtolower($level)) {
            'error' => '<i class="fa fa-exclamation-circle"></i>',
            'info' => '<i class="fa fa-info-circle"></i>',
            'warning' => '<i class="fa fa-exclamation-triangle"></i>',
            'debug' => '<i class="fa fa-info-circle"></i>',
            default => '',
        };

        $colorClass = match (strtolower($level)) {
            'error' => ' fw-bold text-danger',
            'info' => ' fw-bold text-info',
            'warning' => ' fw-bold text-warning',
            'debug' => ' fw-bold text-info',
            default => '',
        };

        return "<span class=\"{$colorClass} d-flex align-items-center\"><span class=\"me-2\">{$iconHtml}</span>{$displayLevel}</span>";
    }

    private function parseLogFile($filePath, $filter)
    {
        $logEntries = [];
        $fileContents = File::get($filePath);
        $lines = explode("\n", $fileContents);

        foreach ($lines as $line) {
            if ($line) {
                // Update the regex to capture environment if needed
                if (preg_match('/\[(.*?)\] (.*?)\.(.*?)\: (.*)/', $line, $matches)) {
                    $date = $matches[1];
                    $level = strtolower($matches[3]);
                    $env = ucfirst(strtolower($matches[2])); // Assuming env is captured in the third match
                    $message = $matches[4];
                    $errorType = $this->getErrorType($level);

                    if (!$filter || $level === $filter) {
                        $logEntries[] = [
                            'date' => $date,
                            'level' => $level,
                            'env' => $env, // Include environment in the log entry
                            'message' => $message,
                            'type' => $errorType,
                        ];
                    }
                }
            }
        }

        return $logEntries;
    }

    private function formatLogDate($dateString)
    {
        try {
            $date = \DateTime::createFromFormat('Y-m-d H:i:s', $dateString);
            if ($date === false) {
                throw new \Exception('Date parsing failed.');
            }
            return $date->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            return 'Invalid Date';
        }
    }

    private function getErrorType($level)
    {
        $errorTypes = [
            'debug' => 'Debug',
            'info' => 'Info',
            'warning' => 'Warning',
            'error' => 'Error'
        ];

        return $errorTypes[$level] ?? 'Unknown';
    }

    // Clear the log file
    public function clearLogs(Request $request)
    {
        $logDirectory = storage_path('logs');
        $validLogFiles = $this->getValidLogFiles($logDirectory);

        // Retrieve the selected file from the request, defaulting to 'laravel.log'
        $selectedFile = $request->get('file', 'laravel.log');
        $selectedFilePath = $logDirectory . '/' . $selectedFile;

        // Check if the selected file exists
        if (!in_array($selectedFile, $validLogFiles)) {
            return response()->json(['error' => 'Invalid log file selected.'], 400);
        }

        // Check if the selected file exists and clear its contents
        if (File::exists($selectedFilePath)) {
            File::put($selectedFilePath, ''); // Write an empty string to clear the file
            return response()->json(['message' => 'Log file cleared successfully!']);
        }

        return response()->json(['error' => 'Log file not found.'], 404);
    }

    // Download the log file
    public function downloadLogs($file)
    {
        $logFile = storage_path('logs/' . $file);

        if (!file_exists($logFile)) {
            abort(404, 'File not found.');
        }

        return response()->download($logFile);
    }
}
