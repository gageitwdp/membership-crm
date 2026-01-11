
@extends('layouts.auth')

@php
    $settings = settings();
@endphp

@section('tab-title')
    {{ __('Register') }}
@endsection

@push('script-page')
    @if ($settings['google_recaptcha'] == 'on')
        {!! NoCaptcha::renderJs() !!}
    @endif
@endpush

@section('content')
<div class="card">
    <div class="card-body">

        <div class="row">
            <div class="d-flex justify-content-center">
                <div class="auth-header text-center">
                    <h2 class="text-secondary">
                        <b>{{ __('Sign up') }}</b>
                    </h2>
                    <p class="f-16 mt-2">
                        {{ __('Enter your details and create account') }}
                    </p>
                </div>
            </div>
        </div>

        {{ Form::open(['route' => 'members.register.store', 'method' => 'post', 'id' => 'registerForm']) }}
        @csrf

        {{-- Alerts --}}
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        {{-- Name --}}
        <div class="form-floating mb-3">
            <input type="text"
                   class="form-control"
                   name="name"
                   value="{{ old('name') }}"
                   placeholder="{{ __('Name') }}"
                   required>
            <label>{{ __('Name') }}</label>
            @error('name') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        {{-- Email --}}
        <div class="form-floating mb-3">
            <input type="email"
                   class="form-control"
                   name="email"
                   value="{{ old('email') }}"
                   placeholder="{{ __('Email address') }}"
                   required>
            <label>{{ __('Email address') }}</label>
            @error('email') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        {{-- Password --}}
        <div class="form-floating mb-3">
            <input type="password"
                   class="form-control"
                   name="password"
                   placeholder="{{ __('Password') }}"
                   required minlength="12">
            <label>{{ __('Password') }}</label>
            @error('password') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        {{-- Confirm Password --}}
        <div class="form-floating mb-3">
            <input type="password"
                   class="form-control"
                   name="password_confirmation"
                   placeholder="{{ __('Password Confirmation') }}"
                   required>
            <label>{{ __('Password Confirmation') }}</label>
        </div>

        {{-- Terms --}}
        <div class="form-check mt-3">
            <input class="form-check-input input-primary"
                   type="checkbox"
                   id="agree"
                   name="acceptTerms"
                   {{ old('acceptTerms') ? 'checked' : '' }}
                   required>

            <label class="form-check-label" for="agree">
                {{ __('I agree to the') }}
                <a href="{{ route('page', 'terms-and-conditions') }}">
                    {{ __('Terms and Conditions') }}
                </a>
            </label>

            @error('acceptTerms')
                <span class="text-danger d-block">{{ $message }}</span>
            @enderror
        </div>

        {{-- reCAPTCHA --}}
        @if ($settings['google_recaptcha'] == 'on')
            <div class="form-group mt-3">
                {!! NoCaptcha::display() !!}
                @error('g-recaptcha-response')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
        @endif

        <div class="d-grid mt-4">
            <button type="submit" class="btn btn-secondary p-2">
                {{ __('Sign Up') }}
            </button>
        </div>

        <hr>

        <h5 class="d-flex justify-content-center">
            {{ __('Already have an account?') }}
            <a class="ms-1 text-secondary" href="{{ route('login') }}">
                {{ __('Login here') }}
            </a>
        </h5>

        {{ Form::close() }}

    </div>
</div>
@endsection
