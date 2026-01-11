<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Create Member')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <ul class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Dashboard')); ?></a></li>
        <li class="breadcrumb-item"><a href="<?php echo e(route('member.index')); ?>"><?php echo e(__('Member')); ?></a></li>
        <li class="breadcrumb-item active" aria-current="page"><?php echo e(__('Create Member')); ?></li>
    </ul>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row">
        <?php echo e(Form::open(['url' => 'member', 'method' => 'post', 'enctype' => 'multipart/form-data'])); ?>

        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title"><?php echo e(__('Create Member')); ?></h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="form-group col-md-4">
                            <?php echo e(Form::label('first_name', __('First Name'), ['class' => 'form-label'])); ?> <span
                                class="text-danger">*</span>
                            <?php echo e(Form::text('first_name', null, ['class' => 'form-control', 'placeholder' => __('Enter first name'), 'required' => 'required'])); ?>

                        </div>
                        <div class="form-group col-md-4">
                            <?php echo e(Form::label('last_name', __('last Name'), ['class' => 'form-label'])); ?>

                            <?php echo e(Form::text('last_name', null, ['class' => 'form-control', 'placeholder' => __('Enter last name')])); ?>

                        </div>
                        <div class="form-group col-md-4">
                            <?php echo e(Form::label('email', __('email'), ['class' => 'form-label'])); ?>

                            <?php echo e(Form::email('email', null, ['class' => 'form-control', 'placeholder' => __('Enter email')])); ?>

                        </div>
                        <div class="form-group col-md-4">
                            <?php echo e(Form::label('password', __('Password'), ['class' => 'form-label'])); ?>

                            <?php echo e(Form::password('password', ['class' => 'form-control', 'placeholder' => __('Enter password'), 'required' => 'required', 'minlength' => '6'])); ?>


                        </div>
                        <div class="form-group col-md-4">
                            <?php echo e(Form::label('phone', __('Phone No.'), ['class' => 'form-label'])); ?> <span
                                class="text-danger">*</span>
                            <?php echo e(Form::text('phone', null, ['class' => 'form-control', 'placeholder' => __('Enter phone number'), 'required' => 'required'])); ?>

                            <small class="form-text text-muted">
                                <?php echo e(__('Please enter the number with country code. e.g., +91XXXXXXXXXX')); ?>

                            </small>
                        </div>
                        <div class="form-group col-md-4">
                            <?php echo e(Form::label('dob', __('dob'), ['class' => 'form-label'])); ?>

                            <?php echo e(Form::date('dob', null, ['class' => 'form-control'])); ?>

                        </div>
                        <div class="form-group col-md-4">
                            <?php echo e(Form::label('address', __('address'), ['class' => 'form-label'])); ?> <span
                                class="text-danger">*</span>
                            <?php echo e(Form::text('address', null, ['class' => 'form-control', 'placeholder' => __('Enter address')])); ?>

                        </div>
                        <div class="form-group col-md-4">
                            <?php echo e(Form::label('gender', __('Gender'), ['class' => 'form-label'])); ?><span
                                class="text-danger">*</span>
                            <?php echo e(Form::select('gender', ['Male' => 'Male', 'Female' => 'Female'], null, ['class' => 'form-control hidesearch basic-select', 'required' => 'required'])); ?>

                        </div>
                        <div class="form-group col-md-4">
                            <?php echo e(Form::label('image', __('image'), ['class' => 'form-label'])); ?>

                            <?php echo e(Form::file('image', ['class' => 'form-control'])); ?>

                        </div>
                        <div class="form-group col-md-4">
                            <?php echo e(Form::label('emergency_contact_information', __('Emergency Contact Information'), ['class' => 'form-label'])); ?>

                            <?php echo e(Form::textarea('emergency_contact_information', null, ['class' => 'form-control', 'placeholder' => __('Enter emergency contact information'), 'rows' => '2'])); ?>

                        </div>
                        <div class="form-group col-md-8">
                            <?php echo e(Form::label('notes', __('Notes'), ['class' => 'form-label'])); ?>

                            <?php echo e(Form::textarea('notes', null, ['class' => 'form-control', 'placeholder' => __('Enter note'), 'rows' => '1'])); ?>

                        </div>
                    </div>


                </div>
            </div>

        </div>

        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title"><?php echo e(__('Assign Membership')); ?></h4>
                    <div class="form-check form-switch">
                        <div class="form-check form-switch custom-switch-v1 mb-2">
                            <input type="hidden" name="membership_part" value="off">

                            <input type="checkbox" class="form-check-input input-secondary" name="membership_part"
                                id="toggleMembership">
                        </div>
                    </div>
                </div>
                <div class="card-body" id="membershipSection">
                    <div class="row">
                        <div class="form-group col-md-6">
                            <?php echo e(Form::label('start_date', __('Start Date'), ['class' => 'form-label'])); ?>

                            <?php echo e(Form::date('start_date', today(), ['class' => 'form-control', 'id' => 'start_date'])); ?>

                        </div>
                        <div class="form-group col-md-6">
                            <?php echo Form::label('plan_id', __('Plan'), ['class' => 'form-label']); ?>

                            <?php echo Form::select('plan_id', $membership, null, [
                                'class' => 'form-control basic-select',
                                'id' => 'plan_id',
                            ]); ?>

                        </div>

                        <div class="form-group col-md-6">
                            <?php echo e(Form::label('expiry_date', __('Expiry Date'), ['class' => 'form-label'])); ?>

                            <?php echo e(Form::date('expiry_date', null, ['class' => 'form-control', 'id' => 'expiry_date', 'readonly' => 'readonly'])); ?>

                        </div>
                        <div class="form-group col-md-6">
                            <?php echo e(Form::label('status', __('Status'), ['class' => 'form-label'])); ?>

                            <?php echo e(Form::select('status', ['Payment Pending' => __('Payment Pending'), 'Active' => __('Active')], null, ['class' => 'form-control basic-select', 'required' => 'required'])); ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-end">
            <?php echo e(Form::submit(__('Create'), ['class' => 'btn btn-secondary'])); ?>

        </div>
        <?php echo e(Form::close()); ?>

    </div>
