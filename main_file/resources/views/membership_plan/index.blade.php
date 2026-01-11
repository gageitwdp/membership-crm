@extends('layouts.app')
@section('page-title')
    {{ __('Membership Plan') }}
@endsection
@section('breadcrumb')
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}">
                {{ __('Dashboard') }}
            </a>
        </li>
        <li class="breadcrumb-item active">
            <a href="#">
                {{ __('Membership Plan') }}
            </a>
        </li>
    </ul>
@endsection
@section('card-action-btn')
    @if (Gate::check('create membership plan'))
        <a class="btn btn-secondary btn-sm customModal" href="#" data-size="lg"
            data-url="{{ route('membership-plan.create') }}" data-title="{{ __('Create Membership Plan') }}"> <i
                class="ti-plus mr-5"></i>
            {{ __('Create Membership Plan') }}
        </a>
    @endif
@endsection
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card table-card">
                <div class="card-header">
                    <div class="row align-items-center g-2">
                        <div class="col">
                            <h5>
                                {{ __('Membership Plan') }}
                            </h5>
                        </div>
                        @if (Gate::check('create membership plan'))
                            <div class="col-auto">
                                <a href="#" class="btn btn-secondary customModal" data-size="lg"
                                    data-url="{{ route('membership-plan.create') }}"
                                    data-title="{{ __('Create Membership Plan') }} ">
                                    <i class="ti ti-circle-plus align-text-bottom"></i>
                                    {{ __('Create Membership Plan') }}
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>
        <div class="col-sm-12 mb-5">
            <div class="row g-4">
                @foreach ($memberShipPlans as $membershipPlan)
                    @php
                        $MemberLatPlan = lastMembershipPlan();
                    @endphp
                    <div class="col-md-3">
                        <div class="card price-card p-4 border border-secondary border-2 h-100">
                            <div class="card-body bg-secondary bg-opacity-10 rounded v3">

                                {{-- Plan Info --}}
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



                                <div class="mt-auto d-flex justify-content-between gap-2">
                                    @can('show membership plan')
                                        <a class="btn btn-outline-warning btn-sm w-100 customModal"
                                             href="#" data-size="lg"
                                            data-title="{{ __('Show membership plan') }}"
                                            data-url="{{ route('membership-plan.show', $membershipPlan->id) }}">
                                            {{ __('Show') }}</a>
                                    @endcan
                                    @can('edit membership plan')
                                        <a class="btn btn-outline-success btn-sm w-100 customModal"  href="#" data-size="lg"
                                            data-url="{{ route('membership-plan.edit', $membershipPlan) }}"
                                            data-title="{{ __('Edit membership plan') }}"> {{ __('Edit') }}</a>
                                    @endcan
                                    @can('delete membership plan')
                                        {!! Form::open(['method' => 'DELETE', 'route' => ['membership-plan.destroy', $membershipPlan->id]]) !!}
                                        <a class="btn btn-outline-danger btn-sm w-100 confirm_dialog"   href="#">
                                            {{ __('Detete') }}</a>
                                        {!! Form::close() !!}
                                    @endcan

                                </div>

                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
{{--  @if ($activeMembership && $activeMembership->plan_id == $membershipPlan->plan_id)
                                    <span class="badge bg-secondary px-3 py-2 fs-6">{{ __('Active') }}</span><br>
                                    @if ($activeMembership->expiry_date)
                                        <small>{{ __('Expiry Date') }}:
                                            {{ date('d M Y', strtotime($activeMembership->expiry_date)) }}</small>
                                    @else
                                        <small>{{ __('Unlimited') }}</small>
                                    @endif
                                @else
                                    @if (Auth::user()->type == 'member')
                                        @if (!empty($MemberLatPlan) && $MemberLatPlan->plan_id == $membershipPlan->plan_id)
                                            <a href="{{ route('membership-plan.payment', Crypt::encrypt($membershipPlan->id)) }}"
                                                class="btn btn-outline-info mt-3">
                                                {{ __('Renew') }}
                                            </a>
                                        @else
                                            @if ($paymentSetting['STRIPE_PAYMENT'] == 'on' || $paymentSetting['paypal_payment'] == 'on' || $paymentSetting['bank_transfer_payment'] == 'on' || $paymentSetting['flutterwave_payment'] == 'on' || $paymentSetting['paystack_payment'] == 'on')
                                                <a href="{{ route('membership-plan.payment', Crypt::encrypt($membershipPlan->id)) }}"
                                                    class="btn btn-outline-primary mt-3">
                                                    {{ __('Buy Now') }}
                                                </a>
                                            @else
                                                <a href="#"
                                                    class="btn btn-primary disabled mt-3">
                                                    {{ __('Buy Now') }}
                                                </a>
                                            @endif
                                        @endif
                                    @endif
                                @endif
 --}}
