@extends('layouts.app')
@section('page-title')
    {{ __('Expense Type') }}
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
                {{ __('Expense Type') }}
            </a>
        </li>
    </ul>
@endsection
@section('card-action-btn')
    @if (Gate::check('create expense type'))
        <a class="btn btn-secondary btn-sm customModal" href="#" data-size="md"
            data-url="{{ route('expense-type.create') }}" data-title="{{ __('Create Type') }}"> <i class="ti-plus mr-5"></i>
            {{ __('Create Type') }}
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
                                {{ __('Expense Type') }}
                            </h5>
                        </div>
                        @if (Gate::check('create expense type'))
                            <div class="col-auto">
                                <a href="#" class="btn btn-secondary customModal" data-size="md"
                                    data-url="{{ route('expense-type.create') }}"
                                    data-title="{{ __('Create Expense Type') }} ">
                                    <i class="ti ti-circle-plus align-text-bottom"></i>
                                    {{ __('Create Expense Type') }}
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
                                    <th>{{ __('Type') }}</th>
                                    @if (Gate::check('edit expense type') || Gate::check('delete expense type'))
                                        <th>{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($types as $type)
                                    <tr>
                                        <td>{{ $type->type }} </td>
                                        @if (Gate::check('edit expense type') || Gate::check('delete expense type'))
                                            <td>
                                                <div class="cart-action">
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['expense-type.destroy', $type->id]]) !!}
                                                    @can('edit expense type')
                                                        <a class="avtar avtar-xs btn-link-secondary text-secondary customModal" data-bs-toggle="tooltip"
                                                            data-bs-original-title="{{ __('Edit') }}" href="#"
                                                            data-size="md" data-url="{{ route('expense-type.edit', $type) }}"
                                                            data-title="{{ __('Edit Type') }}"> <i data-feather="edit"></i></a>
                                                    @endcan
                                                    @can('delete expense type')
                                                        <a class=" avtar avtar-xs btn-link-danger text-danger confirm_dialog" data-bs-toggle="tooltip"
                                                            data-bs-original-title="{{ __('Detete') }}" href="#"> <i
                                                                data-feather="trash-2"></i></a>
                                                    @endcan
                                                    {!! Form::close() !!}
                                                </div>
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
