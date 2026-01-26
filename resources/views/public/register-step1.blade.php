@extends('layouts.auth')
@php
    $settings = settingsById(2);
@endphp
@section('tab-title')
    {{ __('Parent Registration - Step 1') }}
@endsection
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-xl-4 col-lg-5 col-md-6 col-sm-12 mx-auto">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-user-circle"></i> {{ __('Step 1: Parent/Guardian Information') }}</h4>
                    <small>{{ __('Please provide your contact information') }}</small>
                </div>
                <div class="card-body">
                    <!-- Progress Indicator -->
                    <div class="progress mb-4" style="height: 8px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: 33%"></div>
                    </div>
                    <div class="text-center mb-3">
                        <small class="text-muted">{{ __('Step 1 of 3') }}</small>
                    </div>

                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <form method="POST" action="{{ route('public.register.step1') }}">
                        @csrf

                        <div class="form-floating mb-3">
                            <input type="text" name="parent_first_name" class="form-control @error('parent_first_name') is-invalid @enderror" value="{{ old('parent_first_name') }}" required autofocus>
                            <label>{{ __('First Name') }} <span class="text-danger">*</span></label>
                            @error('parent_first_name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-floating mb-3">
                            <input type="text" name="parent_last_name" class="form-control @error('parent_last_name') is-invalid @enderror" value="{{ old('parent_last_name') }}" required>
                            <label>{{ __('Last Name') }} <span class="text-danger">*</span></label>
                            @error('parent_last_name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-floating mb-3">
                            <input type="email" name="parent_email" class="form-control @error('parent_email') is-invalid @enderror" value="{{ old('parent_email') }}" required>
                            <label>{{ __('Email Address') }} <span class="text-danger">*</span></label>
                            @error('parent_email')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-floating mb-3">
                            <input type="text" name="parent_phone" class="form-control @error('parent_phone') is-invalid @enderror" value="{{ old('parent_phone') }}" required>
                            <label>{{ __('Phone Number') }} <span class="text-danger">*</span></label>
                            @error('parent_phone')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-floating mb-3">
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                            <label>{{ __('Password') }} <span class="text-danger">*</span></label>
                            @error('password')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-floating mb-3">
                            <input type="password" name="password_confirmation" class="form-control" required>
                            <label>{{ __('Confirm Password') }} <span class="text-danger">*</span></label>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                {{ __('Next: Add Children') }} <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
