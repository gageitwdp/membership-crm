<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Activity Tracking')); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="<?php echo e(route('dashboard')); ?>">
                <?php echo e(__('Dashboard')); ?>

            </a>
        </li>
        <li class="breadcrumb-item active">
            <a href="#"><?php echo e(__('Activity Tracking')); ?></a>
        </li>
    </ul>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('card-action-btn'); ?>
    <a class="btn btn-secondary btn-sm customModal" href="#" data-size="lg"
        data-url="<?php echo e(route('activity-tracking.create')); ?>" data-title="<?php echo e(__('Create New Activity Tracking')); ?>"> <i
            class="ti-plus mr-5"></i><?php echo e(__('Create Activity Tracking')); ?></a>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-sm-12">
            <div class="card table-card">
                <div class="card-header">
                    <div class="row align-items-center g-2">
                        <div class="col">
                            <h5>
                                <?php echo e(__('Activity Tracking')); ?>

                            </h5>
                        </div>
                        <div class="col-auto">
                            <?php if(Gate::check('create activity tracking')): ?>
                                <a href="#" class="btn btn-secondary customModal" data-size="lg"
                                    data-url="<?php echo e(route('activity-tracking.create')); ?>"
                                    data-title="<?php echo e(__('Create New Activity Tracking')); ?> ">
                                    <i class="ti ti-circle-plus align-text-bottom"></i>
                                    <?php echo e(__('Create Activity Tracking')); ?>

                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="dt-responsive table-responsive">
                        <table class="table table-hover advance-datatable">
                            <thead>
                                <tr>
                                    <th><?php echo e(__('Member')); ?></th>
                                    <th><?php echo e(__('Event')); ?></th>
                                    <th><?php echo e(__('Check in')); ?></th>
                                    <th><?php echo e(__('Check out')); ?></th>
                                    <th><?php echo e(__('Duration')); ?></th>
                                    <?php if(Gate::check('edit activity tracking') ||
                                            Gate::check('delete activity tracking') ||
                                            Gate::check('show activity tracking')): ?>
                                        <th><?php echo e(__('Action')); ?></th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $activityTrackings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><?php echo e(!empty($activity->members) ? $activity->members->first_name : '-'); ?></td>
                                        <td><?php echo e(!empty($activity->events) ? $activity->events->event_name : '-'); ?></td>
                                        <td><?php echo e(dateFormat($activity->check_in)); ?> <?php echo e(timeFormat($activity->check_in)); ?>

                                        </td>
                                        <td><?php echo e(dateFormat($activity->check_out)); ?> <?php echo e(timeFormat($activity->check_out)); ?>

                                        </td>
                                        <td><?php echo e($activity->duration); ?></td>
                                        <?php if(Gate::check('edit activity tracking') ||
                                                Gate::check('delete activity tracking') ||
                                                Gate::check('show activity tracking')): ?>
                                            <td>
                                                <?php echo Form::open(['route' => ['activity-tracking.destroy', $activity->id], 'method' => 'DELETE']); ?>

                                                <?php if(Gate::check('show activity tracking')): ?>
                                                    <a href="#"
                                                        data-url="<?php echo e(route('activity-tracking.show', $activity->id)); ?>"
                                                        data-title="<?php echo e(__('View Activity Tracking')); ?>" data-size="lg"
                                                        class="avtar avtar-xs btn-link-warning text-warning customModal"><i
                                                            data-feather="eye"></i></a>
                                                <?php endif; ?>
                                                <?php if(Gate::check('edit activity tracking')): ?>
                                                    <a href="#"
                                                        data-url="<?php echo e(route('activity-tracking.edit', $activity->id)); ?>"
                                                        data-title="<?php echo e(__('Edit Activity Tracking')); ?>" data-size="lg"
                                                        class="avtar avtar-xs btn-link-secondary text-secondary customModal"><i
                                                            data-feather="edit"></i></a>
                                                <?php endif; ?>
                                                <?php if(Gate::check('delete activity tracking')): ?>
                                                    <a href="#"
                                                        class="avtar avtar-xs btn-link-danger text-danger confirm_dialog"><i
                                                            data-feather="trash-2"></i></a>
                                                <?php endif; ?>
                                                <?php echo Form::close(); ?>

                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>


<?php $__env->startPush('script-page'); ?>
    <script>
        $(document).ready(function() {
            $(document).on('change', '#check_in, #check_out', function() {
                var checkIn = $('#check_in').val();
                var checkOut = $('#check_out').val();


                if (checkIn && checkOut) {
                    var start = new Date(checkIn);
                    var end = new Date(checkOut);

                    if (end > start) {
                        var diff = end - start;

                        var hours = Math.floor(diff / 1000 / 60 / 60);
                        var minutes = Math.floor((diff / 1000 / 60) % 60);

                        var duration = hours + ' hours ' + minutes + ' minutes';

                        $('#duration').val(duration);
                    } else {
                        $('#duration').val('');
                    }
                } else {
                    $('#duration').val('');
                }

            });
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/u417948420/domains/members-triumphwest.triumphtrained.com/public_html/main_file/resources/views/activity_tracking/index.blade.php ENDPATH**/ ?>