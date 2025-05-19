@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <h1 class="text-3xl font-bold mb-4">Welcome to Hotel Management System</h1>
                <p class="mb-4">Your comprehensive solution for hotel management.</p>
                
                @auth
                    <div class="mt-4">
                        <a href="{{ route('dashboard') }}" class="text-blue-600 hover:text-blue-800">Go to Dashboard →</a>
                    </div>
                @else
                    <div class="mt-4 space-x-4">
                        <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-800">Log in</a>
                        <a href="{{ route('register') }}" class="text-blue-600 hover:text-blue-800">Register</a>
                    </div>
                @endauth
            </div>
        </div>
    </div>
</div>
@endsection 