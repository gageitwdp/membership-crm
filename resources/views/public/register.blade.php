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
            const registrationTypeRadios = document.querySelectorAll('input[name="registration_type"]');
            const ageConfirmationDiv = document.getElementById('ageConfirmationDiv');
            const parentInfoDiv = document.getElementById('parentInfoDiv');
            const ageConfirmationCheckbox = document.getElementById('age_confirmation');
            const childrenSection = document.getElementById('childrenSection');
            const selfMemberSection = document.getElementById('selfMemberSection');
            const childrenContainer = document.getElementById('childrenContainer');
            const addChildBtn = document.getElementById('addChildBtn');
            let childCount = 0;
            
            // Handle registration type change
            registrationTypeRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    if (this.value === 'self') {
                        ageConfirmationDiv.style.display = 'block';
                        parentInfoDiv.style.display = 'none';
                        childrenSection.style.display = 'none';
                        selfMemberSection.style.display = 'block';
                        
                        // Show self-registration fields
                        selfMemberSection.style.display = 'block';
                        document.querySelectorAll('.parent-registration-field').forEach(el => el.style.display = 'none');
                        
                        // Clear parent fields
                        document.getElementById('parent_first_name').value = '';
                        document.getElementById('parent_last_name').value = '';
                        document.getElementById('parent_email').value = '';
                        document.getElementById('parent_phone').value = '';
                        // Make parent fields not required
                        document.getElementById('parent_first_name').removeAttribute('required');
                        document.getElementById('parent_last_name').removeAttribute('required');
                        document.getElementById('parent_email').removeAttribute('required');
                        document.getElementById('parent_phone').removeAttribute('required');
                        
                        // Make self-registration fields required
                        const selfSection = document.getElementById('selfMemberSection');
                        if (selfSection) {
                            selfSection.querySelectorAll('input[type="text"], input[type="email"], input[type="date"], input[type="password"], select, textarea').forEach(el => {
                                if (!el.id.includes('emergency_contact') && !el.id.includes('image') && el.id !== 'plan_id') {
                                    el.setAttribute('required', 'required');
                                }
                            });
                        }
                    } else if (this.value === 'parent') {
                        ageConfirmationDiv.style.display = 'none';
                        parentInfoDiv.style.display = 'block';
                        childrenSection.style.display = 'block';
                        selfMemberSection.style.display = 'none';
                        
                        // Hide self-registration fields
                        document.querySelectorAll('.parent-registration-field').forEach(el => el.style.display = 'block');
                        
                        // Make parent fields required
                        document.getElementById('parent_first_name').setAttribute('required', 'required');
                        document.getElementById('parent_last_name').setAttribute('required', 'required');
                        document.getElementById('parent_email').setAttribute('required', 'required');
                        
                        // Remove required from self-registration fields
                        const selfSectionRemove = document.getElementById('selfMemberSection');
                        if (selfSectionRemove) {
                            selfSectionRemove.querySelectorAll('input, select, textarea').forEach(el => {
                                el.removeAttribute('required');
                            });
                        }
                        
                        // Add first child if none exist
                        if (childCount === 0) {
                            addChild();
                        }
                    }
                });
            });
            
            // Function to add child form
            function addChild() {
                childCount++;
                const childIndex = childCount;
                
                const childHtml = `
                    <div class="child-form card mb-3" id="child-${childIndex}">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">{{ __('Child') }} #${childIndex}</h6>
                            <button type="button" class="btn btn-sm btn-danger remove-child-btn" data-child-id="${childIndex}">
                                <i class="fas fa-trash"></i> {{ __('Remove') }}
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-floating mb-3">
                                        <input type="text" name="children[${childIndex}][first_name]" class="form-control" placeholder="{{ __('First Name') }}" required>
                                        <label>{{ __('First Name') }} <span class="text-danger">*</span></label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating mb-3">
                                        <input type="text" name="children[${childIndex}][last_name]" class="form-control" placeholder="{{ __('Last Name') }}" required>
                                        <label>{{ __('Last Name') }} <span class="text-danger">*</span></label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating mb-3">
                                        <input type="email" name="children[${childIndex}][email]" class="form-control" placeholder="{{ __('Email') }}" required>
                                        <label>{{ __('Email') }} <span class="text-danger">*</span></label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating mb-3">
                                        <input type="text" name="children[${childIndex}][phone]" class="form-control child-phone" placeholder="{{ __('Phone') }}" maxlength="12">
                                        <label>{{ __('Phone') }}</label>
                                        <small class="form-text text-muted">{{ __('No need to input the dashes.') }}</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating mb-3">
                                        <input type="date" name="children[${childIndex}][dob]" class="form-control" required>
                                        <label>{{ __('Date of Birth') }} <span class="text-danger">*</span></label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating mb-3">
                                        <select name="children[${childIndex}][gender]" class="form-control" required>
                                            <option value="">{{ __('Select Gender') }}</option>
                                            <option value="Male">{{ __('Male') }}</option>
                                            <option value="Female">{{ __('Female') }}</option>
                                        </select>
                                        <label>{{ __('Gender') }} <span class="text-danger">*</span></label>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-floating mb-3">
                                        <textarea name="children[${childIndex}][address]" class="form-control" placeholder="{{ __('Address') }}" style="height: 80px"></textarea>
                                        <label>{{ __('Address') }}</label>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-floating mb-3">
                                        <textarea name="children[${childIndex}][emergency_contact]" class="form-control" placeholder="{{ __('Emergency Contact') }}" style="height: 80px"></textarea>
                                        <label>{{ __('Emergency Contact Information') }}</label>
                                    </div>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">{{ __('Profile Image') }}</label>
                                    <input type="file" name="children[${childIndex}][image]" class="form-control" accept="image/*">
                                </div>
                                @if($membershipPlans && $membershipPlans->count() > 0)
                                <div class="col-md-12">
                                    <div class="form-floating mb-3">
                                        <select name="children[${childIndex}][plan_id]" class="form-control">
                                            <option value="">{{ __('No membership plan') }}</option>
                                            @foreach($membershipPlans as $plan)
                                                <option value="{{ $plan->id }}">{{ $plan->plan_name }} - {{ $plan->duration }} (${{ $plan->price }})</option>
                                            @endforeach
                                        </select>
                                        <label>{{ __('Membership Plan') }}</label>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                `;
                
                childrenContainer.insertAdjacentHTML('beforeend', childHtml);
                
                // Add phone formatting to new child phone field
                const newPhoneInputs = document.querySelectorAll('.child-phone');
                newPhoneInputs.forEach(input => {
                    if (!input.dataset.formatted) {
                        input.dataset.formatted = 'true';
                        input.addEventListener('input', formatPhoneNumber);
                    }
                });
            }
            
            // Function to format phone numbers
            function formatPhoneNumber(e) {
                let value = e.target.value.replace(/\D/g, '');
                
                if (value.length > 10) {
                    value = value.slice(0, 10);
                }
                
                if (value.length >= 6) {
                    e.target.value = value.slice(0, 3) + '-' + value.slice(3, 6) + '-' + value.slice(6);
                } else if (value.length >= 3) {
                    e.target.value = value.slice(0, 3) + '-' + value.slice(3);
                } else {
                    e.target.value = value;
                }
            }
            
            // Add child button click handler
            if (addChildBtn) {
                addChildBtn.addEventListener('click', addChild);
            }
            
            // Remove child button handler (using event delegation)
            if (childrenContainer) {
                childrenContainer.addEventListener('click', function(e) {
                    if (e.target.classList.contains('remove-child-btn') || e.target.closest('.remove-child-btn')) {
                        const btn = e.target.classList.contains('remove-child-btn') ? e.target : e.target.closest('.remove-child-btn');
                        const childId = btn.dataset.childId;
                        const childElement = document.getElementById(`child-${childId}`);
                        if (childElement && childCount > 1) {
                            childElement.remove();
                        } else if (childCount === 1) {
                            alert('{{ __('You must have at least one child for parent registration.') }}');
                        }
                    }
                });
            }
            
            if (planSelect) {
                planSelect.addEventListener('change', function() {
                    if (this.value) {
                        planSection.style.display = 'block';
                        paymentSection.style.display = 'block';
                        submitText.textContent = '{!! __("Register & Pay") !!}';
                    } else {
                        planSection.style.display = 'none';
                        paymentSection.style.display = 'none';
                        submitText.textContent = '{!! __("Register") !!}';
                    }
                });
            }

            // Global form validation for registration type and age confirmation
            function validateRegistrationType() {
                const selectedType = document.querySelector('input[name="registration_type"]:checked');
                
                if (!selectedType) {
                    alert('{{ __('Please select whether you are registering yourself or a child.') }}');
                    return false;
                }
                
                if (selectedType.value === 'self' && !ageConfirmationCheckbox.checked) {
                    alert('{{ __('You must confirm that you are 18 years or older to register.') }}');
                    return false;
                }
                
                return true;
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

            // Handle form submission with registration and payment
            registerForm.addEventListener('submit', async function(event) {
                // First validate registration type and age confirmation
                if (!validateRegistrationType()) {
                    event.preventDefault();
                    return false;
                }
                
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
                        alert('Card Error: ' + error.message);
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
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
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
                        body: paymentData,
                        headers: {
                            'Accept': 'application/json'
                        }
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
            @else
            // No Stripe - add simple validation for non-payment forms
            registerForm.addEventListener('submit', function(event) {
                if (!validateRegistrationType()) {
                    event.preventDefault();
                    return false;
                }
            });
            @endif

            // Phone number formatting
            const phoneInput = document.getElementById('phone');
            if (phoneInput) {
                phoneInput.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\D/g, '');
                    
                    if (value.length > 10) {
                        value = value.slice(0, 10);
                    }
                    
                    if (value.length >= 6) {
                        e.target.value = value.slice(0, 3) + '-' + value.slice(3, 6) + '-' + value.slice(6);
                    } else if (value.length >= 3) {
                        e.target.value = value.slice(0, 3) + '-' + value.slice(3);
                    } else {
                        e.target.value = value;
                    }
                });
            }
            
            // Parent phone formatting
            const parentPhoneInput = document.getElementById('parent_phone');
            if (parentPhoneInput) {
                parentPhoneInput.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\D/g, '');
                    
                    if (value.length > 10) {
                        value = value.slice(0, 10);
                    }
                    
                    if (value.length >= 6) {
                        e.target.value = value.slice(0, 3) + '-' + value.slice(3, 6) + '-' + value.slice(6);
                    } else if (value.length >= 3) {
                        e.target.value = value.slice(0, 3) + '-' + value.slice(3);
                    } else {
                        e.target.value = value;
                    }
                });
            }
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
                <!-- Registration Type Selection -->
                <div class="col-md-12">
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">{{ __('Who are you registering?') }} <span class="text-danger">*</span></h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="radio" name="registration_type" id="type_self" value="self" {{ old('registration_type') == 'self' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="type_self">
                                            <strong>{{ __('Myself (I am 18 years or older)') }}</strong>
                                            <br>
                                            <small class="text-muted">{{ __('Select this if you are registering for yourself') }}</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="radio" name="registration_type" id="type_parent" value="parent" {{ old('registration_type') == 'parent' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="type_parent">
                                            <strong>{{ __('My Child (I am a parent/guardian)') }}</strong>
                                            <br>
                                            <small class="text-muted">{{ __('Select this if you are registering a child under 18') }}</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            @error('registration_type')
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            
                            <!-- Age Confirmation Checkbox (shown only for self-registration) -->
                            <div id="ageConfirmationDiv" style="display: none;">
                                <div class="alert alert-info">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="age_confirmation" name="age_confirmation" value="1" {{ old('age_confirmation') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="age_confirmation">
                                            <strong>{{ __('I confirm that I am 18 years of age or older') }}</strong> <span class="text-danger">*</span>
                                        </label>
                                    </div>
                                    @error('age_confirmation')
                                        <span class="invalid-feedback d-block" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            
                            <!-- Parent Information (shown only for parent registration) -->
                            <div id="parentInfoDiv" style="display: none;">
                                <div class="alert alert-warning">
                                    <i class="fas fa-info-circle"></i> {{ __('Please provide parent/guardian information below') }}
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            {{ Form::text('parent_first_name', old('parent_first_name'), ['class' => 'form-control', 'id' => 'parent_first_name', 'placeholder' => __('Parent First Name')]) }}
                                            <label for="parent_first_name">{{ __('Parent/Guardian First Name') }} <span class="text-danger">*</span></label>
                                            @error('parent_first_name')
                                                <span class="invalid-feedback d-block" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            {{ Form::text('parent_last_name', old('parent_last_name'), ['class' => 'form-control', 'id' => 'parent_last_name', 'placeholder' => __('Parent Last Name')]) }}
                                            <label for="parent_last_name">{{ __('Parent/Guardian Last Name') }} <span class="text-danger">*</span></label>
                                            @error('parent_last_name')
                                                <span class="invalid-feedback d-block" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            {{ Form::email('parent_email', old('parent_email'), ['class' => 'form-control', 'id' => 'parent_email', 'placeholder' => __('Parent Email')]) }}
                                            <label for="parent_email">{{ __('Parent/Guardian Email') }} <span class="text-danger">*</span></label>
                                            @error('parent_email')
                                                <span class="invalid-feedback d-block" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            {{ Form::text('parent_phone', old('parent_phone'), ['class' => 'form-control', 'id' => 'parent_phone', 'placeholder' => __('Parent Phone'), 'maxlength' => '12']) }}
                                            <label for="parent_phone">{{ __('Parent/Guardian Phone') }} <span class="text-danger">*</span></label>
                                            <small class="form-text text-muted">{{ __('No need to input the dashes.') }}</small>
                                            @error('parent_phone')
                                                <span class="invalid-feedback d-block" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Children Information (for parent registration) -->
                <div id="childrenSection" class="col-md-12" style="display: none;">
                    <div class="card mb-3">
                        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">{{ __('Children Information') }}</h5>
                            <button type="button" class="btn btn-sm btn-light" id="addChildBtn">
                                <i class="fas fa-plus"></i> {{ __('Add Another Child') }}
                            </button>
                        </div>
                        <div class="card-body">
                            <div id="childrenContainer">
                                <!-- Children will be added here dynamically -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Member Information (for self-registration only) -->
                <div id="selfMemberSection" class="col-md-12" style="display: none;">
                    <h5 class="mb-3">{{ __('Member Information') }}</h5>
                
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                {{ Form::text('first_name', old('first_name'), ['class' => 'form-control', 'id' => 'first_name', 'placeholder' => __('First Name')]) }}
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
                                {{ Form::text('last_name', old('last_name'), ['class' => 'form-control', 'id' => 'last_name', 'placeholder' => __('Last Name')]) }}
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
                                {{ Form::email('email', old('email'), ['class' => 'form-control', 'id' => 'email', 'placeholder' => __('Email')]) }}
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
                                {{ Form::text('phone', old('phone'), ['class' => 'form-control', 'id' => 'phone', 'placeholder' => __('Phone Number'), 'maxlength' => '12']) }}
                                <label for="phone">{{ __('Phone Number') }} <span class="text-danger">*</span></label>
                                <small class="form-text text-muted">{{ __('No need to input the dashes.') }}</small>
                                @error('phone')
                                    <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                {{ Form::date('dob', old('dob'), ['class' => 'form-control', 'id' => 'dob']) }}
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
                                {{ Form::select('gender', ['Male' => 'Male', 'Female' => 'Female'], old('gender'), ['class' => 'form-control', 'id' => 'gender']) }}
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
                                {{ Form::textarea('address', old('address'), ['class' => 'form-control', 'id' => 'address', 'placeholder' => __('Address'), 'rows' => '2', 'style' => 'height: 80px']) }}
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

                        <!-- Membership Plan Selection (Optional) for self-registration -->
                        @if($membershipPlans && $membershipPlans->count() > 0)
                            <div class="col-md-12">
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h5>{{ __('Select Membership Plan') }}</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-floating mb-3">
                                            <select name="plan_id" id="plan_id" class="form-control">
                                                <option value="">{{ __('No membership plan') }}</option>
                                                @foreach($membershipPlans as $plan)
                                                    <option value="{{ $plan->id }}" {{ old('plan_id') == $plan->id ? 'selected' : '' }}>
                                                        {{ $plan->plan_name }} - {{ $plan->duration }} (${{ $plan->price }})
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

                        <!-- Password fields for self-registration -->
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                {{ Form::password('password', ['class' => 'form-control', 'id' => 'password_self', 'placeholder' => __('Password'), 'minlength' => '6']) }}
                                <label for="password_self">{{ __('Password') }} <span class="text-danger">*</span></label>
                                <small class="form-text text-muted">{{ __('Minimum 6 characters') }}</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                {{ Form::password('password_confirmation', ['class' => 'form-control', 'id' => 'password_confirmation_self', 'placeholder' => __('Confirm Password')]) }}
                                <label for="password_confirmation_self">{{ __('Confirm Password') }} <span class="text-danger">*</span></label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Password fields for parent registration -->
                <div class="col-md-6 parent-registration-field" style="display: none;">
                    <div class="form-floating mb-3">
                        {{ Form::password('password', ['class' => 'form-control', 'id' => 'password_parent', 'placeholder' => __('Password'), 'minlength' => '6']) }}
                        <label for="password_parent">{{ __('Account Password') }} <span class="text-danger">*</span></label>
                        <small class="form-text text-muted">{{ __('Minimum 6 characters - for parent login') }}</small>
                    </div>
                </div>

                <div class="col-md-6 parent-registration-field" style="display: none;">
                    <div class="form-floating mb-3">
                        {{ Form::password('password_confirmation', ['class' => 'form-control', 'id' => 'password_confirmation_parent', 'placeholder' => __('Confirm Password')]) }}
                        <label for="password_confirmation_parent">{{ __('Confirm Password') }} <span class="text-danger">*</span></label>
                    </div>
                </div>

                <!-- Payment Section -->
                <div id="paymentSection" class="col-md-12" style="display: none;">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5>{{ __('Payment Information') }}</h5>
                        </div>
                        <div class="card-body">
                            @if($stripeEnabled)
                                <div class="alert alert-warning mb-3">
                                    <i class="fas fa-lock"></i> {{ __('Your payment information is secured with online payment security') }}
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="card-element" class="form-label">
                                        {{ __('Credit or Debit Card') }} <span class="text-danger">*</span>
                                    </label>
                                    <div id="card-element" class="form-control" style="height: 40px; padding-top: 10px;"></div>
                                </div>

                                <small class="text-muted">
                                    <i class="fas fa-info-circle"></i> 
                                    {{ __('Payment will be processed after successful registration') }}
                                </small>
                            @else
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-triangle"></i> 
                                    {{ __('Online payment is not currently configured. Please contact the administrator or select no plan to register without payment.') }}
                                </div>
                            @endif
                        </div>
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
