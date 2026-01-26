@extends('layouts.auth')
@php
    $settings = settingsById(2);
@endphp
@section('tab-title')
    {{ __('Parent Registration - Step 2 - Children Info') }}
@endsection
@push('script-page')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let childCount = 0;
    const childrenContainer = document.getElementById('childrenContainer');
    const addChildBtn = document.getElementById('addChildBtn');

    // Email validation function
    async function validateEmail(email) {
        const formData = new FormData();
        formData.append('email', email);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        
        try {
            const response = await fetch('{{ route("public.register.check-email") }}', {
                method: 'POST',
                body: formData,
                headers: { 'Accept': 'application/json' }
            });
            
            const result = await response.json();
            return result;
        } catch (error) {
            console.error('Email validation error:', error);
            return { exists: false };
        }
    }

    // Add child form
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
                                <input type="text" name="children[${childIndex}][first_name]" class="form-control" required>
                                <label>{{ __('First Name') }} <span class="text-danger">*</span></label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="text" name="children[${childIndex}][last_name]" class="form-control" required>
                                <label>{{ __('Last Name') }} <span class="text-danger">*</span></label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="email" name="children[${childIndex}][email]" class="form-control child-email" required data-child-index="${childIndex}">
                                <label>{{ __('Email') }} <span class="text-danger">*</span></label>
                                <div class="invalid-feedback d-none" id="child-email-error-${childIndex}"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="text" name="children[${childIndex}][phone]" class="form-control">
                                <label>{{ __('Phone') }}</label>
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
                                <textarea name="children[${childIndex}][address]" class="form-control" style="height: 80px"></textarea>
                                <label>{{ __('Address') }}</label>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-floating mb-3">
                                <textarea name="children[${childIndex}][emergency_contact]" class="form-control" style="height: 80px"></textarea>
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
        
        // Add blur validation to email field
        const newEmailInput = document.querySelector(`[data-child-index="${childIndex}"]`);
        if (newEmailInput) {
            newEmailInput.addEventListener('blur', async function() {
                const email = this.value.trim();
                const errorDiv = document.getElementById(`child-email-error-${childIndex}`);
                
                if (email) {
                    const result = await validateEmail(email);
                    if (result.exists) {
                        this.classList.add('is-invalid');
                        if (errorDiv) {
                            errorDiv.textContent = '{{ __('This email is already registered.') }}';
                            errorDiv.classList.remove('d-none');
                        }
                    } else {
                        this.classList.remove('is-invalid');
                        if (errorDiv) errorDiv.classList.add('d-none');
                    }
                }
            });
        }
    }

    // Remove child
    if (childrenContainer) {
        childrenContainer.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-child-btn') || e.target.closest('.remove-child-btn')) {
                const btn = e.target.classList.contains('remove-child-btn') ? e.target : e.target.closest('.remove-child-btn');
                const childId = btn.dataset.childId;
                const childElement = document.getElementById(`child-${childId}`);
                if (childElement && childCount > 1) {
                    childElement.remove();
                } else if (childCount === 1) {
                    alert('{{ __('You must have at least one child.') }}');
                }
            }
        });
    }

    if (addChildBtn) {
        addChildBtn.addEventListener('click', addChild);
    }

    // Add first child automatically
    addChild();
});
</script>
@endpush
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-xl-6 col-lg-8 col-md-10 col-sm-12 mx-auto">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">{{ __('Step 2: Children Information') }}</h4>
                    <small>{{ __('Add information for each child') }}</small>
                </div>
                <div class="card-body">
                    <!-- Progress Indicator -->
                    <div class="progress mb-4" style="height: 8px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: 66%"></div>
                    </div>
                    <div class="text-center mb-3">
                        <small class="text-muted">{{ __('Step 2 of 3') }}</small>
                    </div>

                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <form method="POST" action="{{ route('public.register.step2.post') }}" enctype="multipart/form-data">
                        @csrf

                        <div id="childrenContainer">
                            <!-- Children will be added here dynamically -->
                        </div>

                        <div class="text-center mb-3">
                            <button type="button" id="addChildBtn" class="btn btn-outline-primary">
                                <i class="fas fa-plus"></i> {{ __('Add Another Child') }}
                            </button>
                        </div>

                        <div class="d-flex gap-2">
                            <a href="{{ route('public.register') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i> {{ __('Back') }}
                            </a>
                            <button type="submit" class="btn btn-primary flex-grow-1">
                                {{ __('Next: Review & Accept Waiver') }} <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
