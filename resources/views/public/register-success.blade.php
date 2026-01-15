@extends('layouts.auth')
@php
    $settings = settingsById(2);
@endphp
@section('tab-title')
    {{ __('Registration Successful') }}
@endsection
@section('content')
    <div class="card">
        <div class="card-body text-center">
            <div class="py-5">
                <div class="mb-4">
                    <i class="fas fa-check-circle text-success" style="font-size: 80px;"></i>
                </div>
                <h2 class="text-success mb-3">{{ __('Registration Successful!') }}</h2>
                <p class="text-muted f-16 mb-4">
                    {{ __('Your member account has been created successfully.') }}<br>
                    {{ __('You can now log in using your email and password.') }}
                </p>
                
                @if (session('success'))
                    <div class="alert alert-success" role="alert">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="d-grid gap-2 d-md-flex justify-content-md-center mt-4">
                    <a href="{{ route('login') }}" class="btn btn-primary btn-lg">
                        <i class="fas fa-sign-in-alt"></i> {{ __('Go to Login') }}
                    </a>
                    <a href="{{ route('home') }}" class="btn btn-outline-secondary btn-lg">
                        <i class="fas fa-home"></i> {{ __('Back to Home') }}
                    </a>
                </div>

                <div class="mt-5 pt-3 border-top">
                    <h5 class="mb-3">{{ __('What\'s Next?') }}</h5>
                    <div class="row text-start">
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-envelope text-primary me-3 mt-1"></i>
                                <div>
                                    <h6>{{ __('Check Your Email') }}</h6>
                                    <p class="text-muted small">{{ __('You may receive a confirmation email with additional information.') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-user-circle text-primary me-3 mt-1"></i>
                                <div>
                                    <h6>{{ __('Complete Your Profile') }}</h6>
                                    <p class="text-muted small">{{ __('After logging in, you can update your profile information and preferences.') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-credit-card text-primary me-3 mt-1"></i>
                                <div>
                                    <h6>{{ __('Membership Status') }}</h6>
                                    <p class="text-muted small">{{ __('If you selected a membership plan, it will be activated after payment confirmation.') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-question-circle text-primary me-3 mt-1"></i>
                                <div>
                                    <h6>{{ __('Need Help?') }}</h6>
                                    <p class="text-muted small">{{ __('Contact our support team if you have any questions or concerns.') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
