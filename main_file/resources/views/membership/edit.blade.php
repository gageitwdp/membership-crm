{!! Form::model($membership, ['route' => ['membership.update', $membership->id], 'method' => 'PUT', 'enctype' => 'multipart/form-data']) !!}


<div class="modal-body">
    <div class="row">

        <div class="form-group col-md-6">
            {{Form::label('member_id',__('Member'),array('class'=>'form-label')) }}
            {{Form::select('member_id', $members, null, ['class' => 'form-control basic-select','required'=>'required'])}}
        </div>
        <div class="form-group col-md-6">
            {!! Form::label('plan_id', __('Plan'), ['class' => 'form-label']) !!}
            {!! Form::select('plan_id', $plans, null, ['class' => 'form-control basic-select', 'required' => 'required']) !!}
        </div>
        <div class="form-group col-md-6">
            {{Form::label('start_date',__('Start Date'),array('class'=>'form-label')) }}
            {{Form::date('start_date',null,array('class'=>'form-control','required'=>'required'))}}
        </div>
        <div class="form-group col-md-6">
            {{Form::label('expiry_date',__('Expiry Date'),array('class'=>'form-label')) }}
            {{Form::date('expiry_date',null,array('class'=>'form-control','required'=>'required'))}}
        </div>
        <div class="form-group col-md-6">
            {{Form::label('status',__('Status'),array('class'=>'form-label')) }}
            {{Form::select('status', ['Active' => __('Active'), 'Expired' => __('Expired')], null, ['class' => 'form-control basic-select', 'required' => 'required']) }}
        </div>
    </div>
</div>
<div class="modal-footer">

    {{Form::submit(__('Update'),array('class'=>'btn btn-secondary ml-10'))}}
</div>
{{ Form::close() }}

