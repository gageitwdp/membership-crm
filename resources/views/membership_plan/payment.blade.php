@extends('layouts.app')
@section('page-title')
    {{ __('Membership Plan Payment') }}
@endsection

@push('script-page')
    {{-- Stripe --}}
    <script src="https://js.stripe.com/v3/"></script>
    <script>
        @if ($settings['STRIPE_PAYMENT'] == 'on' && !empty($settings['STRIPE_KEY']) && !empty($settings['STRIPE_SECRET']))
            var stripe_key = Stripe('{{ $settings['STRIPE_KEY'] }}');
            var stripe_elements = stripe_key.elements();
            var strip_css = {
                base: {
                    fontSize: '14px',
                    color: '#32325d',
                },
            };
            var stripe_card = stripe_elements.create('card', {
                style: strip_css
            });
            stripe_card.mount('#card-element');

            var stripe_form = document.getElementById('stripe-payment-form');
            stripe_form.addEventListener('submit', function(event) {
                event.preventDefault();


                const billingDetails = {
                    line1: document.querySelector('[name="state"]')?.value || '',
                    city: document.querySelector('[name="city"]')?.value || '',
                    postal_code: document.querySelector('[name="zipcode"]')?.value || '',
                    country: document.querySelector('[name="country"]')?.value || ''
                };

                stripe_key.createToken(stripe_card).then(function(result) {
                    if (result.error) {
                        $("#stripe_card_errors").html(result.error.message);
                        $.NotificationApp.send("Error", result.error.message, "top-right",
                            "rgba(0,0,0,0.2)", "error");
                    } else {
                        var token = result.token;
                        var stripeHiddenData = document.createElement('input');
                        stripeHiddenData.setAttribute('type', 'hidden');
                        stripeHiddenData.setAttribute('name', 'stripeToken');
                        stripeHiddenData.setAttribute('value', token.id);
                        stripe_form.appendChild(stripeHiddenData);
                        stripe_form.submit();
                    }
                });
            });
        @endif
    </script>

    {{-- Flutterwave --}}
    <script src="https://api.ravepay.co/flwv3-pug/getpaidx/api/flwpbf-inline.js"></script>
    <script>
        $(document).on("click", "#flutterwavePaymentBtn", function() {
            var amount = $('.amount').val().trim();
            if (!amount || amount <= 0) {
                alert('Please enter a valid amount');
                return;
            }

            var tx_ref = "RX1_" + Math.floor((Math.random() * 1000000000) + 1);
            var customer_email = '{{ \Auth::user()->email }}';
            var customer_name = '{{ \Auth::user()->name }}';
            var flutterwave_public_key = '{{ $settings['flutterwave_public_key'] }}';
            var currency = '{{ $settings['CURRENCY'] }}';

            var flutterwavePayment = getpaidSetup({
                txref: tx_ref,
                PBFPubKey: flutterwave_public_key,
                amount: amount, // Ensure amount is passed
                currency: currency,
                customer_email: customer_email,
                customer_name: customer_name,
                meta: [{
                    metaname: "payment_id",
                    metavalue: "id"
                }],
                onclose: function() {},
                callback: function(result) {
                    if (result.tx.chargeResponseCode == "00" || result.tx.chargeResponseCode == "0") {
                        var txRef = result.tx.txRef;
                        var redirectUrl =
                            "{{ url('membership/flutterwave') }}/{{ \Illuminate\Support\Facades\Crypt::encrypt($membershipPlan->id) }}/" +
                            txRef + "?amount=" + amount;
                        window.location.href = redirectUrl;
                    } else {
                        alert('Payment failed');
                    }
                    flutterwavePayment.close();
                }
            });
        });
    </script>

    {{-- Paystack --}}
    <script src="{{ asset('assets/js/plugins/jquery.form.min.js') }}"></script>
    <script src="https://js.paystack.co/v1/inline.js"></script>
    @if (isset($settings['paystack_payment']) && $settings['paystack_payment'] == 'on')
        <script>
            $(document).ready(function() {
                $(document).on("click", "#paystackPaymentBtn", function(e) {
                    e.preventDefault();

                    const $button = $(this);
                    const $paymentForm = $('#paystack-payment-form');
                    const formActionUrl = $paymentForm.attr('action');
                    const formMethod = $paymentForm.attr('method');
                    const formSerializedData = $paymentForm.serialize();

                    const paystackPublicKey = "{{ $settings['paystack_public_key'] }}";
                    const redirectBaseUrl = "{{ url('/membership/paystack') }}";
                    const encryptedInvoiceId = "{{ encrypt($membershipPlan->id) }}";

                    $button.prop('disabled', true).text('Processing...');

                    $.ajax({
                        url: formActionUrl,
                        method: formMethod,
                        data: formSerializedData,
                        dataType: 'json',
                        success: function(res) {
                            if (res.flag === 1) {
                                const transactionReference = 'pay_ref_id' + Math.floor(Math
                                    .random() * 1000000000 + 1);
                                const couponId = res.coupon;

                                const paystackOptions = {
                                    key: paystackPublicKey,
                                    email: res.email,
                                    amount: res.total_price * 100,
                                    currency: res.currency,
                                    ref: transactionReference,
                                    metadata: {
                                        custom_fields: [{
                                            display_name: "Email",
                                            variable_name: "email",
                                            value: res.email
                                        }]
                                    },
                                    callback: function(response) {
                                        window.location.href =
                                            `${redirectBaseUrl}/${response.reference}/${encryptedInvoiceId}?`;
                                    },
                                    onClose: function() {
                                        alert(
                                            'Payment popup was closed without completing.'
                                        );
                                        $button.prop('disabled', false).text('Pay Now');
                                    }
                                };

                                const paymentHandler = PaystackPop.setup(paystackOptions);
                                paymentHandler.openIframe();
                            } else if (res.flag === 2) {
                                toastrs('Warning', res.message, 'msg');
                                $button.prop('disabled', false).text('Pay Now');
                            } else {
                                toastrs('Error', res.message, 'msg');
                                $button.prop('disabled', false).text('Pay Now');
                            }
                        },
                        error: function(xhr) {
                            console.error('AJAX Error:', xhr.responseText);
                            toastrs('Error', 'An unexpected error occurred. Please try again.',
                                'msg');
                            $button.prop('disabled', false).text('Pay Now');
                        }
                    });
                });
            });
        </script>
    @endif
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('subscriptions.index') }}">{{ __('Subscription') }}</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ __('Details') }}</li>
@endsection
@section('content')
    <div class="row pricing-grid">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <table class="display dataTable cell-border ">
                        <thead>
                            <tr>
                                <th>{{ __('Plan Id') }}</th>
                                <th>{{ __('Plan Name') }}</th>
                                <th>{{ __('Amount') }}</th>
                                <th>{{ __('Duration') }}</th>
                                <th>{{ __('Billing Frequency') }}</th>

                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    {{ planPrefix() . $membershipPlan->plan_id }}
                                </td>
                                <td>
                                    {{ $membershipPlan->plan_name }}
                                </td>
                                <td>
                                    {{ priceFormat($membershipPlan->price) }}
                                </td>
                                <td>{{ $membershipPlan->duration }} </td>
                                <td>{{ $membershipPlan->billing_frequency }} </td>

                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>


    <div class="row pricing-grid">
        <div class="col-lg-12">
            <div class="row">
                @if ($settings['bank_transfer_payment'] == 'on')
                    <div class="col-sm-6">
                        <div class="card">
                            <div class="card-header">
                                <div class="row align-items-center g-2">
                                    <div class="col">
                                        <h5>{{ __('Bank Transfer Payment') }}</h5>
                                    </div>
                                    <div class="col-auto">

                                    </div>
                                </div>

                            </div>
                            <div class="card-body profile-user-box">
                                <form
                                    action="{{ route('subscription.bank.transfer', \Illuminate\Support\Facades\Crypt::encrypt($membershipPlan->id)) }}"
                                    method="post" class="require-validation" id="bank-payment"
                                    enctype="multipart/form-data">
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="card-name-on"
                                                    class="form-label text-dark">{{ __('Bank Name') }}</label>
                                                <p>{{ $settings['bank_name'] }}</p>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="card-name-on"
                                                    class="form-label text-dark">{{ __('Bank Holder Name') }}</label>
                                                <p>{{ $settings['bank_holder_name'] }}</p>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="card-name-on"
                                                    class="form-label text-dark">{{ __('Bank Account Number') }}</label>
                                                <p>{{ $settings['bank_account_number'] }}</p>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="card-name-on"
                                                    class="form-label text-dark">{{ __('Bank IFSC Code') }}</label>
                                                <p>{{ $settings['bank_ifsc_code'] }}</p>
                                            </div>
                                        </div>
                                        @if (!empty($settings['bank_other_details']))
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="card-name-on"
                                                        class="form-label text-dark">{{ __('Bank Other Details') }}</label>
                                                    <p>{{ $settings['bank_other_details'] }}</p>
                                                </div>
                                            </div>
                                        @endif

                                        <div class="col-md-12 d-none coupon_div">
                                            <div class="form-group">
                                                <label for="card-name-on"
                                                    class="form-label text-dark">{{ __('Coupon Code') }}</label>
                                                <input type="text" name="coupon"
                                                    class="form-control required packageCouponCode"
                                                    placeholder="{{ __('Enter Coupon Code') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="card-name-on"
                                                    class="form-label text-dark">{{ __('Attachment') }}</label>
                                                <input type="file" name="payment_receipt" id="payment_receipt"
                                                    class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-sm-12 ">
                                            <input type="submit" value="{{ __('Pay') }}" class="btn btn-secondary">
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
                @if ($settings['STRIPE_PAYMENT'] == 'on' && !empty($settings['STRIPE_KEY']) && !empty($settings['STRIPE_SECRET']))
                    <div class="col-sm-6">
                        <div class="card">
                            <div class="card-header">
                                <div class="row align-items-center g-2">
                                    <div class="col">
                                        <h5>{{ __('Online Payment') }}</h5>
                                    </div>
                                    <div class="col-auto">

                                    </div>
                                </div>
                            </div>
                            <div class="card-body profile-user-box">
                                <form
                                    action="{{ route('membership.stripe.payment', \Illuminate\Support\Facades\Crypt::encrypt($membershipPlan->id)) }}"
                                    method="post" class="require-validation" id="stripe-payment-form">
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="card-name-on"
                                                    class="form-label text-dark">{{ __('Card Name') }}</label>
                                                <input type="text" name="name" id="card-name-on"
                                                    class="form-control required"
                                                    placeholder="{{ __('Card Holder Name') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="card-name-on"
                                                class="form-label text-dark">{{ __('Card Details') }}</label>
                                            <div id="card-element">
                                            </div>
                                            <div id="stripe_card_errors" role="alert"></div>
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-12 mt-15">
                                                <input type="submit" value="{{ __('Pay') }}"
                                                    class="btn btn-secondary">
                                            </div>
                                        </div>



                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif

                @if (
                    $settings['paypal_payment'] == 'on' &&
                        !empty($settings['paypal_client_id']) &&
                        !empty($settings['paypal_secret_key']))
                    <div class="col-sm-6">
                        <div class="card">
                            <div class="card-header">
                                <div class="row align-items-center g-2">
                                    <div class="col">
                                        <h5>{{ __('Paypal Payment') }}</h5>
                                    </div>
                                    <div class="col-auto">

                                    </div>
                                </div>
                            </div>
                            <div class="card-body profile-user-box">
                                <form
                                    action="{{ route('membership.paypal', \Illuminate\Support\Facades\Crypt::encrypt($membershipPlan->id)) }}"
                                    method="post" class="require-validation">
                                    @csrf
                                    <div class="row">
                                        <div class="col-sm-12 mt-15">
                                            <input type="submit" value="{{ __('Pay') }}" class="btn btn-secondary">
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif

                @if (
                    !empty($settings['flutterwave_payment']) &&
                        $settings['flutterwave_payment'] == 'on' &&
                        !empty($settings['flutterwave_public_key']) &&
                        !empty($settings['flutterwave_secret_key']))
                    <div class="col-sm-6">
                        <div class="card">
                            <div class="card-header">
                                <div class="row align-items-center g-2">
                                    <div class="col">
                                        <h5>{{ __('Flutterwave Payment') }}</h5>
                                    </div>
                                    <div class="col-auto">

                                    </div>
                                </div>
                            </div>
                            <div class="card-body profile-user-box">
                                <form action="#" method="post" class="require-validation"
                                    id="flutterwavePaymentForm">
                                    @csrf

                                    <input type="hidden" class="amount" value="{{ $membershipPlan->price }}">

                                    <div class="row">
                                        <div class="col-sm-12">
                                            <input type="button" value="{{ __('Pay') }}" id="flutterwavePaymentBtn"
                                                class="btn btn-secondary">
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif

                @if (
                    $settings['paystack_payment'] == 'on' &&
                        !empty($settings['paystack_public_key']) &&
                        !empty($settings['paystack_secret_key']))
                    <div class="col-sm-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="d-inline">{{ __('Paystack') }}</h5>

                            </div>
                            <div class="card-body profile-user-box">
                                <form class="require-validation" method="POST" id="paystack-payment-form"
                                    action="{{ route('membership.paystack.payment', encrypt($membershipPlan->id)) }}">
                                    @csrf
                                    <input type="hidden" name="amount" class="amount"
                                        value="{{ $membershipPlan->price }}">
                                    <div class="row">

                                        <div class="col-sm-12">

                                            <input type="button" value="{{ __('Pay') }}" id="paystackPaymentBtn"
                                                class="btn btn-secondary">
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
