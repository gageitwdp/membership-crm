@extends('layouts.auth')
@php
    $settings = settingsById(2);
    $stripeEnabled = $settings['STRIPE_PAYMENT'] == 'on' && !empty($settings['STRIPE_KEY']) && !empty($settings['STRIPE_SECRET']);
@endphp
@section('tab-title')
    {{ __('Member Registration') }}
@endsection
@push('script-page')
    @if ($stripeEnabled)
        <script src="https://js.stripe.com/v3/"></script>
    @endif
    @if (isset($settings['google_recaptcha']) && $settings['google_recaptcha'] == 'on')
        {!! NoCaptcha::renderJs() !!}
    @endif
    <script>
        // Toggle membership plan section and payment
        document.addEventListener('DOMContentLoaded', function() {
            const planSection = document.getElementById('planSection');
            const paymentSection = document.getElementById('paymentSection');
            const planSelect = document.getElementById('plan_id');
            const registerForm = document.getElementById('register-form');
            const submitBtn = document.getElementById('submit-btn');
            const submitText = document.getElementById('submit-text');
            
            if (planSelect) {
                planSelect.addEventListener('change', function() {
                    if (this.value) {
                        planSection.style.display = 'block';
                        paymentSection.style.display = 'block';
                        submitText.textContent = '{{ __("Register & Pay") }}';
                    } else {
                        planSection.style.display = 'none';
                        paymentSection.style.display = 'none';
                        submitText.textContent = '{{ __("Register") }}';
                    }
                });
            }

            @if ($stripeEnabled)
            // Stripe Elements setup
            var stripe = Stripe('{{ $settings['STRIPE_KEY'] }}');
            var elements = stripe.elements();
            var cardElement = elements.create('card', {
                style: {
                    base: {
                        fontSize: '16px',
                        color: '#32325d',
                        fontFamily: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
                        '::placeholder': {
                            color: '#aab7c4'
                        }
                    },
                    invalid: {
                        color: '#fa755a',
                        iconColor: '#fa755a'
                    }
                }
            });
            cardElement.mount('#card-element');

            // Handle real-time validation errors
            cardElement.on('change', function(event) {
                var displayError = document.getElementById('card-errors');
                if (event.error) {
                    displayError.textContent = event.error.message;
                } else {
                    displayError.textContent = '';
                }
            });

            // Handle form submission with registration and payment
            registerForm.addEventListener('submit', async function(event) {
                event.preventDefault();
                
                const planId = planSelect ? planSelect.value : null;
                
                if (!planId) {
                    // No plan selected, submit normally (without payment)
                    registerForm.submit();
                    return;
                }

                // Plan selected, process with payment
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> {{ __("Processing...") }}';

                try {
                    // Create Stripe token
                    const {token, error} = await stripe.createToken(cardElement);
                    
                    if (error) {
                        document.getElementById('card-errors').textContent = error.message;
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '{{ __("Register & Pay") }}';
                        return;
                    }

                    // First, submit registration form via AJAX
                    const formData = new FormData(registerForm);
                    
                    const registrationResponse = await fetch('{{ route("public.register.store") }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });

                    const registrationResult = await registrationResponse.json();

                    if (!registrationResult.success) {
                        throw new Error(registrationResult.message || 'Registration failed');
                    }

                    // If registration successful, process payment
                    const paymentData = new FormData();
                    paymentData.append('member_id', registrationResult.member_id);
                    paymentData.append('plan_id', planId);
                    paymentData.append('stripeToken', token.id);
                    paymentData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

                    const paymentResponse = await fetch('{{ route("public.register.payment") }}', {
                        method: 'POST',
                        body: paymentData
                    });

                    const paymentResult = await paymentResponse.json();

                    if (paymentResult.success) {
                        window.location.href = paymentResult.redirect;
                    } else {
                        throw new Error(paymentResult.error || 'Payment failed');
                    }

                } catch (error) {
                    alert('Error: ' + error.message);
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '{{ __("Register & Pay") }}';
                }
            });
            @endif
        });
    </script>
@endpush
@section('content')
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="d-flex justify-content-center">
                    <div class="auth-header">
                        <h2 class="text-secondary"><b>{{ __('Member Registration') }}</b></h2>
                        <p class="f-16 mt-2">{{ __('Create your member account') }}</p>
                    </div>
                </div>
            </div>

            {{ Form::open(['route' => 'public.register.store', 'method' => 'post', 'enctype' => 'multipart/form-data', 'id' => 'register-form']) }}
            
            @if (session('error'))
                <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
            @endif
            @if (session('success'))
                <div class="alert alert-success" role="alert">{{ session('success') }}</div>
            @endif

            <div class="row">
                <!-- Personal Information -->
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        {{ Form::text('first_name', old('first_name'), ['class' => 'form-control', 'id' => 'first_name', 'placeholder' => __('First Name'), 'required' => 'required']) }}
                        <label for="first_name">{{ __('First Name') }} <span class="text-danger">*</span></label>
                        @error('first_name')
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        {{ Form::text('last_name', old('last_name'), ['class' => 'form-control', 'id' => 'last_name', 'placeholder' => __('Last Name'), 'required' => 'required']) }}
                        <label for="last_name">{{ __('Last Name') }} <span class="text-danger">*</span></label>
                        @error('last_name')
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        {{ Form::email('email', old('email'), ['class' => 'form-control', 'id' => 'email', 'placeholder' => __('Email'), 'required' => 'required']) }}
                        <label for="email">{{ __('Email') }} <span class="text-danger">*</span></label>
                        @error('email')
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        {{ Form::text('phone', old('phone'), ['class' => 'form-control', 'id' => 'phone', 'placeholder' => __('Phone Number'), 'required' => 'required']) }}
                        <label for="phone">{{ __('Phone Number') }} <span class="text-danger">*</span></label>
                        <small class="form-text text-muted">{{ __('Include country code, e.g., +91XXXXXXXXXX') }}</small>
                        @error('phone')
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        {{ Form::password('password', ['class' => 'form-control', 'id' => 'password', 'placeholder' => __('Password'), 'required' => 'required', 'minlength' => '6']) }}
                        <label for="password">{{ __('Password') }} <span class="text-danger">*</span></label>
                        <small class="form-text text-muted">{{ __('Minimum 6 characters') }}</small>
                        @error('password')
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        {{ Form::password('password_confirmation', ['class' => 'form-control', 'id' => 'password_confirmation', 'placeholder' => __('Confirm Password'), 'required' => 'required']) }}
                        <label for="password_confirmation">{{ __('Confirm Password') }} <span class="text-danger">*</span></label>
                        @error('password_confirmation')
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        {{ Form::date('dob', old('dob'), ['class' => 'form-control', 'id' => 'dob', 'required' => 'required']) }}
                        <label for="dob">{{ __('Date of Birth') }} <span class="text-danger">*</span></label>
                        @error('dob')
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        {{ Form::select('gender', ['Male' => 'Male', 'Female' => 'Female'], old('gender'), ['class' => 'form-control', 'id' => 'gender', 'required' => 'required']) }}
                        <label for="gender">{{ __('Gender') }} <span class="text-danger">*</span></label>
                        @error('gender')
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="form-floating mb-3">
                        {{ Form::textarea('address', old('address'), ['class' => 'form-control', 'id' => 'address', 'placeholder' => __('Address'), 'required' => 'required', 'rows' => '2', 'style' => 'height: 80px']) }}
                        <label for="address">{{ __('Address') }} <span class="text-danger">*</span></label>
                        @error('address')
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="form-floating mb-3">
                        {{ Form::textarea('emergency_contact_information', old('emergency_contact_information'), ['class' => 'form-control', 'id' => 'emergency_contact_information', 'placeholder' => __('Emergency Contact Information'), 'rows' => '2', 'style' => 'height: 80px']) }}
                        <label for="emergency_contact_information">{{ __('Emergency Contact Information') }}</label>
                        @error('emergency_contact_information')
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-12 mb-3">
                    <label for="image" class="form-label">{{ __('Profile Image') }}</label>
                    {{ Form::file('image', ['class' => 'form-control', 'id' => 'image', 'accept' => 'image/*']) }}
                    @error('image')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <!-- Membership Plan Selection (Optional) -->
                @if($membershipPlans && $membershipPlans->count() > 0)
                    <div class="col-md-12">
                        <div class="card mb-3">
                            <div class="card-header">
                                <h5>{{ __('Select Membership Plan (Optional)') }}</h5>
                            </div>
                            <div class="card-body">
                                <div class="form-floating mb-3">
                                    <select name="plan_id" id="plan_id" class="form-control">
                                        <option value="">{{ __('No membership plan') }}</option>
                                        @foreach($membershipPlans as $plan)
                                            <option value="{{ $plan->id }}" {{ old('plan_id') == $plan->id ? 'selected' : '' }}>
                                                {{ $plan->plan_name }} - {{ $plan->duration }} ({{ $plan->price }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <label for="plan_id">{{ __('Membership Plan') }}</label>
                                    @error('plan_id')
                                        <span class="invalid-feedback d-block" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div id="planSection" style="display: none;">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i> 
                                        {{ __('Payment is required to activate your membership.') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Payment Section (Stripe) -->
                @if($stripeEnabled)
                <div id="paymentSection" class="col-md-12" style="display: none;">
                    <div class="card mb-3 border-primary">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-credit-card"></i> {{ __('Payment Information') }}
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-warning mb-3">
                                <i class="fas fa-lock"></i> {{ __('Your payment information is secured by Stripe') }}
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="card-element" class="form-label">
                                    {{ __('Credit or Debit Card') }} <span class="text-danger">*</span>
                                </label>
                                <div id="card-element" class="form-control" style="height: 40px; padding-top: 10px;"></div>
                                <div id="card-errors" class="text-danger mt-2" role="alert"></div>
                            </div>

                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i> 
                                {{ __('Payment will be processed after successful registration') }}
                            </small>
                        </div>
                    </div>
                </div>
                @endif

                <div class="col-md-12">
                    <div class="form-floating mb-3">
                        {{ Form::textarea('notes', old('notes'), ['class' => 'form-control', 'id' => 'notes', 'placeholder' => __('Additional Notes'), 'rows' => '2', 'style' => 'height: 80px']) }}
                        <label for="notes">{{ __('Additional Notes') }}</label>
                        @error('notes')
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                @if (isset($settings['google_recaptcha']) && $settings['google_recaptcha'] == 'on')
                    <div class="col-md-12 mb-3">
                        {!! NoCaptcha::display() !!}
                        @error('g-recaptcha-response')
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                @endif
            </div>

            <div class="d-grid">
                <button type="submit" id="submit-btn" class="btn btn-primary btn-block">
                    <span id="submit-text">{{ __('Register') }}</span>
                </button>
            </div>
            {{ Form::close() }}

            <p class="mt-3 mb-0 text-center">
                {{ __('Already have an account?') }}
                <a href="{{ route('login') }}" class="text-primary">{{ __('Sign in') }}</a>
            </p>
        </div>
    </div>
@endsection
