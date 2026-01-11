@extends('layouts.app')

@section('page-title')
    {{ __('Expense Report') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item" aria-current="page"> {{ __('Expense Report') }}</li>
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
                            <h5 class="mb-0">{{ __('Expense Report') }}</h5>
                        </div>

                        <form action="{{ route('report.expense') }}" method="get">
                            <div class="row gx-2 gy-1 align-items-end">
                                <div class="cust-pro col">
                                    {{ Form::label('type', __('Expense Type'), ['class' => 'form-label']) }}
                                    {{ Form::select('type', $types, request('type'), [
                                        'class' => 'form-control hidesearch',
                                        'id' => 'member_id',
                                    ]) }}
                                </div>

                                <div class="cust-pro col">
                                    {{ Form::label('start_date', __('Start Date'), ['class' => 'form-label']) }}
                                    <input class="form-control" name="start_date" type="date"
                                        value="{{ request('start_date') }}">
                                </div>

                                <div class="cust-pro col">
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
                                    <a href="{{ route('report.expense') }}" class="btn btn-light-dark px-3">
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
                                    <th>{{ __('ID') }}</th>
                                    <th>{{ __('Title') }}</th>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('Receipt') }}</th>
                                </tr>

                            </thead>
                            <tbody>
                                @foreach ($expenses as $expense)
                                    <tr>
                                        <td>
                                            @if (Gate::check('show expense'))
                                            <a class="customModal" href="#" data-size="lg"
                                                data-url="{{ route('expense.show', $expense->id) }}"
                                                data-title="{{ __('View Expense') }}">{{ expensePrefix() . $expense->id }}
                                            </a>
                                            @else
                                            {{ expensePrefix() . $expense->id }}
                                            @endif
                                        </td>
                                        <td>{{ $expense->title }}</td>
                                        <td>{{ dateFormat($expense->date) }}</td>
                                        <td>{{ priceFormat($expense->amount) }}</td>
                                        <td>{{ !empty($expense->expenseType) ? $expense->expenseType->type : '-' }}</td>
                                        <td>
                                            @if (!empty($expense->receipt))
                                                <a href="{{ asset(Storage::url('upload/receipt')) . '/' . $expense->receipt }}"
                                                    download="download"><i data-feather="download"></i></a>
                                            @else
                                                -
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
