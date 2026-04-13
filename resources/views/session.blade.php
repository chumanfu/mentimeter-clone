<x-guest-layout>
    <p class="text-sm text-gray-500 mb-1">{{ __('You are in as') }} <strong>{{ $participantName }}</strong></p>
    <h1 class="text-xl font-bold text-gray-900 mb-6">{{ $presentation->title }}</h1>

    @if ($activeQuestion)
        <div class="mb-6">
            <h2 class="text-lg font-medium text-gray-800 mb-4">{{ $activeQuestion->prompt }}</h2>

            @if ($existingResponse)
                <div class="rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-900">
                    <p class="font-medium">{{ __('Your answer') }}</p>
                    <p class="mt-1 text-base text-gray-900">{{ $existingResponse->choice->label }}</p>
                    <p class="mt-3 text-xs text-green-800">{{ __('You cannot submit another answer for this question.') }}</p>
                </div>
            @else
                <form method="POST" action="{{ route('session.vote', $presentation->join_code) }}" class="space-y-3">
                    @csrf
                    @foreach ($activeQuestion->choices as $choice)
                        <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50/50">
                            <input type="radio" name="choice_id" value="{{ $choice->id }}" class="rounded-full border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" required>
                            <span class="text-gray-800">{{ $choice->label }}</span>
                        </label>
                    @endforeach
                    <x-primary-button class="mt-4">{{ __('Submit vote') }}</x-primary-button>
                </form>
            @endif
        </div>
    @else
        <p class="text-gray-600 text-sm">{{ __('No active question yet. This page refreshes every few seconds.') }}</p>
    @endif

    @if (session('status'))
        <p class="mt-4 text-sm text-green-700">{{ session('status') }}</p>
    @endif

    <x-input-error :messages="$errors->get('choice_id')" class="mt-2" />

    <script>
        setTimeout(function () { window.location.reload(); }, 8000);
    </script>
</x-guest-layout>
