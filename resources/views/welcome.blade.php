@extends('layouts.app')
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome - OTP Verification System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="text-center">
        <h1 class="text-4xl font-bold mb-4" style="font-weight:bold;text-decoration:underline;color:red;">Welcome to OTP
            Verification System</h1>
        <p class="text-lg mb-6">You are logged in!</p>
        <a href="{{ route('otp.verify', ['type' => 'email']) }}"
            class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Go to OTP Verification</a>
    </div>
</body>

</html>
