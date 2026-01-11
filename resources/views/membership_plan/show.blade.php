<div class="modal-body">
    <div class="row">
        <div class="col-md-4 col-lg-4">
            <div class="detail-group">
                <b>{{ __('ID') }}</b>
                <p class="mb-20">{{ planPrefix() .$membershipPlan->plan_id }} </p>
            </div>
        </div>
        <div class="col-md-4 col-lg-4">
            <div class="detail-group">
                <b>{{ __('Name') }}</b>
                <p class="mb-20">{{ $membershipPlan->plan_name }} </p>
            </div>
        </div>
        <div class="col-md-4 col-lg-4">
            <div class="detail-group">
                <b>{{ __('Price') }}</b>
                <p class="mb-20">{{ priceFormat($membershipPlan->price) }} </p>
            </div>
        </div>
        <div class="col-md-4 col-lg-4">
            <div class="detail-group">
                <b>{{ __('Duration') }}</b>
                <p class="mb-20">{{ $membershipPlan->duration }} </p>
            </div>
        </div>
        <div class="col-md-4 col-lg-4">
            <div class="detail-group">
                <b>{{ __('Plan Description') }}</b>
                <p class="mb-20">{{ $membershipPlan->plan_description }} </p>
            </div>
        </div>
        <div class="col-md-4 col-lg-4">
            <div class="detail-group">
                <b>{{ __('Billing Frequency') }}</b>
                <p class="mb-20">{{ $membershipPlan->billing_frequency }} </p>
            </div>
        </div>
        {{-- <div class="col-md-4 col-lg-4">
            <div class="detail-group">
                <b>{{ __('Benefits') }}</b>
                <p class="mb-20">{{ $membershipPlan->benefits }} </p>
            </div>
        </div>
        <div class="col-md-4 col-lg-4">
            <div class="detail-group">
                <b>{{ __('Access Level') }}</b>
                <p class="mb-20">{{ $membershipPlan->access_level }} </p>
            </div>
        </div> --}}
    </div>
</div>
