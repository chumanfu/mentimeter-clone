<x-guest-layout>
    <h1 class="text-xl font-bold text-gray-900 mb-1">{{ __('Join a session') }}</h1>
    <p class="text-sm text-gray-600 mb-6">{{ __('Enter your name and the code from the presenter screen.') }}</p>

    <form method="POST" action="{{ route('join.submit') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="name" :value="__('Your name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="join_code" :value="__('Join code')" />
            <x-text-input id="join_code" class="block mt-1 w-full font-mono tracking-widest" type="text" name="join_code" maxlength="6" :value="old('join_code', $prefillCode)" required />
            <x-input-error :messages="$errors->get('join_code')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between gap-4">
            <x-primary-button>{{ __('Join') }}</x-primary-button>
            <a href="{{ route('landing') }}" class="text-sm text-gray-600 hover:text-gray-900">{{ __('Home') }}</a>
        </div>
    </form>
</x-guest-layout>
