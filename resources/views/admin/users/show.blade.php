<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Admin: User Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="p-4 bg-green-50 text-green-800 rounded-lg text-sm">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 space-y-4">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ $user->name }}</h3>
                            <p class="text-sm text-gray-600">{{ $user->email }}</p>
                        </div>
                        <a href="{{ route('admin.users.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">← Back to users</a>
                    </div>

                    <div class="border-t pt-4 flex flex-wrap items-center gap-3">
                        <form method="POST" action="{{ route('admin.users.role', $user) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="is_superuser" value="{{ $user->is_superuser ? '0' : '1' }}">
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-xs font-semibold uppercase tracking-widest text-gray-700 hover:bg-gray-50">
                                {{ $user->is_superuser ? 'Remove superuser' : 'Make superuser' }}
                            </button>
                        </form>

                        @if (auth()->id() !== $user->id)
                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Delete this user and all their presentations?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-red-300 rounded-md text-xs font-semibold uppercase tracking-widest text-red-700 hover:bg-red-50">
                                    Delete user
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Presentations</h3>
                    @if ($user->presentations->isEmpty())
                        <p class="text-sm text-gray-600">This user has no presentations.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left font-semibold text-gray-600">Title</th>
                                        <th class="px-4 py-2 text-left font-semibold text-gray-600">Status</th>
                                        <th class="px-4 py-2 text-left font-semibold text-gray-600">Responses</th>
                                        <th class="px-4 py-2 text-left font-semibold text-gray-600">Created</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach ($user->presentations as $presentation)
                                        <tr>
                                            <td class="px-4 py-3 text-gray-900">{{ $presentation->title }}</td>
                                            <td class="px-4 py-3 text-gray-700">
                                                @if ($presentation->is_live)
                                                    Live ({{ $presentation->join_code }})
                                                @else
                                                    Ended / Draft
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-gray-700">{{ $presentation->responses_count }}</td>
                                            <td class="px-4 py-3 text-gray-700">{{ $presentation->created_at->toDayDateTimeString() }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
