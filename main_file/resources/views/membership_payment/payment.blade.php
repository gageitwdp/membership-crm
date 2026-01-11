@php
    $main_logo = getSettingsValByName('company_logo');
    $settings = settings();

@endphp



@if ($amount > 0)
    @if (\Auth::user()->type == 'member')
        <div class="col-xxl-12 cdx-xxl-100">
            <div class="payment-method card">
                {{-- <div class="card-header">
                        <h5> {{ __('Add Payment') }} </h5>
                    </div> --}}
                <div class="card-body">
                    <ul class="nav nav-tabs profile-tabs border-bottom mb-3 d-print-none" id="myTab" role="tablist">
                        @if ($settings['bank_transfer_payment'] == 'on')
                            <li class="nav-item">
                                <a class="nav-link text-sm active" id="profile-tab-1" data-bs-toggle="tab"
                                    href="#bank_transfer" role="tab" aria-selected="true">{{ __('Bank Transfer') }}
                                </a>

                            </li>
                        @endif

                        @if ($settings['STRIPE_PAYMENT'] == 'on' && !empty($settings['STRIPE_KEY']) && !empty($settings['STRIPE_SECRET']))
                            <li class="nav-item">

                                <a class="nav-link text-sm stripe_payment_tab" id="profile-tab-2" data-bs-toggle="tab"
                                    href="#stripe_payment" role="tab" aria-selected="true">{{ __('Stripe') }}</a>
                            </li>
                        @endif


                        @if (
                            $settings['paypal_payment'] == 'on' &&
                                !empty($settings['paypal_client_id']) &&
                                !empty($settings['paypal_secret_key']))
                            <li class="nav-item">
                                <a class="nav-link text-sm" id="profile-tab-3" data-bs-toggle="tab"
                                    href="#paypal_payment" role="tab" aria-selected="true">
                                    {{ __('Paypal') }} </a>
                            </li>
                        @endif

                        @if (
                            $settings['flutterwave_payment'] == 'on' &&
                                !empty($settings['flutterwave_public_key']) &&
                                !empty($settings['flutterwave_secret_key']))
                            <li class="nav-item">
                                <a class="nav-link text-sm" id="profile-tab-3" data-bs-toggle="tab"
                                    href="#flutterwave_payment" role="tab" aria-selected="true">
                                    {{ __('Flutterwave') }}
                                </a>
                            </li>
                        @endif

                        @if (
                            $settings['paystack_payment'] == 'on' &&
                                !empty($settings['paystack_public_key']) &&
                                !empty($settings['paystack_secret_key']))
                            <li class="nav-item">
                                <a class="nav-link text-sm" id="profile-tab-3" data-bs-toggle="tab"
                                    href="#paystack_payment" role="tab" aria-selected="true">
                                    {{ __('Paystack') }}
                                </a>
                            </li>
                        @endif


                    </ul>

                    <div class="tab-content">
                        @if ($settings['bank_transfer_payment'] == 'on')
                            <div class="tab-pane fade active show" id="bank_transfer">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class=" profile-user-box">
                                            <form
                                                action="{{ route('membership.banktransfer.payment', [encrypt($membership->id)]) }}"
                                                method="post" class="require-validation" id="bank-payment"
                                                enctype="multipart/form-data">
                                                @csrf
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="card-name-on"
                                                                class="f-w-600 mb-1 text-start">{{ __('Bank Name') }}</label>
                                                            <p>{{ $settings['bank_name'] }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="card-name-on"
                                                                class="f-w-600 mb-1 text-start">{{ __('Bank Holder Name') }}</label>
                                                            <p>{{ $settings['bank_holder_name'] }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="card-name-on"
                                                                class="f-w-600 mb-1 text-start">{{ __('Bank Account Number') }}</label>
                                                            <p>{{ $settings['bank_account_number'] }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="card-name-on"
                                                                class="f-w-600 mb-1 text-start">{{ __('Bank IFSC Code') }}</label>
                                                            <p>{{ $settings['bank_ifsc_code'] }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                    @if (!empty($settings['bank_other_details']))
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <label for="card-name-on"
                                                                    class="f-w-600 mb-1 text-start">{{ __('Bank Other Details') }}</label>
                                                                <p>{{ $settings['bank_other_details'] }}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    @endif
                                                    <div class="col-md-12">
                                                        <div class="form-group">
                                                            <label for="amount"
                                                                class="form-label text-dark">{{ __('Amount') }}</label>
                                                            <input type="number" name="amount"
                                                                class="form-control required" step="0.01"
                                                                value="{{ $amount }}" readonly
                                                                placeholder="{{ __('Enter Amount') }}" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-group">
                                                            <label for="card-name-on"
                                                                class="form-label text-dark">{{ __('Attachment') }}</label>
                                                            <input type="file" name="receipt" id="receipt"
                                                                class="form-control" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-group">
                                                            <label for="notes"
                                                                class="form-label text-dark">{{ __('Notes') }}</label>
                                                            <input type="text" name="notes" class="form-control "
                                                                value="" placeholder="{{ __('Enter notes') }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-12 ">
                                                        <input type="submit" value="{{ __('Pay') }}"
                                                            class="btn btn-secondary">
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if ($settings['STRIPE_PAYMENT'] == 'on' && !empty($settings['STRIPE_KEY']) && !empty($settings['STRIPE_SECRET']))
                            <div class="tab-pane fade " id="stripe_payment">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class=" profile-user-box">
                                            <form
                                                action="{{ route('membership.stripe.payment', [encrypt($membership->id)]) }}"
                                                method="post" class="require-validation" id="stripe-payment">
                                                @csrf
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="form-group">
                                                            <label for="amount"
                                                                class="form-label text-dark">{{ __('Amount') }}</label>
                                                            <input type="number" name="amount" step="0.01"
                                                                class="form-control required"
                                                                value="{{ $amount }}" readonly
                                                                placeholder="{{ __('Enter Amount') }}" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-group">
                                                            <label for="card-name-on"
                                                                class="form-label text-dark">{{ __('Card Name') }}</label>
                                                            <input type="text" name="name" id="card-name-on"
                                                                class="form-control required"
                                                                placeholder="{{ __('Card Holder Name') }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <label for="card-name-on"
                                                            class="form-label text-dark">{{ __('Card Details') }}</label>
                                                        <div id="card-element">
                                                        </div>
                                                        <div id="stripe_card_errors" role="alert">
                                                        </div>
                                                    </div>


                                                    <div class="col-sm-12 mt-3">
                                                        <input type="submit" value="{{ __('Pay Now') }}"
                                                            class="btn btn-secondary">
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if (
                            $settings['paypal_payment'] == 'on' &&
                                !empty($settings['paypal_client_id']) &&
                                !empty($settings['paypal_secret_key']))
                            <div class="tab-pane fade" id="paypal_payment">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class=" profile-user-box">
                                            <form action="{{ route('membership.paypal', [encrypt($membership->id)]) }}"
                                                method="post" class="require-validation">
                                                @csrf
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="form-group">
                                                            <label for="amount"
                                                                class="form-label text-dark">{{ __('Amount') }}</label>
                                                            <input type="number" name="amount" step="0.01"
                                                                class="form-control required"
                                                                value="{{ $amount }}" readonly
                                                                placeholder="{{ __('Enter Amount') }}" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-12 ">
                                                        <input type="submit" value="{{ __('Pay Now') }}"
                                                            class="btn btn-secondary">
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif


                        @if (
                            $settings['flutterwave_payment'] == 'on' &&
                                !empty($settings['flutterwave_public_key']) &&
                                !empty($settings['flutterwave_secret_key']))
                            <div class="tab-pane fade" id="flutterwave_payment">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class=" profile-user-box">
                                            <form action="#" method="post" class="require-validation"
                                                id="flutterwavePaymentForm">
                                                @csrf
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="form-group">

                                                            <label for="amount"
                                                                class="form-label text-dark">{{ __('Amount') }}</label>

                                                            <input type="number" name="amount" step="0.01"
                                                                class="form-control amount required"
                                                                value="{{ $amount }}" readonly
                                                                placeholder="{{ __('Enter Amount') }}" required>
                                                        </div>
                                                    </div>

                                                    <div class="col-sm-12 ">
                                                        <input type="button" value="{{ __('Pay Now') }}"
                                                            class="btn btn-secondary" id="flutterwavePaymentBtn">
                                                    </div>

                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif


                        @if (
                            $settings['paystack_payment'] == 'on' &&
                                !empty($settings['paystack_public_key']) &&
                                !empty($settings['paystack_secret_key']))
                            <div class="tab-pane fade" id="paystack_payment">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class=" profile-user-box">
                                            <form class="require-validation" method="POST"
                                                id="paystack-payment-form"
                                                action="{{ route('membership.paystack.payment', [encrypt($membership->id)]) }}">

                                                @csrf
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="form-group">
                                                            <label for="amount"
                                                                class="form-label text-dark">{{ __('Amount') }}</label>
                                                            <input type="number" name="amount" step="0.01"
                                                                class="form-control amount required"
                                                                value="{{ $amount }}" readonly
                                                                placeholder="{{ __('Enter Amount') }}" required>
                                                        </div>
                                                    </div>

                                                    <div class="col-sm-12 ">
                                                        <input type="button" value="{{ __('Pay Now') }}"
                                                            class="btn btn-secondary" id="paystackPaymentBtn">
                                                    </div>

                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif


                    </div>
                </div>
            </div>
        </div>
    @else
        {!! Form::model($membership, [
            'route' => ['membership.renew', $membership->id],
            'method' => 'POST',
            'enctype' => 'multipart/form-data',
        ]) !!}

        <div class="modal-body">
            <div class="row">
                <div class="form-group col-md-12">
                    {{ Form::label('payment_date', __('Payment Date'), ['class' => 'form-label']) }}
                    {{ Form::date('payment_date', today(), ['class' => 'form-control', 'required' => 'required']) }}
                </div>
                <div class="form-group col-md-12">
                    {{ Form::label('amount', __('Amount'), ['class' => 'form-label']) }}
                    {{ Form::text('amount', $amount, ['class' => 'form-control', 'placeholder' => __('Enter amount'), 'readonly' => 'readonly']) }}
                </div>
                <div class="form-group col-md-12">
                    {{ Form::label('payment_method', __('Payment Method'), ['class' => 'form-label']) }}
                    {{ Form::select('payment_method', $payment_methods, null, ['class' => 'form-control basic-select', 'required' => 'required']) }}
                </div>
                <div class="form-group col-md-12">
                    {{ Form::label('note', __('Note'), ['class' => 'form-label']) }}
                    {{ Form::textarea('note', null, ['class' => 'form-control', 'rows' => 3]) }}
                </div>
            </div>
        </div>
        <div class="modal-footer">

            {{ Form::submit(__('Pay'), ['class' => 'btn btn-secondary ml-10']) }}
        </div>
        {!! Form::close() !!}

    @endif
@endif



