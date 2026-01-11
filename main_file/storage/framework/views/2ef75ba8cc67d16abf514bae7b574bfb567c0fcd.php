<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Event')); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="<?php echo e(route('dashboard')); ?>">
                <?php echo e(__('Dashboard')); ?>

            </a>
        </li>
        <li class="breadcrumb-item active">
            <a href="#"><?php echo e(__('Event')); ?></a>
        </li>
    </ul>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('card-action-btn'); ?>
    <?php if(Gate::check('create event')): ?>
        <a class="btn btn-secondary btn-sm customModal" href="#" data-size="lg" data-url="<?php echo e(route('event.create')); ?>"
            data-title="<?php echo e(__('Create New Event')); ?>"> <i class="ti-plus mr-5"></i><?php echo e(__('Create Event')); ?>

        </a>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-sm-12">
            <div class="card table-card">
                <div class="card-header">
                    <div class="row align-items-center g-2">
                        <div class="col">
                            <h5>
                                <?php echo e(__('Events')); ?>

                            </h5>
                        </div>
                        <?php if(Gate::check('create event')): ?>
                            <div class="col-auto">
                                <a href="#" class="btn btn-secondary customModal" data-size="lg"
                                    data-url="<?php echo e(route('event.create')); ?>" data-title="<?php echo e(__('Create Event')); ?> ">
                                    <i class="ti ti-circle-plus align-text-bottom"></i>
                                    <?php echo e(__('Create Event')); ?>

                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="dt-responsive table-responsive">
                        <table class="table table-hover advance-datatable">
                            <thead>
                                <tr>
                                    <th><?php echo e(__('ID')); ?></th>
                                    <th><?php echo e(__('Name')); ?></th>
                                    <th><?php echo e(__('Date')); ?></th>
                                    <th><?php echo e(__('location')); ?></th>
                                    <th><?php echo e(__('Participant')); ?></th>
                                    <th><?php echo e(__('Deadline')); ?></th>
                                    <th><?php echo e(__('Status')); ?></th>
                                    <?php if(Gate::check('edit event') || Gate::check('delete event') || Gate::check('show event')): ?>
                                        <th><?php echo e(__('Action')); ?></th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $events; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><?php echo e(eventPrefix() . $event->event_id); ?></td>
                                        <td><?php echo e($event->event_name); ?></td>
                                        <td><?php echo e(dateFormat($event->date_time)); ?> - <?php echo e(timeFormat($event->date_time)); ?></td>
                                        <td><?php echo e($event->location); ?></td>
                                        <td><?php echo e($event->max_participant); ?></td>
                                        <td><?php echo e(dateFormat($event->registration_deadline)); ?></td>
                                        <td>
                                            <?php if($event->availability_status == 'Open'): ?>
                                                <span class="badge text-bg-success"><?php echo e($event->availability_status); ?></span>
                                            <?php elseif($event->availability_status == 'Cancelled'): ?>
                                                <span class="badge text-bg-danger"><?php echo e($event->availability_status); ?></span>
                                            <?php else: ?>
                                                <span class="badge text-bg-warning"><?php echo e($event->availability_status); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if(Gate::check('edit event') || Gate::check('delete event') || Gate::check('show event')): ?>
                                                <?php echo Form::open(['route' => ['event.destroy', $event->id], 'method' => 'DELETE']); ?>

                                                <?php if(Gate::check('show event')): ?>
                                                    <a href="#" data-size="lg"
                                                        data-url="<?php echo e(route('event.show', $event->id)); ?>"
                                                        data-title="<?php echo e(__('View Event')); ?>"
                                                        class="avtar avtar-xs btn-link-warning text-warning customModal"><i data-feather="eye"></i></a>
                                                <?php endif; ?>
                                                <?php if(Gate::check('edit event')): ?>
                                                    <a href="#" data-size="lg"
                                                        data-url="<?php echo e(route('event.edit', $event->id)); ?>"
                                                        data-title="<?php echo e(__('Edit Event')); ?>"
                                                        class="avtar avtar-xs btn-link-secondary text-secondary customModal"><i data-feather="edit"></i></a>
                                                <?php endif; ?>
                                                <?php if(Gate::check('delete event')): ?>
                                                    <a href="#" data-size="lg" data-ajax-popup="true"
                                                        data-title="<?php echo e(__('Delete Event')); ?>"
                                                        class="avtar avtar-xs btn-link-danger text-danger confirm_dialog"><i data-feather="trash-2"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <?php echo Form::close(); ?>

                                            <?php endif; ?>
                                        </td>
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

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/u417948420/domains/members-triumphwest.triumphtrained.com/public_html/main_file/resources/views/event/index.blade.php ENDPATH**/ ?>