@extends('layouts.app')

@section('page-title')
    {{ __('Membership') }}
@endsection

@section('breadcrumb')
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}">
                {{ __('Dashboard') }}
            </a>
        </li>
        <li class="breadcrumb-item active">
            <a href="#">{{ __('Membership') }}</a>
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
                                {{ __('Membership') }}
                            </h5>
                        </div>
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
                                    @if (Gate::check('edit membership') || Gate::check('delete membership') || Gate::check('show membership'))
                                        <th>{{ __('Action') }}</th>
                                    @endif
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
                                        {{-- <td>
                                            @php
                                                $expiry = \Carbon\Carbon::parse($membership->expiry_date);
                                                $today = \Carbon\Carbon::today();
                                            @endphp

                                            @if ($expiry->lt($today))
                                                <span class="badge text-bg-danger">{{ __('Expired') }}</span>
                                            @else
                                                <span class="badge text-bg-success">{{ __('Active') }}</span>
                                            @endif
                                        </td> --}}

                                        <td>
                                            @if ($membership->status == "Expired")
                                                <span class="badge text-bg-danger">{{ __('Expired') }}</span>
                                            @else
                                                <span class="badge text-bg-success">{{ __('Active') }}</span>
                                            @endif
                                        </td>
                                        @if (Gate::check('edit membership') || Gate::check('delete membership') || Gate::check('show membership'))
                                            <td>
                                                {!! Form::open(['route' => ['membership.destroy', $membership->id], 'method' => 'DELETE']) !!}
                                                @if (Gate::check('show membership'))
                                                    <a href="#"
                                                        class="avtar avtar-xs btn-link-warning text-warning customModal"
                                                        data-size="lg"
                                                        data-url="{{ route('membership.show', $membership->id) }}"
                                                        data-title="{{ __('View Membership') }}">
                                                        <i data-feather="eye"></i>
                                                    </a>
                                                @endif
                                                @if (Gate::check('delete membership'))
                                                    <a href="#"
                                                        class="avtar avtar-xs btn-link-danger text-danger confirm_dialog"
                                                        data-title="{{ __('Delete Membership') }}">
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
