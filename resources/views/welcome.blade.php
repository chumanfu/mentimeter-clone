<x-guest-layout>
    <div class="space-y-6 text-center sm:text-left">
        <h1 class="text-2xl font-bold text-gray-900">
            MentiClone
        </h1>
        <p class="text-gray-600 text-sm leading-relaxed">
            Create live polls, share a join code or QR with your audience, and see results update as people vote.
            Hosts need an account; participants join with a code—no login required.
        </p>
        <div class="flex flex-col sm:flex-row gap-3 justify-center sm:justify-start">
            @auth
                <a href="{{ route('dashboard') }}" class="inline-flex justify-center items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    Dashboard
                </a>
            @else
                <a href="{{ route('login') }}" class="inline-flex justify-center items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    Log in
                </a>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="inline-flex justify-center items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                        Register
                    </a>
                @endif
            @endauth
            <a href="{{ route('join.form') }}" class="inline-flex justify-center items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500">
                Join a session
            </a>
        </div>
    </div>
</x-guest-layout>
