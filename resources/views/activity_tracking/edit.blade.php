{!! Form::model($activityTracking, ['route' => ['activity-tracking.update', $activityTracking->id], 'method' => 'PUT']) !!}

<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-6">
            {{ Form::label('member_id', __('Member'),['class'=>'form-label']) }}
            {{ Form::select('member_id', $members, null, ['class' => 'form-control basic-select','required'=>'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('event_id', __('Event'),['class'=>'form-label']) }}
            {{ Form::select('event_id', $events, null, ['class' => 'form-control basic-select','required'=>'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('check_in', __('Check In'), ['class' => 'form-label']) }}
            <input type="datetime-local" name="check_in" value="{{ $activityTracking->check_in }}" id="check_in" class="form-control datetimepicker">
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('check_out', __('Check Out'), ['class' => 'form-label']) }}
            <input type="datetime-local" name="check_out" value="{{ $activityTracking->check_out }}" id="check_out" class="form-control datetimepicker">
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('duration', __('Duration'), ['class' => 'form-label']) }}
            {{ Form::text('duration', null, ['class' => 'form-control', 'placeholder' => __('Enter duration'), 'id' => 'duration', 'readonly']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('notes', __('Notes'),['class'=>'form-label']) }}
            {{ Form::textarea('notes', null, ['class' => 'form-control', 'placeholder' => __('Enter note'), 'rows' => '1']) }}
        </div>
    </div>
</div>

<div class="modal-footer">
    {{ Form::submit(__('Update'), ['class' => 'btn btn-secondary']) }}
</div>
{!! Form::close() !!}
