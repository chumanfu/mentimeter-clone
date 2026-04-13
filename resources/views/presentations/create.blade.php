<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('New presentation') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('presentations.store') }}" class="space-y-4">
                        @csrf
                        <div>
                            <x-input-label for="title" :value="__('Presentation title')" />
                            <x-text-input id="title" class="block mt-1 w-full" type="text" name="title" :value="old('title')" required autofocus />
                            <x-input-error :messages="$errors->get('title')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="prompt" :value="__('First question')" />
                            <x-text-input id="prompt" class="block mt-1 w-full" type="text" name="prompt" :value="old('prompt')" required />
                            <x-input-error :messages="$errors->get('prompt')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label :value="__('Answer choices')" />
                            <p class="text-xs text-gray-500 mt-1">At least two options. Use the plus button to add more (up to 12).</p>
                            <div id="choices-container" class="mt-2 space-y-2">
                                @foreach (old('choices', ['', '']) as $i => $choice)
                                    <div class="flex gap-2 items-center" data-choice-row>
                                        <x-text-input class="block w-full" type="text" name="choices[]" :value="$choice" placeholder="{{ __('Option') }}" required />
                                        <button type="button" data-remove-choice class="shrink-0 text-sm text-red-600 hover:text-red-800 disabled:opacity-40 px-1" title="{{ __('Remove') }}">&times;</button>
                                    </div>
                                @endforeach
                            </div>
                            <button type="button" id="add-choice-btn" class="mt-3 text-sm font-medium text-indigo-600 hover:text-indigo-800">
                                + {{ __('Add option') }}
                            </button>
                            <x-input-error :messages="$errors->get('choices')" class="mt-2" />
                            @foreach ($errors->get('choices.*') as $messages)
                                <x-input-error :messages="$messages" class="mt-2" />
                            @endforeach
                        </div>

                        <div class="flex items-center gap-4 pt-2">
                            <x-primary-button>{{ __('Save and manage') }}</x-primary-button>
                            <a href="{{ route('dashboard') }}" class="text-sm text-gray-600 hover:text-gray-900">{{ __('Cancel') }}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <template id="choice-row-template">
        <div class="flex gap-2 items-center" data-choice-row>
            <input class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full" type="text" name="choices[]" placeholder="{{ __('Option') }}" required />
            <button type="button" data-remove-choice class="shrink-0 text-sm text-red-600 hover:text-red-800 px-1" title="{{ __('Remove') }}">&times;</button>
        </div>
    </template>
</x-app-layout>
