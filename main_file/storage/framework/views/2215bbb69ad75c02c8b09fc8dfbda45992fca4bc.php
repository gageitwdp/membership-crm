<?php echo e(Form::model($DocumentType, array('route' => array('document-type.update', $DocumentType->id), 'method' => 'PUT'))); ?>

<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-12">
            <?php echo e(Form::label('type',__('Type'),array('class'=>'form-label'))); ?>

            <?php echo e(Form::text('type',null,array('class'=>'form-control','placeholder'=>__('Enter type'),'required'=>'required'))); ?>

        </div>
    </div>
</div>
<div class="modal-footer">

    <?php echo e(Form::submit(__('Update'),array('class'=>'btn btn-secondary ml-10'))); ?>

</div>
<?php echo e(Form::close()); ?>



<?php /**PATH /home/u417948420/domains/members-triumphwest.triumphtrained.com/public_html/main_file/resources/views/document_type/edit.blade.php ENDPATH**/ ?>