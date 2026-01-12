@extends('layouts.app')
@section('page-title')
    {{ __('Dashboard') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item" aria-current="page">{{ __('Dashboard') }}</li>
@endsection
@php
    $settings = settings();
@endphp
@push('script-page')
@endpush
@section('content')
    <div class="row">

        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avtar bg-light-warning">
                                <i class="ti ti-package f-24"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="mb-1">{{ __('Membership Plan') }}</p>
                            <div class="d-flex align-items-center justify-content-between">
                                <h5 class="mb-0 text-muted">{{ $result['MembershipPlan']->plan_name ?? '-' }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avtar bg-light-warning">
                                <i class="ti ti-package f-24"></i>
                            </div>
                        </div>

                        {{-- @dd($Membership) --}}
                        <div class="flex-grow-1 ms-3">
                            <p class="mb-1">{{ __('Membership Expiry Date') }}</p>
                            <div class="d-flex align-items-center justify-content-between">
                                <h5 class="mb-0 text-muted">
                                    {{ !empty($result['Membership']->expiry_date) ? dateformat($result['Membership']->expiry_date) : '-' }}
                                </h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avtar bg-light-primary">
                                <i class="ti ti-history f-24"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="mb-1">{{ __('Total Activity Tracking') }}</p>
                            <div class="d-flex align-items-center justify-content-between">
                                <h5 class="mb-0">{{ $result['totalActivityTrack'] }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avtar bg-light-secondary">
                                <i class="ti ti-users f-24"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="mb-1">{{ __('Total Membership Plan') }}</p>
                            <div class="d-flex align-items-center justify-content-between">
                                <h5 class="mb-0">{{ $result['totalMemberbershipPlan'] }}</h5>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>



        <div class="col-sm-12 mb-5">
            <div class="row g-4">
                {!! Form::hidden('plan', null, ['class' => 'new-plan']) !!}
                @foreach ($memberShipPlans as $membershipPlan)
                    @php
                        $MemberLatPlan = lastMembershipPlan();
                    @endphp
                    <div class="col-md-3">
                        <div class="card price-card p-4 border border-secondary border-2 h-100">
                            <div class="card-body bg-secondary bg-opacity-10 rounded v3">
                                @if (
                                    !empty($MemberLatPlan) &&
                                        $MemberLatPlan->status == 'Payment Pending' &&
                                        $MemberLatPlan->plan_id == $membershipPlan->plan_id)
                                    <div class="position-absolute top-0 end-0 p-2">
                                        <span class="badge bg-secondary px-3 py-2 shadow-sm">
                                            {{ __('Payment Pending') }}
                                        </span>
                                    </div>
                                @endif



                                <h4 class="mb-0 text-secondary">{{ $membershipPlan->plan_name }}</h4>
                                <div class="price-price mt-3">
                                    {{ priceFormat($membershipPlan->price) }}
                                </div>

                                <ul class="list-group list-group-flush product-list v3">
                                    <li class="list-group-item">{{ __('Plan ID') }} :
                                        {{ planPrefix() . $membershipPlan->plan_id }}</li>
                                    <li class="list-group-item">{{ __('Duration') }} : {{ $membershipPlan->duration }}
                                    </li>
                                    <li class="list-group-item">{{ __('Billing Frequency') }} :
                                        {{ $membershipPlan->billing_frequency }}</li>
                                </ul>


                                @if ($activeMembership && $activeMembership->plan_id == $membershipPlan->plan_id)
                                    <span class="badge bg-secondary px-3 py-2 fs-6">{{ __('Active') }}</span><br>
                                    @if ($activeMembership->expiry_date)
                                        <small>{{ __('Expiry Date') }}:
                                            {{ date('d M Y', strtotime($activeMembership->expiry_date)) }}</small>
                                    @else
                                        <small>{{ __('Unlimited') }}</small>
                                    @endif
                                @else
                                    @if (Auth::user()?->type === 'member')
                                        @if (!empty($MemberLatPlan) && $MemberLatPlan->plan_id == $membershipPlan->plan_id)
                                            @if (
                                                $invoicePaymentSettings['STRIPE_PAYMENT'] == 'on' ||
                                                    $invoicePaymentSettings['paypal_payment'] == 'on' ||
                                                    $invoicePaymentSettings['bank_transfer_payment'] == 'on' ||
                                                    $invoicePaymentSettings['flutterwave_payment'] == 'on' ||
                                                    $invoicePaymentSettings['paystack_payment'] == 'on')
                                                <a class="btn btn-outline-info mt-3 customModal buy_now" href="#"
                                                    data-size="lg" data-id="{{ Crypt::encrypt($membershipPlan->id) }}"
                                                    data-url="{{ route('membership-payment.edit', $membershipPlan->id) }}?plan={{ urlencode($membershipPlan->plan_name) }}"
                                                    data-title="{{ __('Renew Plan') }}">
                                                    @if ($MemberLatPlan->status == 'Payment Pending')
                                                        {{ __('Make payment') }}
                                                    @else
                                                        {{ __('Renew') }}
                                                    @endif
                                                </a>
                                            @else
                                                <a href="#" class="btn btn-info disabled mt-3">
                                                    @if ($MemberLatPlan->status == 'Payment Pending')
                                                        {{ __('Make payment') }}
                                                    @else
                                                        {{ __('Renew') }}
                                                    @endif
                                                </a>
                                            @endif
                                        @else
                                            @if (
                                                $invoicePaymentSettings['STRIPE_PAYMENT'] == 'on' ||
                                                    $invoicePaymentSettings['paypal_payment'] == 'on' ||
                                                    $invoicePaymentSettings['bank_transfer_payment'] == 'on' ||
                                                    $invoicePaymentSettings['flutterwave_payment'] == 'on' ||
                                                    $invoicePaymentSettings['paystack_payment'] == 'on')
                                                <a class="btn btn-outline-primary mt-3 customModal buy_now" href="#"
                                                    data-size="lg" data-id="{{ Crypt::encrypt($membershipPlan->id) }}"
                                                    data-url="{{ route('membership-payment.edit', $membershipPlan->id) }}?plan={{ urlencode($membershipPlan->plan_name) }}"
                                                    data-title="{{ __('Buy New Plan ') }}">
                                                    {{ __('Buy Now') }}
                                                </a>
                                            @else
                                                <a href="#" class="btn btn-primary disabled mt-3">
                                                    {{ __('Buy Now') }}
                                                </a>
                                            @endif
                                        @endif
                                    @endif
                                @endif


                            </div>
                        </div>
                    </div>

                    {{--
                    <div class="col-md-3 mb-4">
    <div class="card shadow-lg border-0 h-100 rounded-4 price-card">
        <div class="card-body p-4 position-relative">

             @if ($MemberLatPlan->status == 'Payment Pending')
                <div class="position-absolute top-0 end-0 p-2">
                    <span class="badge bg-warning text-dark px-3 py-2 shadow-sm">
                        {{ __('Payment Pending') }}
                    </span>
                </div>
            @endif

             <h4 class="fw-bold text-primary mb-3">{{ $membershipPlan->plan_name }}</h4>

              <div class="display-6 fw-semibold text-dark">
                {{ priceFormat($membershipPlan->price) }}
            </div>

             <ul class="list-group list-group-flush my-4">
                <li class="list-group-item border-0 ps-0">
                    <i class="bi bi-hash text-primary me-2"></i>
                    {{ __('Plan ID') }}: <span class="fw-semibold">{{ planPrefix() . $membershipPlan->plan_id }}</span>
                </li>
                <li class="list-group-item border-0 ps-0">
                    <i class="bi bi-clock-history text-primary me-2"></i>
                    {{ __('Duration') }}: <span class="fw-semibold">{{ $membershipPlan->duration }}</span>
                </li>
                <li class="list-group-item border-0 ps-0">
                    <i class="bi bi-calendar-check text-primary me-2"></i>
                    {{ __('Billing Frequency') }}:
                    <span class="fw-semibold">{{ $membershipPlan->billing_frequency }}</span>
                </li>
            </ul>

             @if ($activeMembership && $activeMembership->plan_id == $membershipPlan->plan_id)
                <span class="badge bg-success px-3 py-2 fs-6">{{ __('Active') }}</span><br>
                @if ($activeMembership->expiry_date)
                    <small class="text-muted">{{ __('Expiry Date') }}:
                        {{ date('d M Y', strtotime($activeMembership->expiry_date)) }}
                    </small>
                @else
                    <small class="text-muted">{{ __('Unlimited') }}</small>
                @endif
            @else
                 @if (Auth::user()?->type === 'member')
                    @if (!empty($MemberLatPlan) && $MemberLatPlan->plan_id == $membershipPlan->plan_id)
                        <a class="btn btn-outline-info w-100 rounded-3 mt-3 customModal buy_now"
                            data-size="lg"
                            data-id="{{ Crypt::encrypt($membershipPlan->id) }}"
                            data-url="{{ route('membership-payment.edit', $membershipPlan->id) }}?plan={{ urlencode($membershipPlan->plan_name) }}"
                            data-title="{{ __('Renew Plan') }}">
                            {{ $MemberLatPlan->status == 'Payment Pending' ? __('Make Payment') : __('Renew') }}
                        </a>
                    @else
                        <a class="btn btn-outline-primary w-100 rounded-3 mt-3 customModal buy_now"
                            data-size="lg"
                            data-id="{{ Crypt::encrypt($membershipPlan->id) }}"
                            data-url="{{ route('membership-payment.edit', $membershipPlan->id) }}?plan={{ urlencode($membershipPlan->plan_name) }}"
                            data-title="{{ __('Buy New Plan') }}">
                            {{ __('Buy Now') }}
                        </a>
                    @endif
                @endif
            @endif

        </div>
    </div>
</div> --}}
                @endforeach
            </div>
        </div>
    </div>
@endsection

@push('script-page')

    <script>
        $(document).on("click", ".buy_now", function() {
            let planId = $(this).data('id');
            $('.new-plan').val(planId);
        });
    </script>

    <script src="https://js.stripe.com/v3/"></script>

    @if (
        $invoicePaymentSettings['STRIPE_PAYMENT'] == 'on' &&
            !empty($invoicePaymentSettings['STRIPE_KEY']) &&
            !empty($invoicePaymentSettings['STRIPE_SECRET']))
        <script type="text/javascript">
            let stripeCardInstance = null;



            $(document).on('click', '.stripe_payment_tab', function() {
                // Destroy old Stripe element if exists
                if (stripeCardInstance) {
                    stripeCardInstance.unmount();
                    $('#card-element').html(''); // Clear old element content
                }

                // Create new Stripe instance
                const stripe = Stripe('{{ $invoicePaymentSettings['STRIPE_KEY'] }}');
                const elements = stripe.elements();
                const style = {
                    base: {
                        fontSize: '14px',
                        color: '#32325d',
                    },
                };
                stripeCardInstance = elements.create('card', {
                    style: style
                });
                stripeCardInstance.mount('#card-element');

                // Attach submit handler only once
                const stripeForm = document.getElementById('stripe-payment');
                if (!stripeForm.dataset.handlerAttached) {
                    stripeForm.addEventListener('submit', function(event) {
                        event.preventDefault();

                        const billingDetails = {
                            line1: document.querySelector('[name="state"]')?.value || '',
                            city: document.querySelector('[name="city"]')?.value || '',
                            postal_code: document.querySelector('[name="zipcode"]')?.value || '',
                            country: document.querySelector('[name="country"]')?.value || ''
                        };

                        stripe.createToken(stripeCardInstance).then(function(result) {
                            if (result.error) {
                                $("#stripe_card_errors").html(result.error.message);
                                $.NotificationApp.send("Error", result.error.message, "top-right",
                                    "rgba(0,0,0,0.2)", "error");
                            } else {
                                const token = result.token;
                                const hiddenInput = document.createElement('input');
                                hiddenInput.setAttribute('type', 'hidden');
                                hiddenInput.setAttribute('name', 'stripeToken');
                                hiddenInput.setAttribute('value', token.id);
                                stripeForm.appendChild(hiddenInput);
                                stripeForm.submit();
                            }
                        });
                    });
                    stripeForm.dataset.handlerAttached = "true";
                }
            });
        </script>
    @endif


    {{-- ************************* flutterwave payment script ************************* --}}
    <script src="https://api.ravepay.co/flwv3-pug/getpaidx/api/flwpbf-inline.js"></script>
    <script>
        $(document).on("click", "#flutterwavePaymentBtn", function() {
            var amount = $('.amount').val().trim();
            if (!amount || amount <= 0) {
                alert('Please enter a valid amount');
                return;
            }

            let planId = $('.new-plan').val();

            var tx_ref = "RX1_" + Math.floor((Math.random() * 1000000000) + 1);
            var customer_email = '{{ \Auth::user()?->email ?? '' }}';
            var customer_name = '{{ \Auth::user()?->name ?? '' }}';
            var flutterwave_public_key = '{{ $invoicePaymentSettings['flutterwave_public_key'] }}';
            var currency = '{{ $invoicePaymentSettings['CURRENCY'] }}';

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
                            "{{ url('membership/flutterwave') }}/" + planId +
                            "/" + txRef + "?amount=" + amount;

                        window.location.href = redirectUrl;
                    } else {
                        alert('Payment failed');
                    }
                    flutterwavePayment.close();
                }
            });
        });
    </script>

    {{-- ************************* paystack payment script ************************* --}}
    <script src="{{ asset('assets/js/plugins/jquery.form.min.js') }}"></script>
    <script src="https://js.paystack.co/v1/inline.js"></script>
    @if (isset($invoicePaymentSettings['paystack_payment']) && $invoicePaymentSettings['paystack_payment'] == 'on')
        <script>
            $(document).ready(function() {
                $(document).on("click", "#paystackPaymentBtn", function(e) {
                    e.preventDefault();

                    let planId = $('.new-plan').val();

                    const $button = $(this);
                    const $paymentForm = $('#paystack-payment-form');
                    const formActionUrl = $paymentForm.attr('action');
                    const formMethod = $paymentForm.attr('method');
                    const formSerializedData = $paymentForm.serialize();

                    const paystackPublicKey = "{{ $invoicePaymentSettings['paystack_public_key'] }}";
                    const redirectBaseUrl = "{{ url('/membership/paystack') }}";
                    const encryptedInvoiceId = planId;

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
                                            `${redirectBaseUrl}/${response.reference}/${encryptedInvoiceId}?coupon_id=${couponId}`;
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
                                show_toastr('Warning', res.message, 'msg');
                                $button.prop('disabled', false).text('Pay Now');
                            } else {
                                show_toastr('Error', res.message, 'msg');
                                $button.prop('disabled', false).text('Pay Now');
                            }
                        },
                        error: function(xhr) {
                            console.error('AJAX Error:', xhr.responseText);
                            show_toastr('Error', 'An unexpected error occurred. Please try again.',
                                'msg');
                            $button.prop('disabled', false).text('Pay Now');
                        }
                    });
                });
            });
        </script>
    @endif
@endpush
