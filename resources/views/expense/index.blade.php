@extends('layouts.app')

@section('page-title')
    {{ __('Expense') }}
@endsection

@section('breadcrumb')
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}">
                {{ __('Dashboard') }}
            </a>
        </li>
        <li class="breadcrumb-item active">
            <a href="#">{{ __('Expense') }}</a>
        </li>
    </ul>
@endsection

@section('card-action-btn')
    @if (Gate::check('create expense'))
        <a class="btn btn-secondary btn-sm customModal" href="#" data-size="lg"
            data-url="{{ route('expense.create') }}" data-title="{{ __('Create Expense') }}"> <i
                class="ti-plus mr-5"></i>{{ __('Create Expense') }}</a>
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
                                {{ __('Expense') }}
                            </h5>
                        </div>
                        @if (Gate::check('create expense'))
                            <div class="col-auto">
                                <a href="#" class="btn btn-secondary customModal" data-size="lg"
                                    data-url="{{ route('expense.create') }}" data-title="{{ __('Create Expense') }} ">
                                    <i class="ti ti-circle-plus align-text-bottom"></i>
                                    {{ __('Create Expense') }}
                                </a>
                            </div>
                        @endif
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
                                    @if (Gate::check('delete expense') || Gate::check('edit expense') || Gate::check('show expense'))
                                        <th>{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($expenses as $expense)
                                    <tr>
                                        <td>{{ expensePrefix() . $expense->id }}</td>
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
                                        @if (Gate::check('delete expense') || Gate::check('edit expense') || Gate::check('show expense'))
                                            <td>
                                                {!! Form::open(['url' => 'expense/' . $expense->id, 'method' => 'DELETE']) !!}
                                                @if (Gate::check('show expense'))
                                                    <a class="avtar avtar-xs btn-link-warning text-warning customModal" href="#" data-size="lg"
                                                        data-url="{{ route('expense.show', $expense->id) }}"
                                                        data-title="{{ __('View Expense') }}">
                                                        <i data-feather="eye"></i>
                                                    </a>
                                                @endif
                                                @if (Gate::check('edit expense'))
                                                    <a class="avtar avtar-xs btn-link-secondary text-secondary customModal" href="#" data-size="lg"
                                                        data-url="{{ route('expense.edit', $expense->id) }}"
                                                        data-title="{{ __('Edit Expense') }}">
                                                        <i data-feather="edit"></i>
                                                    </a>
                                                @endif
                                                @if (Gate::check('delete expense'))
                                                    <a class="avtar avtar-xs btn-link-danger text-danger confirm_dialog" href="#">
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
