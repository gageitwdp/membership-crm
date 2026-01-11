@extends('layouts.app')
@section('page-title')
    {{ __('Payments') }}
@endsection
@php
    $admin_logo = getSettingsValByName('company_logo');
    $settings = settings();
@endphp
@push('script-page')
    <script>
        $(document).on('click', '.print', function() {
            $('.action').addClass('d-none');
            var printContents = document.getElementById('invoice-print').innerHTML;
            var originalContents = document.body.innerHTML;
            document.body.innerHTML = printContents;
            window.print();
            document.body.innerHTML = originalContents;
            $('.action').removeClass('d-none');
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

            var tx_ref = "RX1_" + Math.floor((Math.random() * 1000000000) + 1);
            var customer_email = '{{ \Auth::user()->email }}';
            var customer_name = '{{ \Auth::user()->name }}';
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
                            "{{ url('membership/flutterwave') }}/{{ \Illuminate\Support\Facades\Crypt::encrypt($payment->id) }}/" +
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

    {{-- ************************* paystack payment script ************************* --}}
    <script src="{{ asset('assets/js/plugins/jquery.form.min.js') }}"></script>
    <script src="https://js.paystack.co/v1/inline.js"></script>
    @if (isset($invoicePaymentSettings['paystack_payment']) && $invoicePaymentSettings['paystack_payment'] == 'on')
        <script>
            $(document).ready(function() {
                $(document).on("click", "#paystackPaymentBtn", function(e) {
                    e.preventDefault();

                    const $button = $(this);
                    const $paymentForm = $('#paystack-payment-form');
                    const formActionUrl = $paymentForm.attr('action');
                    const formMethod = $paymentForm.attr('method');
                    const formSerializedData = $paymentForm.serialize();

                    const paystackPublicKey = "{{ $invoicePaymentSettings['paystack_public_key'] }}";
                    const redirectBaseUrl = "{{ url('/membership/paystack') }}";
                    const encryptedInvoiceId = "{{ encrypt($payment->id) }}";

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

@section('breadcrumb')
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}">
                {{ __('Dashboard') }}
            </a>
        </li>
        {{-- <li class="breadcrumb-item">
            <a href="{{ route('membership-payment.index') }}">{{ __('Payments') }}</a>
        </li> --}}
        <li class="breadcrumb-item active">
            <a href="#">{{ __('Details') }}</a>
        </li>
    </ul>
@endsection

<style>
    @media print {
        .no-print {
            display: none !important;
        }
    }
</style>
@section('content')
    <div class="row" id="invoice-print">
        <div class="row">
            <div class="col-sm-12">
                <div class="d-print-none card mb-3">
                    <div class="card-body p-3">
                        <ul class="list-inline ms-auto mb-0 d-flex justify-content-end flex-wrap">

                            {{-- <li class="list-inline-item align-bottom me-2">
                              @if ($payment->status != 'Paid')
                                    <a href="#" class="btn btn-secondary btn-sm ml-20 customModal" data-size="md"
                                        data-url="{{ route('membership-payment.edit', $payment->id) }}"
                                        data-title="{{ __('Add Payment') }}">
                                        <i class="ph-duotone ph-credit-card"></i> {{ __('Add Payment') }}
                                    </a>
                                @endif
                            </li> --}}
                            <li class="list-inline-item align-bottom me-2">
                                <a href="#" class="btn btn-secondary btn-sm print">
                                    <i class="ph-duotone ph-printer"></i> {{ __('Download') }}
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="card">

                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="row align-items-center g-3">
                                    <div class="col-sm-6">
                                        <div class="d-flex align-items-center mb-2 navbar-brand img-fluid invoice-logo">
                                            <img src="{{ asset(Storage::url('upload/logo/')) . '/' . (isset($admin_logo) && !empty($admin_logo) ? $admin_logo : 'logo.png') }}"
                                                class="img-fluid brand-logo" alt="images" />
                                        </div>
                                        <p class="mb-0">{{ paymentPrefix() . $payment->payment_id }}</p>
                                    </div>
                                    <div class="col-sm-6 text-sm-end">
                                        <h6>
                                            {{ __('Start Date') }}
                                            <span class="text-muted f-w-400">
                                                {{ !empty($membership) ? dateFormat($membership->start_date) : '-' }}</span>
                                        </h6>
                                        <h6>
                                            {{ __('End Date') }}
                                            <span
                                                class="text-muted f-w-400">{{ !empty($membership) ? dateFormat($membership->expiry_date) : '-' }}</span>
                                        </h6>

                                        {{-- @dd($payment) --}}
                                        <h6>
                                            {{ __('Status') }}
                                            @if ($payment->status == 'Paid')
                                                <span class="badge text-bg-success">{{ $payment->status }}</span>
                                            @else
                                                <span class="badge text-bg-danger">{{ $payment->status }}</span>
                                            @endif
                                        </h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="border rounded p-3">
                                    <h6 class="mb-0">From:</h6>
                                    <h5>{{ $settings['company_name'] }}</h5>
                                    <p class="mb-0">{{ $settings['company_phone'] }}</p>
                                    <p class="mb-0">{{ $settings['company_email'] }}</p>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="border rounded p-3">
                                    <h6 class="mb-0">To:</h6>
                                    <h5> <span
                                            class="text-sm text-muted">{{ memberPrefix() . $payment->members->member_id }}</span>
                                        <br>
                                        {{ !empty($payment->members) ? $payment->members->first_name . ' ' . $payment->members->last_name : '-' }}
                                    </h5>
                                    <p class="mb-0">
                                        {{ !empty($payment->members) ? $payment->members->phone : '-' }}
                                    </p>
                                    <p class="mb-0">
                                        {{ !empty($payment->members) ? $payment->members->address : '' }}
                                    </p>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                {{-- <th>{{ __('ID') }}</th> --}}
                                                <th>{{ __('Plan Name') }}</th>
                                                <th>{{ __('Duration') }}</th>
                                                <th>{{ __('Billing Frequency') }}</th>
                                                <th>{{ __('Period') }}</th>
                                                <th>{{ __('Amount') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                {{-- <td>{{ $payment->plan_id }}</td> --}}
                                                <td>{{ $payment->plans->plan_name }}</td>
                                                <td>{{ $payment->plans->duration }}</td>
                                                <td>{{ $payment->plans->billing_frequency }}</td>
                                                <td> {{ dateFormat($membership->start_date) ?? '-' }} -
                                                    {{ dateFormat($membership->expiry_date) ?? '-' }}</td>
                                                <td>{{ priceFormat($payment->plans->price) }}</td>
                                            </tr>
                                    </table>
                                </div>
                                <div class="text-start">
                                    <hr class="mb-2 mt-1 border-secondary border-opacity-50" />
                                </div>
                            </div>
                        </div>


                    </div>
                </div>

            </div>
        </div>


        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ __('Payment History') }}</h5>
                    </div>
                    <div class="card-body pt-0">
                        <div class="dt-responsive table-responsive">
                            <table class="table table-hover ">
                                <thead>
                                    <tr>
                                        <th>{{ __('Payment Date') }}</th>
                                        <th>{{ __('Amount') }}</th>
                                        <th>{{ __('Type') }}</th>
                                        <th>{{ __('Notes') }}</th>
                                        @if(Gate::check('delete membership payment') || $payment->status == 'Pending')
                                            <th class="text-right no-print">{{ __('Action') }}</th>
                                        @endif
                                    </tr>
                                </thead>

                                {{-- @dd($payment); --}}
                                <tbody>
                                    @if ($payment->status != 'Unpaid')
                                        <tr role="row">
                                            <td>{{ dateFormat($payment->payment_date) }} </td>
                                            <td>{{ priceFormat($payment->amount) }} </td>
                                            <td>{{ __($payment->payment_method) }} </td>
                                            <td>{{ !empty($payment->notes) ? $payment->notes : '-' }} </td>


                                            <td class="text-right no-print">
                                                <div class="cart-action">
                                                    {!! Form::open([
                                                        'route' => ['membership-payment.destroy', $payment->id],
                                                        'method' => 'DELETE',
                                                    ]) !!}

                                                    @if (\Auth::user()->type == 'member' && $payment->status == 'Pending')
                                                        <a class="avtar avtar-xs btn-link-secondary text-secondary"
                                                            data-bs-toggle="tooltip"
                                                            data-bs-original-title="{{ __('Accept') }}"
                                                            href="{{ route('membership.bank.transfer.action', [$payment->id, 'accept']) }}">
                                                            <i data-feather="user-check"></i></a>

                                                        <a class="avtar avtar-xs btn-link-danger text-danger"
                                                            data-bs-toggle="tooltip"
                                                            data-bs-original-title="{{ __('Reject') }}"
                                                            href="{{ route('membership.bank.transfer.action', [$payment->id, 'reject']) }}">
                                                            <i data-feather="user-x"></i></a>
                                                    @endif

                                                    @can('delete membership payment')
                                                        <a class=" avtar avtar-xs btn-link-danger text-danger confirm_dialog"
                                                            data-bs-toggle="tooltip"
                                                            data-bs-original-title="{{ __('Detete') }}" href="#">
                                                            <i data-feather="trash-2"></i>
                                                        </a>
                                                    @endcan
                                                    {!! Form::close() !!}
                                                </div>
                                            </td>

                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
