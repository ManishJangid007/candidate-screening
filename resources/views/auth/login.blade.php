@extends('layouts.app')

@section('content')
<div class="flex min-h-[80vh] items-center justify-center">
    <div class="w-full max-w-sm">
        <div class="mb-8 text-center">
            <h1 class="text-2xl font-bold tracking-tight">Candidate Screening Tool</h1>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Sign In</h2>
            </div>
            <div class="card-content">
                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="space-y-4">
                        <div class="space-y-2">
                            <label for="email" class="form-label">Email</label>
                            <input
                                id="email"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                class="form-input @error('email') border-destructive ring-destructive @enderror"
                                placeholder="name@example.com"
                                required
                                autofocus
                            />
                            @error('email')
                                <p class="text-sm text-destructive">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <label for="password" class="form-label">Password</label>
                            <input
                                id="password"
                                type="password"
                                name="password"
                                class="form-input @error('password') border-destructive ring-destructive @enderror"
                                placeholder="Enter your password"
                                required
                            />
                            @error('password')
                                <p class="text-sm text-destructive">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit" class="btn-primary w-full">
                            Sign In
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
