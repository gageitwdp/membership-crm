@extends('layouts.app')

@section('page-title')
    {{ __('Income Report') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item" aria-current="page"> {{ __('Income Report') }}</li>
@endsection

@push('script-page')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        "use strict";
        document.addEventListener("DOMContentLoaded", function() {
            const ctx = document.getElementById('incomeChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: @json($chartData->keys()),
                    datasets: [{
                        label: '{{ __('Income') }}',
                        data: @json($chartData->values()),
                        backgroundColor: 'rgba(54, 162, 235, 0.6)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                }
            });
        });
    </script>
@endpush
@push('css-page')
    <style>
        .cust-pro {
            width: 230px;
        }

        .choices__list--dropdown .choices__item--selectable:after {
            content: '';
        }

        .choices__list--dropdown .choices__item--selectable {
            padding-right: 10px;
        }
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card table-card">

                {{-- 🔹 Header + Filters --}}
                <div class="card-header">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div>
                            <h5 class="mb-0">{{ __('Income Report') }}</h5>
                        </div>

                        <form action="{{ route('report.income') }}" method="get">
                            <div class="row gx-2 gy-1 align-items-end">
                                <div class="cust-pro">
                                    {{ Form::label('member_id', __('Member'), ['class' => 'form-label']) }}
                                    {{ Form::select('member_id', $members, request('member_id'), [
                                        'class' => 'form-control hidesearch',
                                        'id' => 'member_id',
                                    ]) }}
                                </div>

                                <div class="cust-pro">
                                    {{ Form::label('plan_id', __('Plan'), ['class' => 'form-label']) }}
                                    {{ Form::select('plan_id', $plans, request('plan_id'), [
                                        'class' => 'form-control hidesearch',
                                        'id' => 'plan_id',
                                    ]) }}
                                </div>

                                <div class="cust-pro">
                                    {{ Form::label('start_date', __('Start Date'), ['class' => 'form-label']) }}
                                    <input class="form-control" name="start_date" type="date"
                                        value="{{ request('start_date') }}">
                                </div>

                                <div class="cust-pro">
                                    {{ Form::label('end_date', __('End Date'), ['class' => 'form-label']) }}
                                    <input class="form-control" name="end_date" type="date"
                                        value="{{ request('end_date') }}">
                                </div>

                                <div class="col-auto">
                                    <button type="submit" class="btn btn-light-secondary px-3">
                                        <i class="ti ti-search"></i>
                                    </button>
                                </div>

                                <div class="col-auto">
                                    <a href="{{ route('report.income') }}" class="btn btn-light-dark px-3">
                                        <i class="ti ti-refresh"></i>
                                    </a>
                                </div>

                            </div>
                        </form>
                    </div>
                </div>

                {{-- 🔹 Chart --}}
                {{-- <div class="card-body">
                    <canvas id="incomeChart" height="100"></canvas>
                </div> --}}

                {{-- 🔹 Table --}}
                <div class="card-body pt-0">
                    <div class="dt-responsive table-responsive">
                        <table class="table table-hover advance-datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Member') }}</th>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Plan') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Payment Method') }}</th>
                                    <th>{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($payments as $payment)
                                    <tr>
                                        <td>{{ $payment->member->first_name ?? '-' }}</td>
                                        <td>{{ dateformat($payment->payment_date) }}</td>
                                        <td>{{ $payment->plan->plan_name ?? '-' }}</td>
                                        <td>{{ priceformat($payment->amount) }}</td>
                                        <td>{{ ucfirst($payment->payment_method) }}</td>
                                        <td>
                                            @if ($payment->status == 'Paid')
                                                <span class="badge bg-light-success">{{ $payment->status }}</span>
                                            @elseif ($payment->status == 'Pending')
                                                <span class="badge bg-light-warning">{{ $payment->status }}</span>
                                            @else
                                                <span class="badge bg-light-danger">{{ $payment->status }}</span>
                                            @endif
                                        </td>
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
