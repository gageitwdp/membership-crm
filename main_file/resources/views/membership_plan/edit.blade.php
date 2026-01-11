{{ Form::model($membershipPlan, ['route' => ['membership-plan.update', $membershipPlan->id], 'method' => 'PUT', 'enctype' => 'multipart/form-data']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-6">
            {{ Form::label('plan_name', __('plan Name'), ['class' => 'form-label']) }}
            {{ Form::text('plan_name', null, ['class' => 'form-control', 'placeholder' => __('Enter plan name'), 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('price', __('price'), ['class' => 'form-label']) }}
            {{ Form::number('price', null, ['class' => 'form-control', 'placeholder' => __('Enter price'), 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('duration', __('Duration'), ['class' => 'form-label']) }}
                {{ Form::select('duration', ['' => 'Select Frequency', 'Monthly' => 'Monthly', '3-Month' => '3-Month', '6-Month' => '6-Month', 'Yearly' => 'Yearly'], null, ['class' => 'form-control']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('billing_frequency', __('billing frequency'), ['class' => 'form-label']) }}
                {{ Form::select('billing_frequency', ['' => 'Select Frequency', 'Monthly' => 'Monthly', '3-Month' => '3-Month', '6-Month' => '6-Month', 'Yearly' => 'Yearly'], null, ['class' => 'form-control']) }}
        </div>
        {{-- <div class="form-group col-md-6">
            {{ Form::label('benefits', __('Benefits'), ['class' => 'form-label']) }}
            {{ Form::text('benefits', null, ['class' => 'form-control', 'placeholder' => __('Enter benefits'), 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('access_level', __('Access Level'), ['class' => 'form-label']) }}
            {{ Form::text('access_level', null, ['class' => 'form-control', 'placeholder' => __('Enter access level'), 'required' => 'required']) }}
        </div> --}}
        <div class="form-group col-md-6">
            {{ Form::label('plan_description', __('plan description'), ['class' => 'form-label']) }}
            {{ Form::textarea('plan_description', null, ['class' => 'form-control', 'placeholder' => __('Enter note'), 'rows' => '1']) }}
        </div>
    </div>
    <div class="modal-footer">
        {{ Form::submit(__('Update'), ['class' => 'btn btn-secondary']) }}
    </div>
</div>
{{ Form::close() }}
