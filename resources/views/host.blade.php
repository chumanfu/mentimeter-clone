<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $presentation->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="p-4 bg-green-50 text-green-800 rounded-lg text-sm">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 space-y-4">
                    <p class="text-sm text-gray-600">
                        Logged in as <strong>{{ auth()->user()->name }}</strong>
                    </p>

                    @if (! $presentation->is_live)
                        <div class="border border-amber-200 bg-amber-50 rounded-lg p-4">
                            <p class="text-sm text-amber-900 font-medium mb-3">This presentation is not live yet. Participants cannot join until you start it.</p>
                            <form method="POST" action="{{ route('host.start', $presentation->manage_token) }}">
                                @csrf
                                <x-primary-button>{{ __('Start presentation') }}</x-primary-button>
                            </form>
                        </div>
                    @else
                        <div class="flex flex-wrap items-center gap-4">
                            <p class="text-sm text-gray-700">
                                Join code: <span class="font-mono text-lg font-bold tracking-widest">{{ $presentation->join_code }}</span>
                            </p>
                            <a href="{{ route('host.present', $presentation->manage_token) }}"
                               class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500">
                                {{ __('Open presenter view (QR)') }}
                            </a>
                            <form method="POST" action="{{ route('host.stop', $presentation->manage_token) }}" onsubmit="return confirm('End this session? Participants will no longer be able to join or vote.');">
                                @csrf
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                                    {{ __('End presentation') }}
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>

            @if ($activeQuestion)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Active question</h3>
                        <p class="text-gray-800 mb-4">{{ $activeQuestion->prompt }}</p>
                        <p class="text-sm text-gray-500 mb-3">Total votes: {{ $totalVotes }}</p>
                        <div class="space-y-3">
                            @foreach ($totals as $row)
                                @php
                                    $percent = $totalVotes > 0 ? round(($row['count'] / $totalVotes) * 100) : 0;
                                @endphp
                                <div>
                                    <div class="flex justify-between text-sm mb-1">
                                        <span>{{ $row['label'] }}</span>
                                        <span class="text-gray-500">{{ $row['count'] }} ({{ $percent }}%)</span>
                                    </div>
                                    <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-indigo-500 rounded-full transition-all" style="width: {{ $percent }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            @if ($presentation->questions->count() > 1)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 space-y-2">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">All questions</h3>
                        @foreach ($presentation->questions as $question)
                            <form method="POST" action="{{ route('host.questions.activate', [$presentation->manage_token, $question]) }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 rounded-md border {{ $question->is_active ? 'border-indigo-500 bg-indigo-50 text-indigo-900' : 'border-gray-200 hover:bg-gray-50' }}">
                                    {{ $question->is_active ? '● ' : '' }}{{ $question->prompt }}
                                </button>
                            </form>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($presentation->responses_count > 0)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-red-100">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('Reset results') }}</h3>
                        <p class="text-sm text-gray-600 mb-4">
                            {{ __('Remove all votes so you can run the same presentation again with a clean tally. This does not delete your questions or answer options.') }}
                        </p>
                        <form method="POST" action="{{ route('presentations.reset-results', $presentation) }}"
                              onsubmit="return confirm(@js(__('Clear all votes for every question? This cannot be undone.')));">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-white border border-red-300 rounded-md font-semibold text-xs text-red-700 uppercase tracking-widest hover:bg-red-50">
                                {{ __('Clear all results') }}
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            <p class="text-sm">
                <a href="{{ route('dashboard') }}" class="text-indigo-600 hover:text-indigo-800">← Back to dashboard</a>
            </p>
        </div>
    </div>
</x-app-layout>