<?php $__env->stopSection(); ?>


<?php $__env->startPush('script-page'); ?>
    


    <script>
        $(document).ready(function() {
            let currentDuration = null;

            function calculateExpiryDate(start_date, duration) {
                let startDate = new Date(start_date);



                startDate.setMonth(startDate.getMonth() + duration);

                const year = startDate.getFullYear();
                const month = String(startDate.getMonth() + 1).padStart(2, '0');
                const day = String(startDate.getDate()).padStart(2, '0');

                const formattedEndDate = `${year}-${month}-${day}`;
                $('#expiry_date').val(formattedEndDate);
            }

            // Trigger when plan changes
            $(document).on('change', '#plan_id', function() {
                var plan_id = $(this).val();
                $.ajax({
                    url: "<?php echo e(route('getDurations')); ?>",
                    type: "GET",
                    data: {
                        plan_id: plan_id
                    },
                    success: function(data) {
                        currentDuration = parseInt(data.duration, 10);
                        let start_date = $('#start_date').val();
                        calculateExpiryDate(start_date, currentDuration);
                    }
                });
            });

            // Trigger when start date changes
            $(document).on('change', '#start_date', function() {
                let start_date = $(this).val();
                if (currentDuration !== null) {
                    calculateExpiryDate(start_date, currentDuration);
                }
            });
        });
    </script>


    <script>
        $(document).ready(function() {
            // Initially hide the card body
            $('#membershipSection').hide();

            $('#toggleMembership').change(function() {
                if ($(this).is(':checked')) {
                    $('#membershipSection').slideDown(); // Show with animation
                } else {
                    $('#membershipSection').slideUp(); // Hide with animation
                }
            });
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/u417948420/domains/members-triumphwest.triumphtrained.com/public_html/main_file/resources/views/member/create.blade.php ENDPATH**/ ?>