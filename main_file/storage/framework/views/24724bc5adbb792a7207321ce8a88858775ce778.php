<?php echo e(Form::open(['url' => 'event', 'method' => 'post'])); ?>

<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-6">
            <?php echo e(Form::label('event_name', __('Name'), ['class' => 'form-label'])); ?>

            <?php echo e(Form::text('event_name', null, ['class' => 'form-control', 'placeholder' => __('Enter event name')])); ?>

        </div>
        <div class="form-group col-md-6">
            <?php echo e(Form::label('date_time', __('Date & Time'), ['class' => 'form-label'])); ?>

            <input type="datetime-local" name="date_time" class="form-control datetimepicker">
        </div>
        <div class="form-group col-md-6">
            <?php echo e(Form::label('location', __('Location'), ['class' => 'form-label'])); ?>

            <?php echo e(Form::text('location', null, ['class' => 'form-control', 'placeholder' => __('Enter event location')])); ?>

        </div>
        <div class="form-group col-md-6">
            <?php echo e(Form::label('max_participant', __('Max. Participant'), ['class' => 'form-label'])); ?>

            <?php echo e(Form::number('max_participant', null, ['class' => 'form-control', 'placeholder' => __('Enter max. participant')])); ?>

        </div>
        <div class="form-group col-md-6">
            <?php echo e(Form::label('registration_deadline', __('Registration Deadline'), ['class' => 'form-label'])); ?>

            <?php echo e(Form::date('registration_deadline', null, ['class' => 'form-control'])); ?>

        </div>
        <div class="form-group col-md-6">
            <?php echo e(Form::label('availability_status', __('Availability Status'), ['class' => 'form-label'])); ?>

            <?php echo e(Form::select('availability_status', ['Open' => __('Open'), 'Full' => __('Full'), 'Cancelled' => __('Cancelled')], null, ['class' => 'form-control select2'])); ?>

        </div>
        <div class="form-group col-md-6">
            <?php echo e(Form::label('description', __('Description'), ['class' => 'form-label'])); ?>

            <?php echo e(Form::textarea('description', null, ['class' => 'form-control', 'placeholder' => __('Enter event description'), 'rows' => '1'])); ?>

        </div>
    </div>
</div>

<div class="modal-footer">

    <?php echo Form::submit(__('Create'), ['class' => 'btn  btn-secondary']); ?>

</div>
<?php echo Form::close(); ?>

<?php /**PATH /home/u417948420/domains/members-triumphwest.triumphtrained.com/public_html/main_file/resources/views/event/create.blade.php ENDPATH**/ ?>