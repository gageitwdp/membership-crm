{!! Form::model($event, [
    'route' => ['event.update', $event->id],
    'method' => 'PUT',
    'enctype' => 'multipart/form-data',
]) !!}

<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-6">
            {{ Form::label('event_name', __('Name'), ['class' => 'form-label']) }}
            {{ Form::text('event_name', null, ['class' => 'form-control', 'placeholder' => __('Enter event name')]) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('date_time', __('Date & Time'), ['class' => 'form-label']) }}
            <input type="datetime-local" name="date_time" value="{{ $event->date_time }}"
                class="form-control datetimepicker">
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('location', __('Location'), ['class' => 'form-label']) }}
            {{ Form::text('location', null, ['class' => 'form-control', 'placeholder' => __('Enter event location')]) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('max_participant', __('Max. Participant'), ['class' => 'form-label']) }}
            {{ Form::number('max_participant', null, ['class' => 'form-control', 'placeholder' => __('Enter max. participant')]) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('registration_deadline', __('Registration Deadline'), ['class' => 'form-label']) }}
            {{ Form::date('registration_deadline', null, ['class' => 'form-control']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('availability_status', __('Availability Status'), ['class' => 'form-label']) }}
            {{ Form::select('availability_status', ['Open' => __('Open'), 'Full' => __('Full'), 'Cancelled' => __('Cancelled')], null, ['class' => 'form-control select2']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('description', __('Description'), ['class' => 'form-label']) }}
            {{ Form::textarea('description', null, ['class' => 'form-control', 'placeholder' => __('Enter event description'), 'rows' => '1']) }}
        </div>
    </div>
</div>

<div class="modal-footer">

    {!! Form::submit(__('Update'), ['class' => 'btn  btn-secondary']) !!}
</div>
{!! Form::close() !!}
