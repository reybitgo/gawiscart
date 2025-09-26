@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Verify your email
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                A verification link has been sent to your email address
            </p>
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                A new verification link has been sent to the email address you provided during registration.
            </div>
        @endif

        <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded">
            <p class="text-sm">
                Before proceeding, please check your email for a verification link.
                If you didn't receive the email, we can send you another.
            </p>
        </div>

        <div class="flex items-center justify-center space-x-4">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit"
                        class="inline-flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Resend Verification Email
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="inline-flex justify-center py-2 px-4 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Log Out
                </button>
            </form>
        </div>
    </div>
</div>
@endsection