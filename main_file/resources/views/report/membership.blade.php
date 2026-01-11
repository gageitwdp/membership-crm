@extends('layouts.app')

@section('page-title')
    {{ __('Membership Report') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item" aria-current="page"> {{ __('Membership Report') }}</li>
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
                            <h5 class="mb-0">{{ __('Membership Report') }}</h5>
                        </div>

                        <form action="{{ route('report.membership') }}" method="get">
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
                                    <a href="{{ route('report.membership') }}" class="btn btn-light-dark px-3">
                                        <i class="ti ti-refresh"></i>
                                    </a>
                                </div>

                            </div>
                        </form>
                    </div>
                </div>


                <div class="card-body pt-0">
                    <div class="dt-responsive table-responsive">
                        <table class="table table-hover advance-datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Member') }}</th>
                                    <th>{{ __('Plan') }}</th>
                                    <th>{{ __('Start Date') }}</th>
                                    <th>{{ __('Expiry Date') }}</th>
                                    <th>{{ __('Status') }}</th>

                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($memberships as $membership)
                                     <tr>
                                        <td>{{ !empty($membership->members) ? $membership->members->first_name : '-' }}
                                        </td>
                                        <td>{{ !empty($membership->plans) ? $membership->plans->plan_name : '-' }}</td>
                                        <td>{{ dateFormat($membership->start_date) }}</td>
                                        <td>{{ dateFormat($membership->expiry_date) }}</td>
                                        
                                        <td>
                                            @if ($membership->status == "Expired")
                                                <span class="badge text-bg-danger">{{ __('Expired') }}</span>
                                            @else
                                                <span class="badge text-bg-success">{{ __('Active') }}</span>
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
