<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Membership Plan')); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="<?php echo e(route('dashboard')); ?>">
                <?php echo e(__('Dashboard')); ?>

            </a>
        </li>
        <li class="breadcrumb-item active">
            <a href="#">
                <?php echo e(__('Membership Plan')); ?>

            </a>
        </li>
    </ul>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('card-action-btn'); ?>
    <?php if(Gate::check('create membership plan')): ?>
        <a class="btn btn-secondary btn-sm customModal" href="#" data-size="lg"
            data-url="<?php echo e(route('membership-plan.create')); ?>" data-title="<?php echo e(__('Create Membership Plan')); ?>"> <i
                class="ti-plus mr-5"></i>
            <?php echo e(__('Create Membership Plan')); ?>

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
                                <?php echo e(__('Membership Plan')); ?>

                            </h5>
                        </div>
                        <?php if(Gate::check('create membership plan')): ?>
                            <div class="col-auto">
                                <a href="#" class="btn btn-secondary customModal" data-size="lg"
                                    data-url="<?php echo e(route('membership-plan.create')); ?>"
                                    data-title="<?php echo e(__('Create Membership Plan')); ?> ">
                                    <i class="ti ti-circle-plus align-text-bottom"></i>
                                    <?php echo e(__('Create Membership Plan')); ?>

                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
        <div class="col-sm-12 mb-5">
            <div class="row g-4">
                <?php $__currentLoopData = $memberShipPlans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $membershipPlan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $MemberLatPlan = lastMembershipPlan();
                    ?>
                    <div class="col-md-3">
                        <div class="card price-card p-4 border border-secondary border-2 h-100">
                            <div class="card-body bg-secondary bg-opacity-10 rounded v3">

                                
                                <h4 class="mb-0 text-secondary"><?php echo e($membershipPlan->plan_name); ?></h4>
                                <div class="price-price mt-3">
                                    <?php echo e(priceFormat($membershipPlan->price)); ?>

                                </div>

                                <ul class="list-group list-group-flush product-list v3">
                                    <li class="list-group-item"><?php echo e(__('Plan ID')); ?> :
                                        <?php echo e(planPrefix() . $membershipPlan->plan_id); ?></li>
                                    <li class="list-group-item"><?php echo e(__('Duration')); ?> : <?php echo e($membershipPlan->duration); ?>

                                    </li>
                                    <li class="list-group-item"><?php echo e(__('Billing Frequency')); ?> :
                                        <?php echo e($membershipPlan->billing_frequency); ?></li>
                                </ul>



                                <div class="mt-auto d-flex justify-content-between gap-2">
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('show membership plan')): ?>
                                        <a class="btn btn-outline-warning btn-sm w-100 customModal"
                                             href="#" data-size="lg"
                                            data-title="<?php echo e(__('Show membership plan')); ?>"
                                            data-url="<?php echo e(route('membership-plan.show', $membershipPlan->id)); ?>">
                                            <?php echo e(__('Show')); ?></a>
                                    <?php endif; ?>
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit membership plan')): ?>
                                        <a class="btn btn-outline-success btn-sm w-100 customModal"  href="#" data-size="lg"
                                            data-url="<?php echo e(route('membership-plan.edit', $membershipPlan)); ?>"
                                            data-title="<?php echo e(__('Edit membership plan')); ?>"> <?php echo e(__('Edit')); ?></a>
                                    <?php endif; ?>
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete membership plan')): ?>
                                        <?php echo Form::open(['method' => 'DELETE', 'route' => ['membership-plan.destroy', $membershipPlan->id]]); ?>

                                        <a class="btn btn-outline-danger btn-sm w-100 confirm_dialog"   href="#">
                                            <?php echo e(__('Detete')); ?></a>
                                        <?php echo Form::close(); ?>

                                    <?php endif; ?>

                                </div>

                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/u417948420/domains/members-triumphwest.triumphtrained.com/public_html/main_file/resources/views/membership_plan/index.blade.php ENDPATH**/ ?>