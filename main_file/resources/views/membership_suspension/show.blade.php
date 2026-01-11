<div class="modal-body">
    <div class="row">
        <div class="col-md-4 col-lg-4">
            <div class="detail-group">
                <b>{{ __('ID') }}</b>
                <p class="mb-20">{{ suspensionPrefix() . $membershipSuspension->suspension_id }} </p>
            </div>
        </div>
        <div class="col-md-4 col-lg-4">
            <div class="detail-group">
                <b>{{ __('Member') }}</b>
                <p class="mb-20">{{ $membershipSuspension->members->first_name }} </p>
            </div>
        </div>
        <div class="col-md-4 col-lg-4">
            <div class="detail-group">
                <b>{{ __('Start Date') }}</b>
                <p class="mb-20">{{ dateFormat($membershipSuspension->start_date) }} </p>
            </div>
        </div>
        <div class="col-md-4 col-lg-4">
            <div class="detail-group">
                <b>{{ __('End Date') }}</b>
                <p class="mb-20">{{ dateFormat($membershipSuspension->end_date) }} </p>
            </div>
        </div>
        <div class="col-md-4 col-lg-4">
            <div class="detail-group">
                <b>{{ __('Status') }}</b>
                <p class="mb-20">{{ $membershipSuspension->status }} </p>
            </div>
        </div>
        <div class="col-md-4 col-lg-4">
            <div class="detail-group">
                <b>{{ __('Reason') }}</b>
                <p class="mb-20">{{ $membershipSuspension->reason }} </p>
            </div>
        </div>
    </div>
</div>
