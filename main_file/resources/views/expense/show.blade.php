<div class="modal-body">
    <div class="row">
        <div class="col-md-6 col-lg-6">
            <div class="detail-group">
                <b>{{ __('Title') }}</b>
                <p class="mb-20">{{ $expense->title }} </p>
            </div>
        </div>
        <div class="col-md-6 col-lg-6">
            <div class="detail-group">
                <b>{{ __('Type') }}</b>
                <p class="mb-20">{{ !empty($expense->expenseType)?$expense->expenseType->type:'-' }} </p>
            </div>
        </div>
        <div class="col-md-6 col-lg-6">
            <div class="detail-group">
                <b>{{ __('Date') }}</b>
                <p class="mb-20">{{ dateFormat($expense->date) }} </p>
            </div>
        </div>
        <div class="col-md-6 col-lg-6">
            <div class="detail-group">
                <b>{{ __('Amount') }}</b>
                <p class="mb-20">{{ priceFormat($expense->amount) }} </p>
            </div>
        </div>
        <div class="col-md-6 col-lg-6">
            <div class="detail-group">
                <b>{{ __('Receipt') }}</b>
                <p class="mb-20">
                    @if(!empty($expense->receipt))
                        <a href="{{asset(Storage::url('upload/receipt')).'/'.$expense->receipt}}" download="download"><i class="ti-download"></i></a>
                    @else
                        -
                    @endif
                </p>
            </div>
        </div>
        <div class="col-md-6 col-lg-6">
            <div class="detail-group">
                <b>{{ __('Notes') }}</b>
                <p class="mb-20">{{ !empty($expense->notes) ? $expense->notes : '-' }} </p>
            </div>
        </div>
    </div>
</div>
