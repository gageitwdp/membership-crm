<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Member')); ?>

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
                <?php echo e(__('Member')); ?>

            </a>
        </li>
    </ul>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('card-action-btn'); ?>
    <?php if(Gate::check('create member')): ?>
        <a class="btn btn-secondary btn-sm ml-20" href="<?php echo e(route('member.create')); ?>">
            <i class="ti-plus mr-5"></i>
            <?php echo e(__('Create Member')); ?>

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
                                <?php echo e(__('Member')); ?>

                            </h5>
                        </div>
                        <?php if(Gate::check('create member')): ?>
                            <div class="col-auto">
                                <a class="btn btn-secondary"href="<?php echo e(route('member.create')); ?>"
                                    data-title="<?php echo e(__('Create Member')); ?> ">
                                    <i class="ti ti-circle-plus align-text-bottom"></i>
                                    <?php echo e(__('Create Member')); ?>

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
                                    <th><?php echo e(__('Profile')); ?></th>
                                    <th><?php echo e(__('Member ID')); ?></th>
                                    <th><?php echo e(__('Email')); ?></th>
                                    <th><?php echo e(__('Phone No.')); ?></th>
                                    <th><?php echo e(__('Membership')); ?></th>
                                    <th><?php echo e(__('Expiry Date')); ?></th>
                                    <th><?php echo e(__('Gender')); ?></th>
                                    <?php if(Gate::check('edit member') || Gate::check('delete member')): ?>
                                        <th><?php echo e(__('Action')); ?></th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $members; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0 wid-40">
                                                    <img class="img-radius img-fluid wid-40"
                                                        src="<?php echo e(!empty($member->image) ? asset(Storage::url('upload/member')) . '/' . $member->image : asset(Storage::url('upload/profile')) . '/avatar.png'); ?>"
                                                        alt="User image">
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h5 class="mb-1">
                                                        <?php echo e($member->first_name); ?> <?php echo e($member->last_name); ?>


                                                    </h5>

                                                </div>
                                            </div>

                                        </td>
                                        <td> <?php echo e(memberPrefix() . $member->member_id); ?></td>


                                        <td><?php echo e($member->email); ?> </td>
                                        <td><?php echo e($member->phone); ?> </td>
                                        <td><?php echo e(!empty($member->membershipLates) && !empty($member->membershipLates->plans) ? $member->membershipLates->plans->plan_name: '-'); ?>

                                        </td>
                                        <td><?php echo e(!empty($member->membershipLates) ? dateFormat($member->membershipLates->expiry_date) : '-'); ?>

                                        </td>
                                        <td><?php echo e($member->gender); ?> </td>
                                        <?php if(Gate::check('edit member') || Gate::check('delete member')): ?>
                                            <td>
                                                <div class="cart-action">
                                                    <?php echo Form::open(['method' => 'DELETE', 'route' => ['member.destroy', $member->id]]); ?>

                                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('show member')): ?>
                                                        <a class="avtar avtar-xs btn-link-warning text-warning"
                                                            data-bs-toggle="tooltip"
                                                            data-bs-original-title="<?php echo e(__('Show')); ?>"
                                                            href="<?php echo e(route('member.show', Illuminate\Support\Facades\Crypt::encrypt($member->id))); ?>">
                                                            <i data-feather="eye"></i></a>
                                                    <?php endif; ?>
                                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit member')): ?>
                                                        <a class="avtar avtar-xs btn-link-secondary text-secondary"
                                                            data-bs-toggle="tooltip"
                                                            data-bs-original-title="<?php echo e(__('Edit')); ?>"
                                                            href="<?php echo e(route('member.edit', Illuminate\Support\Facades\Crypt::encrypt($member->id))); ?>">
                                                            <i data-feather="edit"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete member')): ?>
                                                        <a class=" avtar avtar-xs btn-link-danger text-danger confirm_dialog"
                                                            data-bs-toggle="tooltip"
                                                            data-bs-original-title="<?php echo e(__('Detete')); ?>" href="#"> <i
                                                                data-feather="trash-2"></i></a>
                                                    <?php endif; ?>
                                                    <?php echo Form::close(); ?>

                                                </div>
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

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/u417948420/domains/members-triumphwest.triumphtrained.com/public_html/main_file/resources/views/member/index.blade.php ENDPATH**/ ?>