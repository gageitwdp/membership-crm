{!! Form::open(['url' => 'membership-suspension', 'method' => 'post']) !!}

    <div class="modal-body">
        <div class="row">
            <div class="form-group col-md-6">
                {{ Form::label('member_id', __('Member'), ['class' => 'form-label']) }}
                {{ Form::select('member_id', $member, null, ['class' => 'form-control basic-select', 'required' => 'required']) }}
            </div>
            <div class="form-group col-md-6">
                {{ Form::label('start_date', __('Start Date'), ['class' => 'form-label']) }}
                {{ Form::date('start_date', null, ['class' => 'form-control', 'required' => 'required']) }}
            </div>
            <div class="form-group col-md-6">
                {{ Form::label('end_date', __('End Date'), ['class' => 'form-label']) }}
                {{ Form::date('end_date', null, ['class' => 'form-control', 'required' => 'required']) }}
            </div>
            <div class="form-group col-md-6">
                {{ Form::label('status', __('Status'), ['class' => 'form-label']) }}
                {{ Form::select('status', ['Approved' => __('Approved'), 'Pending' => __('Pending'), 'Rejected' => __('Rejected')], null, ['class' => 'form-control basic-select', 'required' => 'required']) }}
            </div>
            <div class="form-group col-md-6">
                {{ Form::label('reason', __('Reason'), ['class' => 'form-label']) }}
                {{ Form::textarea('reason', null, ['class' => 'form-control', 'required' => 'required', 'rows' => 2]) }}
            </div>
        </div>
    </div>

    <div class="modal-footer">
        {{ Form::submit(__('Create'), ['class' => 'btn btn-secondary']) }}
    </div>
{{ Form::close() }}
