@extends('layouts.app')
@section('page-title')
    {{ __('Calendar') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('dashboard') }}">
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item active">
        <a href="#">
            {{ __('Calendar') }}
        </a>
    </li>
@endsection
@push('css-page')
@endpush
@push('script-page')
    <script>
        var eventData = {!! json_encode($eventData) !!};
    </script>
    <script src="{{ asset('assets/js/plugins/index.global.min.js') }}"></script>
    <script src="{{ asset('assets/js/pages/calendar.js') }}"></script>
@endpush
@section('page-class')
    codex-calendar
@endsection
@section('card-action-btn')
    @if (Gate::check('create appointment'))
        <a class="btn btn-secondary btn-sm ml-20 customModal" href="#" data-size="lg"
            data-url="{{ route('appointment.create') }}" data-title="{{ __('Create Appointment') }}"> <i
                class="ti-plus mr-5"></i>
            {{ __('Create Appointment') }}
        </a>
    @endif
@endsection
@section('content')
    <div class="row">
        <!-- [ sample-page ] start -->
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div id="calendar" class="calendar"></div>
                </div>
            </div>
        </div>
        <!-- [ sample-page ] end -->
    </div>
    <div class="modal fade" id="calendar-modal" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header justify-content-between align-items-center">
                    <h3 class="calendar-modal-title f-w-600 text-truncate">Modal title</h3>
                    <a href="#" class="avtar avtar-s btn-link-danger btn-pc-default" data-bs-dismiss="modal">
                        <i class="ti ti-x f-20"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <div class="avtar avtar-xs bg-light-secondary">
                                <i class="ti ti-heading f-20"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-1"><b>Title</b></h5>
                            <p class="pc-event-title text-muted"></p>
                        </div>
                    </div>
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <div class="avtar avtar-xs bg-light-danger">
                                <i class="ti ti-calendar-event f-20"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-1"><b>Start Date</b></h5>
                            <p class="pc-event-date text-muted"></p>
                        </div>
                    </div>
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <div class="avtar avtar-xs bg-light-warning">
                                <i class="ti ti-calendar-event f-20"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-1"><b>End Date</b></h5>
                            <p class="pc-event-end text-muted"></p>
                        </div>
                    </div>

                </div>
                <div class="modal-footer justify-content-between">
                    <div class="flex-grow-1 text-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
