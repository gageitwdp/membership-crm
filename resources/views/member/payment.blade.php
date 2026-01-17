@extends('layouts.app')
@php
    $settings = invoicePaymentSettings(parentId());
    $stripeEnabled = $settings['STRIPE_PAYMENT'] == 'on' && !empty($settings['STRIPE_KEY']) && !empty($settings['STRIPE_SECRET']);
@endphp

@section('page-title')
    {{ __('Make Payment') }}
@endsection

@push('script-page')
    @if ($stripeEnabled)
        <script src="https://js.stripe.com/v3/"></script>
        <script>
            // Stripe Elements setup
            var stripe = Stripe('{{ $settings['STRIPE_KEY'] }}');
            var elements = stripe.elements();
            
            var style = {
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
            };
            
            var cardElement = elements.create('card', {style: style});
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

            // Handle form submission
            var form = document.getElementById('payment-form');
            form.addEventListener('submit', async function(event) {
                event.preventDefault();

                var submitButton = document.getElementById('submit-payment');
                submitButton.disabled = true;
                submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';

                try {
                    // Create payment method
                    const {paymentMethod, error} = await stripe.createPaymentMethod({
                        type: 'card',
                        card: cardElement,
                    });

                    if (error) {
                        document.getElementById('card-errors').textContent = error.message;
                        submitButton.disabled = false;
                        submitButton.innerHTML = '<i class="fas fa-credit-card"></i> {{ __("Pay Now") }}';
                        return;
                    }

                    // Create token for backward compatibility
                    const {token, tokenError} = await stripe.createToken(cardElement);
                    
                    if (tokenError) {
                        document.getElementById('card-errors').textContent = tokenError.message;
                        submitButton.disabled = false;
                        submitButton.innerHTML = '<i class="fas fa-credit-card"></i> {{ __("Pay Now") }}';
                        return;
                    }

                    // Add token to form
                    var hiddenInput = document.createElement('input');
                    hiddenInput.setAttribute('type', 'hidden');
                    hiddenInput.setAttribute('name', 'stripeToken');
                    hiddenInput.setAttribute('value', token.id);
                    form.appendChild(hiddenInput);

                    // Submit the form
                    form.submit();

                } catch (error) {
                    alert('Error: ' + error.message);
                    submitButton.disabled = false;
                    submitButton.innerHTML = '<i class="fas fa-credit-card"></i> {{ __("Pay Now") }}';
                }
            });
        </script>
    @endif
@endpush

@section('content')
    <div class="row">
        <div class="col-lg-8 offset-lg-2">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Make Payment for Membership Plan') }}</h5>
                </div>
                <div class="card-body">
                    @if (session('error'))
                        <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
                    @endif
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">{{ session('success') }}</div>
                    @endif

                    @if (isset($membershipPlan))
                        <!-- Plan Details -->
                        <div class="card mb-4 border-primary">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0"><i class="fas fa-info-circle"></i> {{ __('Plan Details') }}</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless">
                                    <tr>
                                        <td class="fw-bold">{{ __('Plan Name') }}:</td>
                                        <td>{{ $membershipPlan->plan_name }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">{{ __('Duration') }}:</td>
                                        <td>{{ $membershipPlan->duration }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">{{ __('Amount') }}:</td>
                                        <td><h4 class="text-primary mb-0">{{ $settings['CURRENCY_SYMBOL'] ?? '$' }}{{ number_format($membershipPlan->price, 2) }}</h4></td>
                                    </tr>
                                    @if ($membershipPlan->plan_description)
                                    <tr>
                                        <td class="fw-bold">{{ __('Description') }}:</td>
                                        <td>{{ $membershipPlan->plan_description }}</td>
                                    </tr>
                                    @endif
                                </table>
                            </div>
                        </div>

                        @if ($stripeEnabled)
                            <!-- Stripe Payment Form -->
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="fas fa-credit-card"></i> {{ __('Payment Information') }}
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-info mb-3">
                                        <i class="fas fa-lock"></i> {{ __('Your payment is secured by Stripe. We never store your card details.') }}
                                    </div>

                                    <form id="payment-form" method="POST" action="{{ route('membership.stripe.payment', \Illuminate\Support\Facades\Crypt::encrypt($membershipPlan->id)) }}">
                                        @csrf
                                        <input type="hidden" name="type" value="membership_plan">
                                        
                                        <div class="form-group mb-3">
                                            <label for="card-element" class="form-label">
                                                {{ __('Credit or Debit Card') }} <span class="text-danger">*</span>
                                            </label>
                                            <div id="card-element" class="form-control" style="height: 45px; padding-top: 12px;"></div>
                                            <div id="card-errors" class="text-danger mt-2" role="alert"></div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="name" class="form-label">{{ __('Name on Card') }}</label>
                                                <input type="text" name="name" id="name" class="form-control" value="{{ Auth::user()->name }}" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="country" class="form-label">{{ __('Country') }}</label>
                                                <input type="text" name="country" id="country" class="form-control" value="US" required>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label for="city" class="form-label">{{ __('City') }}</label>
                                                <input type="text" name="city" id="city" class="form-control" required>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="state" class="form-label">{{ __('State') }}</label>
                                                <input type="text" name="state" id="state" class="form-control" required>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="zipcode" class="form-label">{{ __('Zip Code') }}</label>
                                                <input type="text" name="zipcode" id="zipcode" class="form-control" required>
                                            </div>
                                        </div>

                                        <div class="d-grid gap-2 mt-4">
                                            <button type="submit" id="submit-payment" class="btn btn-primary btn-lg">
                                                <i class="fas fa-credit-card"></i> {{ __('Pay') }} {{ $settings['CURRENCY_SYMBOL'] ?? '$' }}{{ number_format($membershipPlan->price, 2) }}
                                            </button>
                                            <a href="{{ route('member.dashboard') }}" class="btn btn-outline-secondary">
                                                <i class="fas fa-arrow-left"></i> {{ __('Cancel') }}
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i> 
                                {{ __('Stripe payment is not configured. Please contact the administrator.') }}
                            </div>
                        @endif
                    @else
                        <div class="alert alert-danger">
                            <i class="fas fa-times-circle"></i> {{ __('Membership plan not found.') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
