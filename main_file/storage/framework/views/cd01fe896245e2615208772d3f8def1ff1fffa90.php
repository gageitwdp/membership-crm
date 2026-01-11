<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Membership Suspension')); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="<?php echo e(route('dashboard')); ?>">
                <?php echo e(__('Dashboard')); ?>

            </a>
        </li>
        <li class="breadcrumb-item active">
            <a href="#"><?php echo e(__('Membership Suspension')); ?></a>
        </li>
    </ul>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('card-action-btn'); ?>
    <?php if(Gate::check('create membership suspension')): ?>
        <a class="btn btn-secondary btn-sm customModal" href="#" data-size="lg"
            data-url="<?php echo e(route('membership-suspension.create')); ?>" data-title="<?php echo e(__('Create Membership Suspension')); ?>"> <i
                class="ti-plus mr-5"></i><?php echo e(__('Create Membership Suspension')); ?></a>
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
                                <?php echo e(__('Membership Suspension')); ?>

                            </h5>
                        </div>
                        <?php if(Gate::check('create membership suspension')): ?>
                            <div class="col-auto">
                                <a href="#" class="btn btn-secondary customModal" data-size="lg"
                                    data-url="<?php echo e(route('membership-suspension.create')); ?>"
                                    data-title="<?php echo e(__('Create Membership Suspension')); ?> ">
                                    <i class="ti ti-circle-plus align-text-bottom"></i>
                                    <?php echo e(__('Create Membership Suspension')); ?>

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
                                    <th><?php echo e(__('Member')); ?></th>
                                    <th><?php echo e(__('Start Date')); ?></th>
                                    <th><?php echo e(__('End Date')); ?></th>
                                    <th><?php echo e(__('Status')); ?></th>
                                    <?php if(Gate::check('edit membership suspension') ||
                                            Gate::check('delete membership suspension') ||
                                            Gate::check('show membership suspension')): ?>
                                        <th><?php echo e(__('Action')); ?></th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $membershipSuspensions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $suspension): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><?php echo e(suspensionPrefix() . $suspension->suspension_id); ?></td>
                                        <td><?php echo e(!empty($suspension->members) ? $suspension->members->first_name : '-'); ?></td>
                                        <td><?php echo e(dateFormat($suspension->start_date)); ?></td>
                                        <td><?php echo e(dateFormat($suspension->end_date)); ?></td>
                                        <td>
                                            <?php if($suspension->status == 'Approved'): ?>
                                                <span class="badge text-bg-success"><?php echo e($suspension->status); ?></span>
                                            <?php elseif($suspension->status == 'Pending'): ?>
                                                <span class="badge text-bg-warning"><?php echo e($suspension->status); ?></span>
                                            <?php else: ?>
                                                <span class="badge text-bg-danger"><?php echo e($suspension->status); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <?php if(Gate::check('edit membership suspension') ||
                                                Gate::check('delete membership suspension') ||
                                                Gate::check('show membership suspension')): ?>
                                            <td>
                                                <?php echo Form::open(['url' => 'membership-suspension/' . $suspension->suspension_id, 'method' => 'DELETE']); ?>

                                                <?php if(Gate::check('show membership suspension')): ?>
                                                    <a href="#" class="avtar avtar-xs btn-link-warning text-warning customModal" data-size="lg"
                                                        data-url="<?php echo e(route('membership-suspension.show', $suspension->id)); ?>"
                                                        data-title="<?php echo e(__('Membership Suspension Details')); ?>">
                                                        <i data-feather="eye"></i>
                                                    </a>
                                                <?php endif; ?>

                                                <?php if(Gate::check('edit membership suspension')): ?>
                                                    <a href="#" class="avtar avtar-xs btn-link-secondary text-secondary customModal" data-size="lg"
                                                        data-url="<?php echo e(route('membership-suspension.edit', $suspension->id)); ?>"
                                                        data-title="<?php echo e(__('Edit Membership Suspension')); ?>">
                                                        <i data-feather="edit"></i>
                                                    </a>
                                                <?php endif; ?>

                                                <?php if(Gate::check('delete membership suspension')): ?>
                                                    <a type="submit" class="avtar avtar-xs btn-link-danger text-danger confirm_dialog" href="#"><i
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

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/u417948420/domains/members-triumphwest.triumphtrained.com/public_html/main_file/resources/views/membership_suspension/index.blade.php ENDPATH**/ ?>