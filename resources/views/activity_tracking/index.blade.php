@extends('layouts.app')

@section('page-title')
    {{ __('Activity Tracking') }}
@endsection
@section('breadcrumb')
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}">
                {{ __('Dashboard') }}
            </a>
        </li>
        <li class="breadcrumb-item active">
            <a href="#">{{ __('Activity Tracking') }}</a>
        </li>
    </ul>
@endsection
@section('card-action-btn')
    <a class="btn btn-secondary btn-sm customModal" href="#" data-size="lg"
        data-url="{{ route('activity-tracking.create') }}" data-title="{{ __('Create New Activity Tracking') }}"> <i
            class="ti-plus mr-5"></i>{{ __('Create Activity Tracking') }}</a>
@endsection
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card table-card">
                <div class="card-header">
                    <div class="row align-items-center g-2">
                        <div class="col">
                            <h5>
                                {{ __('Activity Tracking') }}
                            </h5>
                        </div>
                        <div class="col-auto">
                            @if (Gate::check('create activity tracking'))
                                <a href="#" class="btn btn-secondary customModal" data-size="lg"
                                    data-url="{{ route('activity-tracking.create') }}"
                                    data-title="{{ __('Create New Activity Tracking') }} ">
                                    <i class="ti ti-circle-plus align-text-bottom"></i>
                                    {{ __('Create Activity Tracking') }}
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="dt-responsive table-responsive">
                        <table class="table table-hover advance-datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Member') }}</th>
                                    <th>{{ __('Event') }}</th>
                                    <th>{{ __('Check in') }}</th>
                                    <th>{{ __('Check out') }}</th>
                                    <th>{{ __('Duration') }}</th>
                                    @if (Gate::check('edit activity tracking') ||
                                            Gate::check('delete activity tracking') ||
                                            Gate::check('show activity tracking'))
                                        <th>{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($activityTrackings as $activity)
                                    <tr>
                                        <td>{{ !empty($activity->members) ? $activity->members->first_name : '-' }}</td>
                                        <td>{{ !empty($activity->events) ? $activity->events->event_name : '-' }}</td>
                                        <td>{{ dateFormat($activity->check_in) }} {{ timeFormat($activity->check_in) }}
                                        </td>
                                        <td>{{ dateFormat($activity->check_out) }} {{ timeFormat($activity->check_out) }}
                                        </td>
                                        <td>{{ $activity->duration }}</td>
                                        @if (Gate::check('edit activity tracking') ||
                                                Gate::check('delete activity tracking') ||
                                                Gate::check('show activity tracking'))
                                            <td>
                                                {!! Form::open(['route' => ['activity-tracking.destroy', $activity->id], 'method' => 'DELETE']) !!}
                                                @if (Gate::check('show activity tracking'))
                                                    <a href="#"
                                                        data-url="{{ route('activity-tracking.show', $activity->id) }}"
                                                        data-title="{{ __('View Activity Tracking') }}" data-size="lg"
                                                        class="avtar avtar-xs btn-link-warning text-warning customModal"><i
                                                            data-feather="eye"></i></a>
                                                @endif
                                                @if (Gate::check('edit activity tracking'))
                                                    <a href="#"
                                                        data-url="{{ route('activity-tracking.edit', $activity->id) }}"
                                                        data-title="{{ __('Edit Activity Tracking') }}" data-size="lg"
                                                        class="avtar avtar-xs btn-link-secondary text-secondary customModal"><i
                                                            data-feather="edit"></i></a>
                                                @endif
                                                @if (Gate::check('delete activity tracking'))
                                                    <a href="#"
                                                        class="avtar avtar-xs btn-link-danger text-danger confirm_dialog"><i
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


@push('script-page')
    <script>
        $(document).ready(function() {
            $(document).on('change', '#check_in, #check_out', function() {
                var checkIn = $('#check_in').val();
                var checkOut = $('#check_out').val();


                if (checkIn && checkOut) {
                    var start = new Date(checkIn);
                    var end = new Date(checkOut);

                    if (end > start) {
                        var diff = end - start;

                        var hours = Math.floor(diff / 1000 / 60 / 60);
                        var minutes = Math.floor((diff / 1000 / 60) % 60);

                        var duration = hours + ' hours ' + minutes + ' minutes';

                        $('#duration').val(duration);
                    } else {
                        $('#duration').val('');
                    }
                } else {
                    $('#duration').val('');
                }

            });
        });
    </script>
@endpush
