    <?php echo e(Form::open(['url' => 'membership-plan', 'method' => 'post', 'enctype' => 'multipart/form-data'])); ?>

    <div class="modal-body">
        <div class="row">
            <div class="form-group col-md-6">
                <?php echo e(Form::label('plan_name', __('plan Name'), ['class' => 'form-label'])); ?>

                <?php echo e(Form::text('plan_name', null, ['class' => 'form-control', 'placeholder' => __('Enter plan name'), 'required' => 'required'])); ?>

            </div>
            <div class="form-group col-md-6">
                <?php echo e(Form::label('price', __('price'), ['class' => 'form-label'])); ?>

                <?php echo e(Form::number('price', null, ['class' => 'form-control', 'placeholder' => __('Enter price'), 'required' => 'required'])); ?>

            </div>
            <div class="form-group col-md-6">
                <?php echo e(Form::label('duration', __('Duration'), ['class' => 'form-label'])); ?>

                <?php echo e(Form::select('duration', ['' => 'Select Frequency', 'Monthly' => 'Monthly', '3-Month' => '3-Month', '6-Month' => '6-Month', 'Yearly' => 'Yearly'], null, ['class' => 'form-control'])); ?>

            </div>
            <div class="form-group col-md-6">
                <?php echo e(Form::label('billing_frequency', __('billing frequency'), ['class' => 'form-label'])); ?>

                <?php echo e(Form::select('billing_frequency', ['' => 'Select Frequency', 'Monthly' => 'Monthly', '3-Month' => '3-Month', '6-Month' => '6-Month', 'Yearly' => 'Yearly'], null, ['class' => 'form-control'])); ?>

            </div>
            
            <div class="form-group col-md-6">
                <?php echo e(Form::label('plan_description', __('plan description'), ['class' => 'form-label'])); ?>

                <?php echo e(Form::textarea('plan_description', null, ['class' => 'form-control', 'placeholder' => __('Enter note'), 'rows' => '1'])); ?>

            </div>
        </div>
        <div class="modal-footer">
            <?php echo e(Form::submit(__('Create'), ['class' => 'btn btn-secondary'])); ?>

        </div>
        <?php echo e(Form::close()); ?>

    </div>
<?php /**PATH /home/u417948420/domains/members-triumphwest.triumphtrained.com/public_html/main_file/resources/views/membership_plan/create.blade.php ENDPATH**/ ?>