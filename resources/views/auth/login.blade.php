<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="mb-6">
        <a href="{{ route('google.redirect') }}" class="w-full inline-flex items-center justify-center gap-2 border border-slate-300 text-slate-700 font-semibold rounded-md px-4 py-2 hover:bg-slate-50 transition">
            <svg class="w-5 h-5" viewBox="0 0 533.5 544.3" aria-hidden="true">
                <path fill="#4285F4" d="M533.5 278.4c0-17.4-1.5-34.1-4.3-50.3H272v95.2h146.9c-6.3 34-25 62.9-53.5 82.1v68.2h86.4c50.7-46.7 80.7-115.5 80.7-195.2z"/>
                <path fill="#34A853" d="M272 544.3c72.6 0 133.5-23.9 178-64.8l-86.4-68.2c-23.9 16-54.4 25.5-91.6 25.5-70.5 0-130.2-47.6-151.6-111.6H32.7v70.2c44.4 88 135.6 148.9 239.3 148.9z"/>
                <path fill="#FBBC05" d="M120.4 325.2c-10.8-32-10.8-66.5 0-98.5V156.5H32.7c-45.8 91.6-45.8 199.6 0 291.2l87.7-69.8z"/>
                <path fill="#EA4335" d="M272 107.7c39.5-.6 77.4 14 106.4 40.8l79.3-79.3C408.3 24.1 345.1-.7 272 0 168.3 0 77.1 60.9 32.7 148.9l87.7 70.2C141.8 155.3 201.5 107.7 272 107.7z"/>
            </svg>
            <span>Lanjutkan dengan Google</span>
        </a>
    </div>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
