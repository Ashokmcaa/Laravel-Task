<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>OTP Verification - OTP Assessment</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous"> --}}

    @livewireStyles
</head>

<body class="bg-gradient-to-br from-gray-100 to-gray-300 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white rounded-lg shadow-xl p-8">
        <!-- Header with Branding -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray" style="color:red;">OTP Verification</h1>
            <p class="text-gray-600 mt-2" style="font-size:18px;">Enter the 6-digit code sent to your
                {{ $type === 'email' ? 'email' : 'phone' }}
            </p>
        </div>

        <!-- OTP Input Component -->
        @livewire('otp-input', ['type' => $type])

        <!-- Footer Links -->
        <div class="text-center mt-6">
            <a href="{{ route('otp.verify', ['type' => $type === 'email' ? 'sms' : 'email']) }}"
                class="text-blue-600 hover:underline text-sm">
                Switch to {{ $type === 'email' ? 'SMS' : 'Email' }} Verification
            </a>
            <br>
            <a href="{{ route('logout') }}" class="text-gray-600 hover:underline text-sm mt-2 inline-block"
                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                Logout
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                @csrf
            </form>
        </div>
    </div>
    @livewireScripts
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    {{-- @extends('layouts.app') --}}
</body>

</html>
