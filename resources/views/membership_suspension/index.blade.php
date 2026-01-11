@extends('layouts.app')

@section('page-title')
    {{ __('Membership Suspension') }}
@endsection
@section('breadcrumb')
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}">
                {{ __('Dashboard') }}
            </a>
        </li>
        <li class="breadcrumb-item active">
            <a href="#">{{ __('Membership Suspension') }}</a>
        </li>
    </ul>
@endsection
@section('card-action-btn')
    @if (Gate::check('create membership suspension'))
        <a class="btn btn-secondary btn-sm customModal" href="#" data-size="lg"
            data-url="{{ route('membership-suspension.create') }}" data-title="{{ __('Create Membership Suspension') }}"> <i
                class="ti-plus mr-5"></i>{{ __('Create Membership Suspension') }}</a>
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
                                {{ __('Membership Suspension') }}
                            </h5>
                        </div>
                        @if (Gate::check('create membership suspension'))
                            <div class="col-auto">
                                <a href="#" class="btn btn-secondary customModal" data-size="lg"
                                    data-url="{{ route('membership-suspension.create') }}"
                                    data-title="{{ __('Create Membership Suspension') }} ">
                                    <i class="ti ti-circle-plus align-text-bottom"></i>
                                    {{ __('Create Membership Suspension') }}
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
                                    <th>{{ __('Member') }}</th>
                                    <th>{{ __('Start Date') }}</th>
                                    <th>{{ __('End Date') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    @if (Gate::check('edit membership suspension') ||
                                            Gate::check('delete membership suspension') ||
                                            Gate::check('show membership suspension'))
                                        <th>{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($membershipSuspensions as $suspension)
                                    <tr>
                                        <td>{{ suspensionPrefix() . $suspension->suspension_id }}</td>
                                        <td>{{ !empty($suspension->members) ? $suspension->members->first_name : '-' }}</td>
                                        <td>{{ dateFormat($suspension->start_date) }}</td>
                                        <td>{{ dateFormat($suspension->end_date) }}</td>
                                        <td>
                                            @if ($suspension->status == 'Approved')
                                                <span class="badge text-bg-success">{{ $suspension->status }}</span>
                                            @elseif($suspension->status == 'Pending')
                                                <span class="badge text-bg-warning">{{ $suspension->status }}</span>
                                            @else
                                                <span class="badge text-bg-danger">{{ $suspension->status }}</span>
                                            @endif
                                        </td>
                                        @if (Gate::check('edit membership suspension') ||
                                                Gate::check('delete membership suspension') ||
                                                Gate::check('show membership suspension'))
                                            <td>
                                                {!! Form::open(['url' => 'membership-suspension/' . $suspension->suspension_id, 'method' => 'DELETE']) !!}
                                                @if (Gate::check('show membership suspension'))
                                                    <a href="#" class="avtar avtar-xs btn-link-warning text-warning customModal" data-size="lg"
                                                        data-url="{{ route('membership-suspension.show', $suspension->id) }}"
                                                        data-title="{{ __('Membership Suspension Details') }}">
                                                        <i data-feather="eye"></i>
                                                    </a>
                                                @endif

                                                @if (Gate::check('edit membership suspension'))
                                                    <a href="#" class="avtar avtar-xs btn-link-secondary text-secondary customModal" data-size="lg"
                                                        data-url="{{ route('membership-suspension.edit', $suspension->id) }}"
                                                        data-title="{{ __('Edit Membership Suspension') }}">
                                                        <i data-feather="edit"></i>
                                                    </a>
                                                @endif

                                                @if (Gate::check('delete membership suspension'))
                                                    <a type="submit" class="avtar avtar-xs btn-link-danger text-danger confirm_dialog" href="#"><i
                                                            data-feather="trash-2"></i></a>
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
