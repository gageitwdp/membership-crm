<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Payments')); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="<?php echo e(route('dashboard')); ?>">
                <?php echo e(__('Dashboard')); ?>

            </a>
        </li>
        <li class="breadcrumb-item active">
            <a href="#"><?php echo e(__('Payments')); ?></a>
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
                                <?php echo e(__('Payments')); ?>

                            </h5>
                        </div>

                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="dt-responsive table-responsive">
                        <table class="table table-hover advance-datatable">
                            <thead>
                                <tr>
                                    <th><?php echo e(__('ID')); ?></th>
                                    <th><?php echo e(__('Member')); ?></th>
                                    <th><?php echo e(__('Plan')); ?></th>
                                    <th><?php echo e(__('Period')); ?></th>
                                    <th><?php echo e(__('Amount')); ?></th>
                                    <th><?php echo e(__('Status')); ?></th>

                                    <?php if(Gate::check('show membership payment') || Gate::check('delete membership payment')): ?>
                                        <th><?php echo e(__('Action')); ?></th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $membershipPayments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $membership = App\Models\Membership::where('member_id', $payment->member_id)
                                            ->where('plan_id', $payment->plan_id)
                                            ->first();
                                    ?>

                                    <tr>
                                        <td><?php echo e(paymentPrefix() . $payment->payment_id); ?></td>
                                        <td><?php echo e(!empty($payment->members) ? $payment->members->first_name : ''); ?></td>
                                        <td><?php echo e(!empty($payment->plans) ? $payment->plans->plan_name : ''); ?></td>
                                        <td> <?php echo e(dateFormat($membership->start_date) ?? '-'); ?> -
                                            <?php echo e(dateFormat($membership->expiry_date) ?? '-'); ?></td>

                                        <td><?php echo e(priceFormat($payment->amount)); ?></td>
                                        <td>
                                            <?php if($payment->status == 'Paid'): ?>
                                                <span class="badge text-bg-success"><?php echo e($payment->status); ?></span>
                                            <?php else: ?>
                                                <span class="badge text-bg-danger"><?php echo e($payment->status); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <?php if(Gate::check('show membership payment') || Gate::check('delete membership payment')): ?>
                                            <td>
                                                <?php echo Form::open(['route' => ['membership-payment.destroy', $payment->id], 'method' => 'DELETE']); ?>

                                                <?php if(Gate::check('show membership payment')): ?>
                                                    <a class="avtar avtar-xs btn-link-warning text-warning"
                                                        href="<?php echo e(route('membership-payment.show', \Illuminate\Support\Facades\Crypt::encrypt($payment->id))); ?>">
                                                        <i data-feather="eye"></i>
                                                    </a>
                                                <?php endif; ?>

                                                <?php if(Gate::check('delete membership payment')): ?>
                                                    <a class="avtar avtar-xs btn-link-danger text-danger confirm_dialog"
                                                        href="#">
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

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/u417948420/domains/members-triumphwest.triumphtrained.com/public_html/main_file/resources/views/membership_payment/index.blade.php ENDPATH**/ ?>