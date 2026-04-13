<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Your presentations') }}
            </h2>
            <a href="{{ route('presentations.create') }}"
               class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                {{ __('New presentation') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12" x-data="presentationSummary" @keydown.escape.window="if (open) closeModal()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="p-4 bg-green-50 text-green-800 rounded-lg text-sm">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if ($presentations->isEmpty())
                        <p class="text-gray-600">You have no presentations yet. Create one to get a host link and questions.</p>
                    @else
                        <ul class="divide-y divide-gray-200">
                            @foreach ($presentations as $presentation)
                                <li class="py-4 flex flex-wrap items-center justify-between gap-4">
                                    <div
                                        class="flex-1 min-w-0 @if (! $presentation->is_live) cursor-pointer hover:bg-gray-50 -m-2 p-2 rounded-md @endif"
                                        @if (! $presentation->is_live)
                                            @click="loadSummary(@js(route('presentations.summary', $presentation)))"
                                        @endif
                                    >
                                        <p class="font-medium text-gray-900">{{ $presentation->title }}</p>
                                        <p class="text-sm text-gray-500">
                                            @if ($presentation->is_live)
                                                <span class="text-green-600 font-medium">{{ __('Live') }}</span>
                                                — {{ __('code') }} {{ $presentation->join_code }}
                                            @elseif ($presentation->responses_count > 0)
                                                <span class="text-gray-700 font-medium">{{ __('Ended') }}</span>
                                                — {{ __(':count responses', ['count' => $presentation->responses_count]) }}
                                                <span class="text-indigo-600"> · {{ __('Click row for summary') }}</span>
                                            @else
                                                <span class="text-gray-600">{{ __('Draft') }}</span>
                                                — {{ __('no responses yet') }}
                                                <span class="text-indigo-600"> · {{ __('Click row for summary') }}</span>
                                            @endif
                                        </p>
                                    </div>
                                    <div class="flex flex-wrap gap-2 items-center shrink-0" @click.stop>
                                        <a href="{{ route('host.show', $presentation->manage_token) }}"
                                           class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                                            {{ __('Manage') }}
                                        </a>
                                        @if ($presentation->is_live)
                                            <a href="{{ route('host.present', $presentation->manage_token) }}"
                                               class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                                                {{ __('Present (QR)') }}
                                            </a>
                                        @endif
                                        @if ($presentation->responses_count > 0)
                                            <form method="POST" action="{{ route('presentations.reset-results', $presentation) }}"
                                                  onsubmit="return confirm(@js(__('Clear all votes and answers for this presentation? You can run a new session afterwards.')));">
                                                @csrf
                                                <input type="hidden" name="redirect_to" value="dashboard">
                                                <button type="submit" class="text-sm text-red-600 hover:text-red-800 font-medium">
                                                    {{ __('Reset results') }}
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>

        {{-- Results summary modal --}}
        <div
            x-show="open"
            x-transition.opacity
            class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
            role="dialog"
            aria-modal="true"
        >
            <div class="absolute inset-0 bg-gray-900/60" @click="closeModal()"></div>

            <div
                class="relative w-full max-w-3xl max-h-[90vh] overflow-y-auto rounded-lg bg-white shadow-xl"
                @click.stop
            >
                <div class="sticky top-0 z-10 flex items-center justify-between border-b border-gray-200 bg-white px-4 py-3">
                    <h3 class="text-lg font-semibold text-gray-900" x-text="payload?.title ?? '{{ __('Results') }}'"></h3>
                    <button type="button" class="rounded-md p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-800" @click="closeModal()" aria-label="{{ __('Close') }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="p-4 sm:p-6 space-y-6">
                    <template x-if="loading">
                        <p class="text-sm text-gray-600">{{ __('Loading…') }}</p>
                    </template>

                    <template x-if="error && !loading">
                        <p class="text-sm text-red-600" x-text="error"></p>
                    </template>

                    <template x-if="payload && !loading && !error">
                        <div class="space-y-8">
                            <template x-for="q in payload.questions" :key="q.id">
                                <div class="border-b border-gray-100 pb-8 last:border-0 last:pb-0">
                                    <h4 class="text-base font-medium text-gray-900 mb-4" x-text="q.prompt"></h4>

                                    <div class="grid gap-6 sm:grid-cols-2">
                                        <div>
                                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-2">{{ __('Distribution') }}</p>
                                            <div class="flex h-64 max-w-xs items-center justify-center mx-auto">
                                                <canvas class="max-h-full w-full" x-bind:id="'pie-' + q.id"></canvas>
                                            </div>
                                            <p class="text-xs text-gray-500 mt-2 text-center" x-show="q.totals && q.totals.length && q.totals.every(t => t.count === 0)">
                                                {{ __('No votes for this question.') }}
                                            </p>
                                        </div>
                                        <div>
                                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-2">{{ __('Participant breakdown') }}</p>
                                            <div class="overflow-x-auto rounded-md border border-gray-200">
                                                <table class="min-w-full divide-y divide-gray-200 text-sm">
                                                    <thead class="bg-gray-50">
                                                        <tr>
                                                            <th class="px-3 py-2 text-left font-medium text-gray-700">{{ __('Participant') }}</th>
                                                            <th class="px-3 py-2 text-left font-medium text-gray-700">{{ __('Answer') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-gray-100 bg-white">
                                                        <template x-for="(row, idx) in q.participants" :key="idx">
                                                            <tr>
                                                                <td class="px-3 py-2 text-gray-900" x-text="row.participant_name"></td>
                                                                <td class="px-3 py-2 text-gray-700" x-text="row.choice_label"></td>
                                                            </tr>
                                                        </template>
                                                        <tr x-show="!q.participants || q.participants.length === 0">
                                                            <td colspan="2" class="px-3 py-3 text-gray-500">{{ __('No responses yet.') }}</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
