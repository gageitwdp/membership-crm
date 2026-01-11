@extends('layouts.app')

@section('page-title')
    {{ __('Payments') }}
@endsection
@section('breadcrumb')
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}">
                {{ __('Dashboard') }}
            </a>
        </li>
        <li class="breadcrumb-item active">
            <a href="#">{{ __('Payments') }}</a>
        </li>
    </ul>
@endsection


@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card table-card">
                <div class="card-header">
                    <div class="row align-items-center g-2">
                        <div class="col">
                            <h5>
                                {{ __('Payments') }}
                            </h5>
                        </div>

                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="dt-responsive table-responsive">
                        <table class="table table-hover advance-datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('ID') }}</th>
                                    <th>{{ __('Member') }}</th>
                                    <th>{{ __('Plan') }}</th>
                                    <th>{{ __('Period') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Status') }}</th>

                                    @if (Gate::check('show membership payment') || Gate::check('delete membership payment'))
                                        <th>{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($membershipPayments as $payment)
                                    @php
                                        $membership = App\Models\Membership::where('member_id', $payment->member_id)
                                            ->where('plan_id', $payment->plan_id)
                                            ->first();
                                    @endphp

                                    <tr>
                                        <td>{{ paymentPrefix() . $payment->payment_id }}</td>
                                        <td>{{ !empty($payment->members) ? $payment->members->first_name : '' }}</td>
                                        <td>{{ !empty($payment->plans) ? $payment->plans->plan_name : '' }}</td>
                                        <td> {{ dateFormat($membership->start_date) ?? '-' }} -
                                            {{ dateFormat($membership->expiry_date) ?? '-' }}</td>

                                        <td>{{ priceFormat($payment->amount) }}</td>
                                        <td>
                                            @if ($payment->status == 'Paid')
                                                <span class="badge text-bg-success">{{ $payment->status }}</span>
                                            @else
                                                <span class="badge text-bg-danger">{{ $payment->status }}</span>
                                            @endif
                                        </td>
                                        @if (Gate::check('show membership payment') || Gate::check('delete membership payment'))
                                            <td>
                                                {!! Form::open(['route' => ['membership-payment.destroy', $payment->id], 'method' => 'DELETE']) !!}
                                                @if (Gate::check('show membership payment'))
                                                    <a class="avtar avtar-xs btn-link-warning text-warning"
                                                        href="{{ route('membership-payment.show', \Illuminate\Support\Facades\Crypt::encrypt($payment->id)) }}">
                                                        <i data-feather="eye"></i>
                                                    </a>
                                                @endif

                                                @if (Gate::check('delete membership payment'))
                                                    <a class="avtar avtar-xs btn-link-danger text-danger confirm_dialog"
                                                        href="#">
                                                        <i data-feather="trash-2"></i>
                                                    </a>
                                                @endif

                                                {!! Form::close() !!}
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
