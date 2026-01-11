{!! Form::open(['url' => 'expense', 'method' => 'post', 'enctype' => 'multipart/form-data']) !!}
    <div class="modal-body">
        <div class="row">
            <div class="form-group col-md-6">
                {{Form::label('title',__('Title'),array('class'=>'form-label'))}}
                {{Form::text('title',null,array('class'=>'form-control','placeholder'=>__('Enter title'),'required'=>'required'))}}
            </div>
            <div class="form-group col-md-6">
                {{Form::label('type',__('Type'),array('class'=>'form-label'))}}
                {{Form::select('type', $types, null, ['class' => 'form-control basic-select','required'=>'required'])}}
            </div>
            <div class="form-group col-md-6">
                {{Form::label('date',__('Date'),array('class'=>'form-label'))}}
                {{Form::date('date',null,array('class'=>'form-control','required'=>'required'))}}
            </div>
            <div class="form-group col-md-6">
                {{Form::label('amount',__('Amount'),array('class'=>'form-label'))}}
                {{Form::number('amount',null,array('class'=>'form-control','required'=>'required'))}}
            </div>
            <div class="form-group col-md-6">
                {{Form::label('receipt',__('Receipt'),array('class'=>'form-label'))}}
                {{Form::file('receipt',array('class'=>'form-control'))}}
            </div>
            <div class="form-group col-md-6">
                {{Form::label('notes',__('Note'),array('class'=>'form-label'))}}
                {{Form::textarea('notes',null,array('class'=>'form-control','rows'=>2))}}
            </div>
        </div>
    </div>
    <div class="modal-footer">
        {{Form::submit(__('Create'),array('class'=>'btn btn-secondary btn-rounded'))}}
    </div>

{{ Form::close() }}
