{{ Form::model($DocumentType, array('route' => array('document-type.update', $DocumentType->id), 'method' => 'PUT')) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-12">
            {{Form::label('type',__('Type'),array('class'=>'form-label')) }}
            {{Form::text('type',null,array('class'=>'form-control','placeholder'=>__('Enter type'),'required'=>'required'))}}
        </div>
    </div>
</div>
<div class="modal-footer">

    {{Form::submit(__('Update'),array('class'=>'btn btn-secondary ml-10'))}}
</div>
{{Form::close()}}


