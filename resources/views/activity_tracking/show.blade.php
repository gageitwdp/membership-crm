<div class="modal-body">
    <div class="row">
        <div class="col-md-6 col-lg-6">
            <div class="detail-group">
                <b>{{ __('Activity Name') }}</b>
                <p class="mb-20">{{ (!empty($activityTracking->events) ? $activityTracking->events->event_name : '-') }} </p>
            </div>
        </div>
        <div class="col-md-6 col-lg-6">
            <div class="detail-group">
                <b>{{ __('Date & Time') }}</b>
                <p class="mb-20">{{ dateFormat($activityTracking->date_time) }}
                    {{ timeFormat($activityTracking->date_time) }}</p>
            </div>
        </div>
        <div class="col-md-6 col-lg-6">
            <div class="detail-group">
                <b>{{ __('Activity Location') }}</b>
                <p class="mb-20">{{ $activityTracking->events->location }} </p>
            </div>
        </div>
        <div class="col-md-6 col-lg-6">
            <div class="detail-group">
                <b>{{ __('Check In') }}</b>
                <p class="mb-20">{{ dateFormat($activityTracking->check_in) }}
                    {{ timeFormat($activityTracking->check_in) }} </p>
            </div>
        </div>
        <div class="col-md-6 col-lg-6">
            <div class="detail-group">
                <b>{{ __('Check Out') }}</b>
                <p class="mb-20">{{ dateFormat($activityTracking->check_out) }}
                    {{ timeFormat($activityTracking->check_out) }} </p>
            </div>
        </div>
        <div class="col-md-6 col-lg-6">
            <div class="detail-group">
                <b>{{ __('Duration') }}</b>
                <p class="mb-20">{{ $activityTracking->duration }} </p>
            </div>
        </div>

        <div class="col-md-6 col-lg-6">
            <div class="detail-group">
                <b>{{ __('Description') }}</b>
                <p class="mb-20">{{ !empty($activityTracking->notes) ? $activityTracking->notes : __('N/A') }} </p>
            </div>

        </div>

    </div>
</div>
