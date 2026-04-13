<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="refresh" content="5">
    <title>Present — {{ $presentation->title }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-950 text-white min-h-screen">
    <div class="max-w-5xl mx-auto px-4 py-8 space-y-8">
        <div class="flex flex-wrap items-start justify-between gap-6">
            <div>
                <p class="text-indigo-300 text-sm uppercase tracking-wider">Join this session</p>
                <p class="text-5xl sm:text-6xl font-mono font-bold tracking-[0.2em] mt-2">{{ $presentation->join_code }}</p>
                <p class="text-gray-400 text-sm mt-2 break-all">{{ $joinUrl }}</p>
            </div>
            <div class="bg-white p-3 rounded-xl shrink-0">
                <img src="{{ $qrImageUrl }}" width="280" height="280" alt="QR code to join" class="block" loading="lazy">
            </div>
        </div>

        <div class="flex flex-wrap gap-3">
            <form method="POST" action="{{ route('host.stop', $presentation->manage_token) }}" onsubmit="return confirm('End this session?');">
                @csrf
                <button type="submit" class="px-4 py-2 rounded-lg bg-gray-800 border border-gray-600 text-sm font-medium hover:bg-gray-700">
                    End presentation
                </button>
            </form>
            <a href="{{ route('host.show', $presentation->manage_token) }}" class="px-4 py-2 rounded-lg bg-indigo-600 text-sm font-medium hover:bg-indigo-500">
                Manage questions
            </a>
        </div>

        @if ($activeQuestion)
            <div class="rounded-2xl border border-gray-800 bg-gray-900/80 p-6">
                <h2 class="text-xl font-semibold mb-1">{{ $activeQuestion->prompt }}</h2>
                <p class="text-gray-400 text-sm mb-6">Total votes: {{ $totalVotes }} · refreshes every 5s</p>
                <div class="space-y-4">
                    @foreach ($totals as $row)
                        @php
                            $percent = $totalVotes > 0 ? round(($row['count'] / $totalVotes) * 100) : 0;
                        @endphp
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span>{{ $row['label'] }}</span>
                                <span class="text-gray-400">{{ $row['count'] }} ({{ $percent }}%)</span>
                            </div>
                            <div class="h-3 bg-gray-800 rounded-full overflow-hidden">
                                <div class="h-full bg-indigo-500 rounded-full transition-all" style="width: {{ $percent }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</body>
</html>
