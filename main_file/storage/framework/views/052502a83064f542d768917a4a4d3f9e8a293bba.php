<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Membership')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="<?php echo e(route('dashboard')); ?>">
                <?php echo e(__('Dashboard')); ?>

            </a>
        </li>
        <li class="breadcrumb-item active">
            <a href="#"><?php echo e(__('Membership')); ?></a>
        </li>
    </ul>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-sm-12">
            <div class="card table-card">
                <div class="card-header">
                    <div class="row align-items-center g-2">
                        <div class="col">
                            <h5>
                                <?php echo e(__('Membership')); ?>

                            </h5>
                        </div>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="dt-responsive table-responsive">
                        <table class="table table-hover advance-datatable">
                            <thead>
                                <tr>
                                    <th><?php echo e(__('Member')); ?></th>
                                    <th><?php echo e(__('Plan')); ?></th>
                                    <th><?php echo e(__('Start Date')); ?></th>
                                    <th><?php echo e(__('Expiry Date')); ?></th>
                                    <th><?php echo e(__('Status')); ?></th>
                                    <?php if(Gate::check('edit membership') || Gate::check('delete membership') || Gate::check('show membership')): ?>
                                        <th><?php echo e(__('Action')); ?></th>
                                    <?php endif; ?>
                                </tr>

                            </thead>

                            <tbody>
                                <?php $__currentLoopData = $memberships; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $membership): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><?php echo e(!empty($membership->members) ? $membership->members->first_name : '-'); ?>

                                        </td>
                                        <td><?php echo e(!empty($membership->plans) ? $membership->plans->plan_name : '-'); ?></td>
                                        <td><?php echo e(dateFormat($membership->start_date)); ?></td>
                                        <td><?php echo e(dateFormat($membership->expiry_date)); ?></td>
                                        

                                        <td>
                                            <?php if($membership->status == "Expired"): ?>
                                                <span class="badge text-bg-danger"><?php echo e(__('Expired')); ?></span>
                                            <?php else: ?>
                                                <span class="badge text-bg-success"><?php echo e(__('Active')); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <?php if(Gate::check('edit membership') || Gate::check('delete membership') || Gate::check('show membership')): ?>
                                            <td>
                                                <?php echo Form::open(['route' => ['membership.destroy', $membership->id], 'method' => 'DELETE']); ?>

                                                <?php if(Gate::check('show membership')): ?>
                                                    <a href="#"
                                                        class="avtar avtar-xs btn-link-warning text-warning customModal"
                                                        data-size="lg"
                                                        data-url="<?php echo e(route('membership.show', $membership->id)); ?>"
                                                        data-title="<?php echo e(__('View Membership')); ?>">
                                                        <i data-feather="eye"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <?php if(Gate::check('delete membership')): ?>
                                                    <a href="#"
                                                        class="avtar avtar-xs btn-link-danger text-danger confirm_dialog"
                                                        data-title="<?php echo e(__('Delete Membership')); ?>">
                                                        <i data-feather="trash-2"></i>
                                                    </a>
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

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/u417948420/domains/members-triumphwest.triumphtrained.com/public_html/main_file/resources/views/membership/index.blade.php ENDPATH**/ ?>