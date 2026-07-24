@extends('layouts.admin')

@section('title', 'Application Logs')
@section('breadcrumb', 'Application Logs')

@php
    if (!function_exists('log_level_badge')) {
        function log_level_badge($level) {
            return match ($level) {
                'ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY' => 'bg-red-100 text-red-800',
                'WARNING' => 'bg-yellow-100 text-yellow-800',
                'INFO', 'NOTICE' => 'bg-blue-100 text-blue-800',
                'DEBUG' => 'bg-gray-100 text-gray-700',
                default => 'bg-purple-100 text-purple-800',
            };
        }
    }
@endphp

@section('content')
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex flex-wrap justify-between items-center gap-4 mb-6">
            <div>
                <h3 class="text-2xl font-semibold text-gray-800">Application Logs</h3>
                <p class="mt-1 text-sm text-gray-600">
                    View, search, download, clear or delete application log files.
                </p>
            </div>

            @if ($currentFile)
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.logs.download', ['file' => $currentFile]) }}"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-[#00BDE0] rounded-lg hover:bg-[#00a5c4] transition-colors">
                        Download
                    </a>

                    <form action="{{ route('admin.logs.clear') }}" method="POST"
                        onsubmit="return confirm('Clear all entries in {{ $currentFile }}? This cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="file" value="{{ $currentFile }}">
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 text-sm font-medium text-yellow-800 bg-yellow-100 rounded-lg hover:bg-yellow-200 transition-colors">
                            Clear
                        </button>
                    </form>

                    <form action="{{ route('admin.logs.delete') }}" method="POST"
                        onsubmit="return confirm('Delete the log file {{ $currentFile }}? This cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="file" value="{{ $currentFile }}">
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 text-sm font-medium text-red-800 bg-red-100 rounded-lg hover:bg-red-200 transition-colors">
                            Delete
                        </button>
                    </form>
                </div>
            @endif
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow-sm p-4">
                <p class="text-xs font-medium text-gray-500 uppercase">Total</p>
                <p class="mt-1 text-2xl font-semibold text-gray-800">{{ number_format($stats['total']) }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-4">
                <p class="text-xs font-medium text-gray-500 uppercase">Errors</p>
                <p class="mt-1 text-2xl font-semibold text-red-600">{{ number_format($stats['ERROR']) }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-4">
                <p class="text-xs font-medium text-gray-500 uppercase">Warnings</p>
                <p class="mt-1 text-2xl font-semibold text-yellow-600">{{ number_format($stats['WARNING']) }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-4">
                <p class="text-xs font-medium text-gray-500 uppercase">Info</p>
                <p class="mt-1 text-2xl font-semibold text-blue-600">{{ number_format($stats['INFO']) }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-4">
                <p class="text-xs font-medium text-gray-500 uppercase">Debug</p>
                <p class="mt-1 text-2xl font-semibold text-gray-600">{{ number_format($stats['DEBUG']) }}</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <form method="GET" action="{{ route('admin.logs.index') }}"
                class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Log File</label>
                    <select name="file" onchange="this.form.submit()"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#00BDE0]">
                        @forelse ($files as $file)
                            <option value="{{ $file }}" {{ $currentFile === $file ? 'selected' : '' }}>{{ $file }}</option>
                        @empty
                            <option value="">No log files</option>
                        @endforelse
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Level</label>
                    <select name="level"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#00BDE0]">
                        <option value="">All Levels</option>
                        @foreach (['ERROR', 'WARNING', 'INFO', 'DEBUG', 'NOTICE', 'CRITICAL'] as $lvl)
                            <option value="{{ $lvl }}" {{ $level === $lvl ? 'selected' : '' }}>{{ ucfirst(strtolower($lvl)) }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Message or context..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#00BDE0]">
                </div>

                <div class="flex gap-2">
                    <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-[#00BDE0] rounded-lg hover:bg-[#00a5c4] transition-colors">
                        Filter
                    </button>
                    <a href="{{ route('admin.logs.index', ['file' => $currentFile]) }}"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Entries -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            @forelse ($entries as $entry)
                <div class="border-b border-gray-100 last:border-b-0">
                    <div class="flex items-start gap-3 p-4 hover:bg-gray-50">
                        <span class="shrink-0 mt-0.5 inline-flex px-2 py-0.5 text-xs font-semibold rounded {{ log_level_badge($entry['level']) }}">
                            {{ $entry['level'] }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                                <span class="text-xs text-gray-400 font-mono">{{ $entry['date'] }}</span>
                                <span class="text-xs text-gray-400">{{ $entry['channel'] }}</span>
                            </div>
                            <p class="mt-1 text-sm text-gray-800 break-words">{{ $entry['message'] }}</p>

                            @if (!empty($entry['context']))
                                <details class="mt-2">
                                    <summary class="text-xs text-[#00BDE0] cursor-pointer select-none">View context / trace</summary>
                                    <pre class="mt-2 p-3 bg-gray-900 text-gray-100 text-xs rounded-md overflow-x-auto whitespace-pre-wrap break-words">{{ $entry['context'] }}</pre>
                                </details>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-12 text-center text-gray-500">
                    <p class="text-sm">No log entries found{{ $search || $level ? ' for the current filters' : '' }}.</p>
                </div>
            @endforelse
        </div>

        @if ($entries->hasPages())
            <div class="mt-6">
                {{ $entries->links() }}
            </div>
        @endif
    </div>
@endsection
