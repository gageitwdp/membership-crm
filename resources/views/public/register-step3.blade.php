@extends('layouts.auth')
@php
    $settings = settingsById(2);
@endphp
@section('tab-title')
    {{ __('Parent Registration - Step 3 - Review & Waiver') }}
@endsection
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-xl-6 col-lg-8 col-md-10 col-sm-12 mx-auto">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">{{ __('Step 3: Review & Accept Waiver') }}</h4>
                    <small>{{ __('Please review your information and accept the waiver') }}</small>
                </div>
                <div class="card-body">
                    <!-- Progress Indicator -->
                    <div class="progress mb-4" style="height: 8px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: 100%"></div>
                    </div>
                    <div class="text-center mb-3">
                        <small class="text-muted">{{ __('Step 3 of 3') }}</small>
                    </div>

                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <!-- Parent Information Summary -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="fas fa-user me-2"></i>{{ __('Parent/Guardian Information') }}</h5>
                        </div>
                        <div class="card-body">
                            <p><strong>{{ __('Name') }}:</strong> {{ $step1Data['parent_first_name'] }} {{ $step1Data['parent_last_name'] }}</p>
                            <p><strong>{{ __('Email') }}:</strong> {{ $step1Data['parent_email'] }}</p>
                            <p><strong>{{ __('Phone') }}:</strong> {{ $step1Data['parent_phone'] }}</p>
                        </div>
                    </div>

                    <!-- Children Information Summary -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="fas fa-users me-2"></i>{{ __('Children') }}</h5>
                        </div>
                        <div class="card-body">
                            @foreach($step2Data['children'] as $index => $child)
                                <div class="mb-3 pb-3 @if(!$loop->last) border-bottom @endif">
                                    <h6>{{ __('Child') }} #{{ $loop->iteration }}</h6>
                                    <p class="mb-1"><strong>{{ __('Name') }}:</strong> {{ $child['first_name'] }} {{ $child['last_name'] }}</p>
                                    <p class="mb-1"><strong>{{ __('Email') }}:</strong> {{ $child['email'] }}</p>
                                    <p class="mb-1"><strong>{{ __('Date of Birth') }}:</strong> {{ $child['dob'] }}</p>
                                    <p class="mb-1"><strong>{{ __('Gender') }}:</strong> {{ $child['gender'] }}</p>
                                    @if(!empty($child['plan_id']))
                                        @php
                                            $plan = \App\Models\MembershipPlan::find($child['plan_id']);
                                        @endphp
                                        @if($plan)
                                            <p class="mb-0"><strong>{{ __('Membership Plan') }}:</strong> {{ $plan->plan_name }} - ${{ $plan->price }}</p>
                                        @endif
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Waiver -->
                    <div class="card mb-3 border-primary">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-file-contract me-2"></i>{{ __('Waiver & Release of Liability') }}</h5>
                        </div>
                        <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                            <h6>{{ __('Liability Waiver Agreement') }}</h6>
                            <p>{{ __('By signing below, I acknowledge and agree to the following:') }}</p>
                            
                            <ol>
                                <li>{{ __('I understand that participation in activities involves inherent risks, including but not limited to physical injury.') }}</li>
                                <li>{{ __('I voluntarily agree to assume all risks associated with my child(ren)\'s participation in these activities.') }}</li>
                                <li>{{ __('I hereby release, waive, discharge, and covenant not to sue the organization, its officers, employees, and agents from any and all liability, claims, demands, actions, and causes of action.') }}</li>
                                <li>{{ __('I certify that my child(ren) is/are physically fit and has/have no medical condition that would prevent safe participation.') }}</li>
                                <li>{{ __('I authorize the organization to obtain emergency medical treatment for my child(ren) if necessary.') }}</li>
                                <li>{{ __('I grant permission for my child(ren)\'s likeness to be used in photographs and videos for promotional purposes.') }}</li>
                                <li>{{ __('I have read this waiver and fully understand its contents. I voluntarily agree to the terms and conditions stated above.') }}</li>
                            </ol>

                            <p class="mt-3"><strong>{{ __('Electronic Signature Agreement:') }}</strong></p>
                            <p>{{ __('By checking the box below and submitting this form, I acknowledge that this electronic signature has the same legal effect as a handwritten signature.') }}</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('public.register.step3.post') }}">
                        @csrf

                        <div class="form-check mb-3">
                            <input class="form-check-input @error('waiver_accepted') is-invalid @enderror" type="checkbox" name="waiver_accepted" id="waiver_accepted" value="1" required>
                            <label class="form-check-label" for="waiver_accepted">
                                {{ __('I have read and accept the Waiver & Release of Liability') }} <span class="text-danger">*</span>
                            </label>
                            @error('waiver_accepted')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            {{ __('After accepting the waiver, your parent account will be created. If you selected membership plans, you will be directed to complete payment.') }}
                        </div>

                        <div class="d-flex gap-2">
                            <a href="{{ route('public.register.step2') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i> {{ __('Back') }}
                            </a>
                            <button type="submit" class="btn btn-success flex-grow-1">
                                <i class="fas fa-check me-2"></i> {{ __('Accept & Continue') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
