@extends('layouts.app')

@section('page-title')
    {{ __('Event') }}
@endsection
@section('breadcrumb')
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}">
                {{ __('Dashboard') }}
            </a>
        </li>
        <li class="breadcrumb-item active">
            <a href="#">{{ __('Event') }}</a>
        </li>
    </ul>
@endsection

@section('card-action-btn')
    @if (Gate::check('create event'))
        <a class="btn btn-secondary btn-sm customModal" href="#" data-size="lg" data-url="{{ route('event.create') }}"
            data-title="{{ __('Create New Event') }}"> <i class="ti-plus mr-5"></i>{{ __('Create Event') }}
        </a>
    @endif
@endSection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card table-card">
                <div class="card-header">
                    <div class="row align-items-center g-2">
                        <div class="col">
                            <h5>
                                {{ __('Events') }}
                            </h5>
                        </div>
                        @if (Gate::check('create event'))
                            <div class="col-auto">
                                <a href="#" class="btn btn-secondary customModal" data-size="lg"
                                    data-url="{{ route('event.create') }}" data-title="{{ __('Create Event') }} ">
                                    <i class="ti ti-circle-plus align-text-bottom"></i>
                                    {{ __('Create Event') }}
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
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('location') }}</th>
                                    <th>{{ __('Participant') }}</th>
                                    <th>{{ __('Deadline') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    @if (Gate::check('edit event') || Gate::check('delete event') || Gate::check('show event'))
                                        <th>{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($events as $event)
                                    <tr>
                                        <td>{{ eventPrefix() . $event->event_id }}</td>
                                        <td>{{ $event->event_name }}</td>
                                        <td>{{ dateFormat($event->date_time) }} - {{ timeFormat($event->date_time) }}</td>
                                        <td>{{ $event->location }}</td>
                                        <td>{{ $event->max_participant }}</td>
                                        <td>{{ dateFormat($event->registration_deadline) }}</td>
                                        <td>
                                            @if ($event->availability_status == 'Open')
                                                <span class="badge text-bg-success">{{ $event->availability_status }}</span>
                                            @elseif($event->availability_status == 'Cancelled')
                                                <span class="badge text-bg-danger">{{ $event->availability_status }}</span>
                                            @else
                                                <span class="badge text-bg-warning">{{ $event->availability_status }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if (Gate::check('edit event') || Gate::check('delete event') || Gate::check('show event'))
                                                {!! Form::open(['route' => ['event.destroy', $event->id], 'method' => 'DELETE']) !!}
                                                @if (Gate::check('show event'))
                                                    <a href="#" data-size="lg"
                                                        data-url="{{ route('event.show', $event->id) }}"
                                                        data-title="{{ __('View Event') }}"
                                                        class="avtar avtar-xs btn-link-warning text-warning customModal"><i data-feather="eye"></i></a>
                                                @endif
                                                @if (Gate::check('edit event'))
                                                    <a href="#" data-size="lg"
                                                        data-url="{{ route('event.edit', $event->id) }}"
                                                        data-title="{{ __('Edit Event') }}"
                                                        class="avtar avtar-xs btn-link-secondary text-secondary customModal"><i data-feather="edit"></i></a>
                                                @endif
                                                @if (Gate::check('delete event'))
                                                    <a href="#" data-size="lg" data-ajax-popup="true"
                                                        data-title="{{ __('Delete Event') }}"
                                                        class="avtar avtar-xs btn-link-danger text-danger confirm_dialog"><i data-feather="trash-2"></i>
                                                    </a>
                                                @endif
                                                {!! Form::close() !!}
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
@endSection
