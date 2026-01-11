<div class="modal-body">
    <div class="row">
        <div class="col-md-6 col-lg-6">
            <div class="detail-group">
                <b>{{ __('Member') }}</b>
                <p class="mb-20">{{ !empty($membership->members) ? $membership->members->first_name : '-' }}</p>
            </div>
        </div>
        <div class="col-md-6 col-lg-6">
            <div class="detail-group">
                <b>{{ __('Plan') }}</b>
                <p class="mb-20">{{ !empty($membership->plans) ? $membership->plans->plan_name : '-' }} </p>
            </div>
        </div>

        <div class="col-md-6 col-lg-6">
            <div class="detail-group">
                <b>{{ __('Start Date') }}</b>
                <p class="mb-20">{{ dateFormat($membership->start_date) }} </p>
            </div>
        </div>

        <div class="col-md-6 col-lg-6">
            <div class="detail-group">
                <b>{{ __('Expiry Date') }}</b>
                <p class="mb-20">{{ dateFormat($membership->expiry_date) }} </p>
            </div>
        </div>

        <div class="col-md-6 col-lg-6">
            <div class="detail-group">
                <b>{{ __('Status') }}</b>
                <p class="mb-20">
                    @if ($membership->status == 'Active')
                        <span class="badge text-bg-success">{{ __('Active') }}</span>
                    @else
                        <span class="badge text-bg-danger">{{ __('Expired') }}</span>
                    @endif
                </p>
            </div>


        </div>

    </div>
</div>
{{-- @if ($membership->status == 'Expired' && optional($membership->latestPayment)->status == 'Unpaid') --}}
{{-- @if ($membership->status == 'Expired')
    {{ Form::open(['route' => ['membership.renew', $membership->id], 'method' => 'POST']) }}
    <div class="modal-footer">
        {{ Form::submit(__('Renew'), ['class' => 'btn btn-info']) }}
    </div>
    {{ Form::close() }}
@endif --}}
