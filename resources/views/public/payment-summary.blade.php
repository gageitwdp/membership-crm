<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Payment Summary') }} - {{ $settings['app_name'] ?? 'Membership Portal' }}</title>
    <link rel="icon" href="{{ asset(Storage::url('upload/logo/favicon.png')) }}" type="image/x-icon" />
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
</head>
<body>
    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0">{{ __('Payment Summary') }}</h3>
                    </div>
                    <div class="card-body">
                        
                        @if(session('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h5>{{ __('Member Information') }}</h5>
                                <p class="mb-1"><strong>{{ __('Name') }}:</strong> {{ $member->first_name }} {{ $member->last_name }}</p>
                                <p class="mb-1"><strong>{{ __('Email') }}:</strong> {{ $member->email }}</p>
                                <p class="mb-1"><strong>{{ __('Phone') }}:</strong> {{ $member->phone }}</p>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <h5>{{ __('Payment Date') }}</h5>
                                <p class="mb-1"><strong>{{ __('Today') }}:</strong> {{ now()->format('F d, Y') }}</p>
                                <p class="mb-1"><strong>{{ __('Billing Start') }}:</strong> {{ now()->format('F d, Y') }}</p>
                            </div>
                        </div>

                        <div class="table-responsive mb-4">
                            <table class="table table-bordered">
                                <thead class="bg-light">
                                    <tr>
                                        <th>{{ __('Member Name') }}</th>
                                        <th>{{ __('Membership Plan') }}</th>
                                        <th>{{ __('Duration') }}</th>
                                        <th>{{ __('Billing Frequency') }}</th>
                                        <th>{{ __('Expiry Date') }}</th>
                                        <th class="text-end">{{ __('Amount') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($items as $item)
                                        <tr>
                                            <td>{{ $item['member_name'] }}</td>
                                            <td>{{ $item['plan_name'] }}</td>
                                            <td>{{ $item['duration'] }}</td>
                                            <td>{{ $item['billing_frequency'] }}</td>
                                            <td>{{ \Carbon\Carbon::parse($item['expiry_date'])->format('F d, Y') }}</td>
                                            <td class="text-end">{{ priceFormat($item['amount']) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-light">
                                    <tr>
                                        <th colspan="5" class="text-end">{{ __('Total Amount Due Today') }}:</th>
                                        <th class="text-end">{{ priceFormat($totalAmount) }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="alert alert-info">
                            <i class="ti ti-info-circle"></i>
                            <strong>{{ __('Important') }}:</strong> {{ __('After payment is processed, all memberships will be activated and you will receive a confirmation email with your login credentials.') }}
                        </div>

                        <h5 class="mb-3">{{ __('Select Payment Method') }}</h5>

                        <form action="{{ route('public.register.payment') }}" method="POST" id="paymentForm">
                            @csrf

                            <div class="row">
                                @if($invoicePaymentSettings['bank_transfer_payment'] == 'on')
                                    <div class="col-md-4 mb-3">
                                        <div class="card payment-method-card">
                                            <div class="card-body text-center">
                                                <input type="radio" name="payment_method" value="bank_transfer" id="bank_transfer" required>
                                                <label for="bank_transfer" class="d-block mt-2">
                                                    <i class="ti ti-building-bank" style="font-size: 2rem;"></i>
                                                    <h6 class="mt-2">{{ __('Bank Transfer') }}</h6>
                                                    <small class="text-muted">{{ __('Manual verification required') }}</small>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @if($invoicePaymentSettings['STRIPE_PAYMENT'] == 'on')
                                    <div class="col-md-4 mb-3">
                                        <div class="card payment-method-card">
                                            <div class="card-body text-center">
                                                <input type="radio" name="payment_method" value="stripe" id="stripe" required>
                                                <label for="stripe" class="d-block mt-2">
                                                    <i class="ti ti-credit-card" style="font-size: 2rem;"></i>
                                                    <h6 class="mt-2">{{ __('Credit/Debit Card') }}</h6>
                                                    <small class="text-muted">{{ __('Secure Online Payment') }}</small>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @if($invoicePaymentSettings['paypal_payment'] == 'on')
                                    <div class="col-md-4 mb-3">
                                        <div class="card payment-method-card">
                                            <div class="card-body text-center">
                                                <input type="radio" name="payment_method" value="paypal" id="paypal" required>
                                                <label for="paypal" class="d-block mt-2">
                                                    <i class="ti ti-brand-paypal" style="font-size: 2rem;"></i>
                                                    <h6 class="mt-2">{{ __('PayPal') }}</h6>
                                                    <small class="text-muted">{{ __('Pay with PayPal') }}</small>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Bank Transfer Details (shown when selected) -->
                            <div id="bankTransferDetails" style="display: none;" class="mb-4">
                                <div class="alert alert-warning">
                                    <h6>{{ __('Bank Transfer Instructions') }}</h6>
                                    <p class="mb-1"><strong>{{ __('Bank Name') }}:</strong> {{ $invoicePaymentSettings['bank_name'] ?? 'N/A' }}</p>
                                    <p class="mb-1"><strong>{{ __('Account Number') }}:</strong> {{ $invoicePaymentSettings['bank_account_number'] ?? 'N/A' }}</p>
                                    <p class="mb-1"><strong>{{ __('Account Holder') }}:</strong> {{ $invoicePaymentSettings['bank_holder_name'] ?? 'N/A' }}</p>
                                    <p class="mb-1"><strong>{{ __('Amount') }}:</strong> {{ priceFormat($totalAmount) }}</p>
                                    <p class="mb-0 mt-2 text-danger"><small>{{ __('Note: Your membership will be activated after we verify your payment (usually within 1-2 business days).') }}</small></p>
                                </div>
                            </div>

                            <!-- Stripe Card Details (shown when selected) -->
                            @if($invoicePaymentSettings['STRIPE_PAYMENT'] == 'on')
                            <div id="stripeCardDetails" style="display: none;" class="mb-4">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="mb-3">{{ __('Enter Card Details') }}</h6>
                                        <div class="form-group mb-3">
                                            <label>{{ __('Cardholder Name') }}</label>
                                            <input type="text" class="form-control" id="cardholder_name" placeholder="{{ __('Name on card') }}" required>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label>{{ __('Card Information') }}</label>
                                            <div id="card-element" class="form-control" style="height: 40px; padding-top: 10px;">
                                                <!-- Stripe Card Element will be inserted here -->
                                            </div>
                                            <div id="card-errors" class="text-danger mt-2" role="alert"></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label>{{ __('Billing ZIP/Postal Code') }}</label>
                                                <input type="text" class="form-control" id="billing_postal_code" placeholder="{{ __('ZIP Code') }}">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label>{{ __('Country') }}</label>
                                                <input type="text" class="form-control" id="billing_country" placeholder="{{ __('Country') }}">
                                            </div>
                                        </div>
                                        <div class="alert alert-info">
                                            <i class="ti ti-lock"></i>
                                            <small>{{ __('Your payment information is secure and encrypted') }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <input type="hidden" name="stripe_token" id="stripe_token">

                            <div class="d-flex justify-content-between">
                                <a href="{{ route('public.register') }}" class="btn btn-secondary">
                                    <i class="ti ti-arrow-left"></i> {{ __('Back to Registration') }}
                                </a>
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="ti ti-check"></i> {{ __('Complete Payment') }}
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/js/plugins/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/bootstrap.min.js') }}"></script>
    @if($invoicePaymentSettings['STRIPE_PAYMENT'] == 'on')
    <script src="https://js.stripe.com/v3/"></script>
    @endif
    
    <script>
        $(document).ready(function() {
            let stripe = null;
            let cardElement = null;

            @if($invoicePaymentSettings['STRIPE_PAYMENT'] == 'on' && !empty($invoicePaymentSettings['STRIPE_KEY']))
            // Initialize Stripe
            stripe = Stripe('{{ $invoicePaymentSettings['STRIPE_KEY'] }}');
            const elements = stripe.elements();
            
            // Create card element
            const style = {
                base: {
                    fontSize: '16px',
                    color: '#32325d',
                    fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
                    '::placeholder': {
                        color: '#aab7c4'
                    }
                },
                invalid: {
                    color: '#fa755a',
                    iconColor: '#fa755a'
                }
            };
            
            cardElement = elements.create('card', {style: style});
            @endif

            // Show/hide payment method details
            $('input[name="payment_method"]').on('change', function() {
                const method = $(this).val();
                
                // Hide all payment details
                $('#bankTransferDetails').slideUp();
                $('#stripeCardDetails').slideUp();
                
                // Show relevant payment details
                if (method === 'bank_transfer') {
                    $('#bankTransferDetails').slideDown();
                } else if (method === 'stripe') {
                    $('#stripeCardDetails').slideDown();
                    
                    // Mount Stripe card element if not already mounted
                    @if($invoicePaymentSettings['STRIPE_PAYMENT'] == 'on' && !empty($invoicePaymentSettings['STRIPE_KEY']))
                    if (cardElement && !cardElement._mounted) {
                        cardElement.mount('#card-element');
                        cardElement._mounted = true;
                        
                        // Handle real-time validation errors
                        cardElement.on('change', function(event) {
                            const displayError = document.getElementById('card-errors');
                            if (event.error) {
                                displayError.textContent = event.error.message;
                            } else {
                                displayError.textContent = '';
                            }
                        });
                    }
                    @endif
                }
            });

            // Style selected payment method card
            $('input[name="payment_method"]').on('change', function() {
                $('.payment-method-card').removeClass('border-primary');
                $(this).closest('.payment-method-card').addClass('border-primary border-2');
            });

            // Handle form submission
            $('#paymentForm').on('submit', function(e) {
                const paymentMethod = $('input[name="payment_method"]:checked').val();
                
                if (paymentMethod === 'stripe') {
                    e.preventDefault();
                    
                    @if($invoicePaymentSettings['STRIPE_PAYMENT'] == 'on' && !empty($invoicePaymentSettings['STRIPE_KEY']))
                    // Disable submit button
                    const submitBtn = $(this).find('button[type="submit"]');
                    submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>{{ __("Processing...") }}');
                    
                    // Create payment method with Stripe
                    const cardholderName = $('#cardholder_name').val();
                    const billingDetails = {
                        name: cardholderName,
                        address: {
                            postal_code: $('#billing_postal_code').val() || null,
                            country: $('#billing_country').val() || null
                        }
                    };
                    
                    stripe.createPaymentMethod({
                        type: 'card',
                        card: cardElement,
                        billing_details: billingDetails
                    }).then(function(result) {
                        if (result.error) {
                            // Show error
                            $('#card-errors').text(result.error.message);
                            submitBtn.prop('disabled', false).html('<i class="ti ti-check"></i> {{ __("Complete Payment") }}');
                        } else {
                            // Create token for backend processing
                            stripe.createToken(cardElement).then(function(tokenResult) {
                                if (tokenResult.error) {
                                    $('#card-errors').text(tokenResult.error.message);
                                    submitBtn.prop('disabled', false).html('<i class="ti ti-check"></i> {{ __("Complete Payment") }}');
                                } else {
                                    // Add token to form and submit
                                    $('#stripe_token').val(tokenResult.token.id);
                                    $('#paymentForm')[0].submit();
                                }
                            });
                        }
                    });
                    @else
                    alert('{{ __("Stripe is not properly configured") }}');
                    return false;
                    @endif
                }
                // For bank transfer and PayPal, allow normal form submission
            });
        });
    </script>

    <style>
        .payment-method-card {
            cursor: pointer;
            transition: all 0.3s;
        }
        .payment-method-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .payment-method-card input[type="radio"] {
            cursor: pointer;
        }
        .payment-method-card label {
            cursor: pointer;
        }
    </style>
</body>
</html>
