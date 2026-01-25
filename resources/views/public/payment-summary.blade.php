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
    <script>
        $(document).ready(function() {
            // Show/hide bank transfer details
            $('input[name="payment_method"]').on('change', function() {
                if ($(this).val() === 'bank_transfer') {
                    $('#bankTransferDetails').slideDown();
                } else {
                    $('#bankTransferDetails').slideUp();
                }
            });

            // Style selected payment method card
            $('input[name="payment_method"]').on('change', function() {
                $('.payment-method-card').removeClass('border-primary');
                $(this).closest('.payment-method-card').addClass('border-primary border-2');
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
