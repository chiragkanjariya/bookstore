<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LogViewerController extends Controller
{
    /**
     * Number of log entries shown per page.
     */
    private const PER_PAGE = 50;

    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * Display the application logs with level filtering and search.
     */
    public function index(Request $request)
    {
        $files = $this->logFiles();

        // Resolve the selected file (defaults to the most recent one).
        $selected = $request->query('file');
        $path = $this->safePath($selected ?: ($files[0] ?? 'laravel.log'));

        $entries = collect();
        $stats = ['total' => 0, 'ERROR' => 0, 'WARNING' => 0, 'INFO' => 0, 'DEBUG' => 0, 'OTHER' => 0];
        $fileSize = 0;

        if ($path && file_exists($path)) {
            $fileSize = filesize($path);
            $entries = $this->parseEntries(file_get_contents($path));

            // Tally stats before filtering so the counts reflect the whole file.
            foreach ($entries as $entry) {
                $stats['total']++;
                $bucket = match ($entry['level']) {
                    'ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY' => 'ERROR',
                    'WARNING' => 'WARNING',
                    'INFO', 'NOTICE' => 'INFO',
                    'DEBUG' => 'DEBUG',
                    default => 'OTHER',
                };
                $stats[$bucket]++;
            }
        }

        // Filter by level.
        $level = strtoupper((string) $request->query('level', ''));
        if ($level !== '') {
            $entries = $entries->filter(fn ($e) => $e['level'] === $level);
        }

        // Filter by search term (matches message or context).
        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $entries = $entries->filter(
                fn ($e) => Str::contains(Str::lower($e['message'] . ' ' . $e['context']), Str::lower($search))
            );
        }

        // Newest entries first.
        $entries = $entries->reverse()->values();

        // Manual pagination over the in-memory collection.
        $page = LengthAwarePaginator::resolveCurrentPage();
        $paginated = new LengthAwarePaginator(
            $entries->forPage($page, self::PER_PAGE)->values(),
            $entries->count(),
            self::PER_PAGE,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $currentFile = $path ? basename($path) : null;

        return view('admin.logs.index', [
            'entries' => $paginated,
            'files' => $files,
            'currentFile' => $currentFile,
            'stats' => $stats,
            'fileSize' => $fileSize,
            'level' => $level,
            'search' => $search,
        ]);
    }

    /**
     * Download the raw log file.
     */
    public function download(Request $request)
    {
        $path = $this->safePath($request->query('file', 'laravel.log'));

        if (!$path || !file_exists($path)) {
            return redirect()->route('admin.logs.index')->with('error', 'Log file not found.');
        }

        return response()->download($path);
    }

    /**
     * Clear (empty) the contents of a log file without deleting it.
     */
    public function clear(Request $request)
    {
        $path = $this->safePath($request->input('file', 'laravel.log'));

        if (!$path || !file_exists($path)) {
            return redirect()->route('admin.logs.index')->with('error', 'Log file not found.');
        }

        file_put_contents($path, '');

        Log::info('Admin cleared log file', [
            'file' => basename($path),
            'admin_id' => optional($request->user())->id,
        ]);

        return redirect()
            ->route('admin.logs.index', ['file' => basename($path)])
            ->with('success', 'Log file "' . basename($path) . '" cleared.');
    }

    /**
     * Delete a log file entirely.
     */
    public function delete(Request $request)
    {
        $path = $this->safePath($request->input('file', 'laravel.log'));

        if (!$path || !file_exists($path)) {
            return redirect()->route('admin.logs.index')->with('error', 'Log file not found.');
        }

        $name = basename($path);

        // Record the deletion before removing the file so the entry survives
        // in a freshly recreated log rather than the file being deleted.
        Log::info('Admin deleted log file', [
            'file' => $name,
            'admin_id' => optional($request->user())->id,
        ]);

        @unlink($path);

        return redirect()->route('admin.logs.index')->with('success', 'Log file "' . $name . '" deleted.');
    }

    /**
     * List available *.log files in the logs directory, newest first.
     *
     * @return array<int, string>
     */
    private function logFiles(): array
    {
        $files = glob(storage_path('logs') . DIRECTORY_SEPARATOR . '*.log') ?: [];

        // Sort by last-modified time, newest first.
        usort($files, fn ($a, $b) => filemtime($b) <=> filemtime($a));

        return array_map('basename', $files);
    }

    /**
     * Resolve a requested file name to a safe absolute path inside the logs
     * directory, guarding against path traversal. Returns null if invalid.
     */
    private function safePath(?string $file): ?string
    {
        $file = basename((string) $file); // strip any directory components

        if ($file === '' || !Str::endsWith($file, '.log')) {
            return null;
        }

        $logDir = realpath(storage_path('logs'));
        $path = storage_path('logs') . DIRECTORY_SEPARATOR . $file;
        $real = realpath($path);

        // For an existing file, confirm it actually resolves inside the logs dir.
        if ($real !== false && $logDir !== false && !Str::startsWith($real, $logDir)) {
            return null;
        }

        return $path;
    }

    /**
     * Parse raw log content into structured entries.
     *
     * @return \Illuminate\Support\Collection<int, array{date:string,channel:string,level:string,message:string,context:string}>
     */
    private function parseEntries(string $content)
    {
        // Split on the start of each log line: [YYYY-MM-DD HH:MM:SS ...].
        $blocks = preg_split(
            '/(?=^\[\d{4}-\d{2}-\d{2}[ T]\d{2}:\d{2}:\d{2})/m',
            $content,
            -1,
            PREG_SPLIT_NO_EMPTY
        ) ?: [];

        $entries = collect();

        foreach ($blocks as $block) {
            if (!preg_match('/^\[(?<date>[^\]]+)\]\s+(?<channel>[^.]+)\.(?<level>[A-Z]+):\s?(?<body>.*)$/s', $block, $m)) {
                continue;
            }

            $body = rtrim($m['body']);

            // Separate the first line (message) from any JSON context / stack trace.
            $newlinePos = strpos($body, "\n");
            if ($newlinePos !== false) {
                $message = substr($body, 0, $newlinePos);
                $context = trim(substr($body, $newlinePos + 1));
            } else {
                $message = $body;
                $context = '';
            }

            $entries->push([
                'date' => trim($m['date']),
                'channel' => trim($m['channel']),
                'level' => strtoupper($m['level']),
                'message' => trim($message),
                'context' => $context,
            ]);
        }

        return $entries;
    }
}
