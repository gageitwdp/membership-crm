<div class="modal-body">
    <div class="row">
        <div class="col-md-6 col-lg-6">
            <div class="detail-group">
                <b>{{ __('ID') }}</b>
                <p class="mb-20">{{ eventPrefix() . $event->event_id }} </p>
            </div>
        </div>
        <div class="col-md-6 col-lg-6">
            <div class="detail-group">
                <b>{{ __('Event Name') }}</b>
                <p class="mb-20">{{ $event->event_name }} </p>
            </div>
        </div>
        <div class="col-md-6 col-lg-6">
            <div class="detail-group">
                <b>{{ __('Date & Time') }}</b>
                <p class="mb-20">{{ dateFormat($event->date_time) }} {{ timeFormat($event->date_time) }}</p>
            </div>
        </div>
        <div class="col-md-6 col-lg-6">
            <div class="detail-group">
                <b>{{ __('Event Location') }}</b>
                <p class="mb-20">{{ $event->location }} </p>
            </div>
        </div>
        <div class="col-md-6 col-lg-6">
            <div class="detail-group">
                <b>{{ __('Max. Participant') }}</b>
                <p class="mb-20">{{ $event->max_participant }} </p>
            </div>
        </div>
        <div class="col-md-6 col-lg-6">
            <div class="detail-group">
                <b>{{ __('Registration Deadline') }}</b>
                <p class="mb-20">{{ $event->registration_deadline }} </p>
            </div>
        </div>
        <div class="col-md-6 col-lg-6">
            <div class="detail-group">
                <b>{{ __('Availability Status') }}</b>
                <p class="mb-20">
                    @if ($event->availability_status == 'Open')
                        <span class="badge text-bg-success">{{ $event->availability_status }}</span>
                    @elseif($event->availability_status == 'Cancelled')
                        <span class="badge text-bg-danger">{{ $event->availability_status }}</span>
                    @else
                        <span class="badge text-bg-warning">{{ $event->availability_status }}</span>
                    @endif
                </p>
            </div>
        </div>
        <div class="col-md-6 col-lg-6">
            <div class="detail-group">
                <b>{{ __('Description') }}</b>
                <p class="mb-20">{{ $event->description }} </p>
            </div>
        </div>
    </div>
</div>
