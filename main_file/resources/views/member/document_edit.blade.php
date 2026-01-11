{{ Form::model($document, ['route' => ['member.document.update', $document->id], 'method' => 'POST', 'enctype' => 'multipart/form-data']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-12">
            {{ Form::label('document_name', __('document name'), ['class' => 'form-label']) }}
            {{ Form::text('document_name', null, ['class' => 'form-control', 'placeholder' => __('Enter name'), 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-12">
            {{ Form::label('document_type', __('document type'), ['class' => 'form-label']) }}
            {{ Form::select('document_type', $types, null, ['class' => 'form-control hidesearch basic-select']) }}
        </div>
        <div class="form-group col-md-12">
            {{ Form::label('document', __('document'), ['class' => 'form-label']) }}
            {{ Form::file('document', ['class' => 'form-control']) }}
        </div>
        <div class="form-group col-md-12">
            {{ Form::label('upload_date', __('upload date'), ['class' => 'form-label']) }}
            {{ Form::date('upload_date', null, ['class' => 'form-control']) }}
        </div>
    </div>
    <div class="modal-footer">
        {{ Form::submit(__('Update'), ['class' => 'btn btn-secondary']) }}
    </div>
    {{ Form::close() }}
</div>
