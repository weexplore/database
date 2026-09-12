<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Change Password
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Update the password for {{ auth()->user()->name }}.
                </p>
            </div>

            <a
                href="{{ route('home') }}"
                class="inline-flex items-center rounded bg-gray-200 px-4 py-2 text-sm text-gray-800 hover:bg-gray-300"
            >
                Back
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto w-full max-w-xl px-4 sm:px-6 lg:px-8">
            @include('partials.admin.flash-messages')
            @include('partials.admin.validation-summary')

            <div class="overflow-hidden rounded-lg bg-white shadow-sm sm:rounded-lg">
                <form
                    method="POST"
                    action="{{ route('password.update') }}"
                    class="space-y-5 p-6"
                >
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="current_password" class="block text-sm font-medium text-gray-700">
                            Current password
                        </label>

                        <input
                            id="current_password"
                            name="current_password"
                            type="password"
                            autocomplete="current-password"
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">
                            New password
                        </label>

                        <input
                            id="password"
                            name="password"
                            type="password"
                            autocomplete="new-password"
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >

                        <p class="mt-1 text-xs text-gray-500">
                            Use at least 12 characters, including uppercase and lowercase letters,
                            a number, and a symbol.
                        </p>
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                            Confirm new password
                        </label>

                        <input
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            autocomplete="new-password"
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                    </div>

                    <div class="flex justify-end border-t border-gray-200 pt-4">
                        <button
                            type="submit"
                            class="inline-flex items-center rounded bg-green-600 px-5 py-2 text-sm font-medium text-white hover:bg-green-700"
                        >
                            Change Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>