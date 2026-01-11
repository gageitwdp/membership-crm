<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Income Report')); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Dashboard')); ?></a></li>
    <li class="breadcrumb-item" aria-current="page"> <?php echo e(__('Income Report')); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('script-page'); ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        "use strict";
        document.addEventListener("DOMContentLoaded", function() {
            const ctx = document.getElementById('incomeChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($chartData->keys(), 15, 512) ?>,
                    datasets: [{
                        label: '<?php echo e(__('Income')); ?>',
                        data: <?php echo json_encode($chartData->values(), 15, 512) ?>,
                        backgroundColor: 'rgba(54, 162, 235, 0.6)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                }
            });
        });
    </script>
<?php $__env->stopPush(); ?>
<?php $__env->startPush('css-page'); ?>
    <style>
        .cust-pro {
            width: 230px;
        }

        .choices__list--dropdown .choices__item--selectable:after {
            content: '';
        }

        .choices__list--dropdown .choices__item--selectable {
            padding-right: 10px;
        }
    </style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-sm-12">
            <div class="card table-card">

                
                <div class="card-header">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div>
                            <h5 class="mb-0"><?php echo e(__('Income Report')); ?></h5>
                        </div>

                        <form action="<?php echo e(route('report.income')); ?>" method="get">
                            <div class="row gx-2 gy-1 align-items-end">
                                <div class="cust-pro">
                                    <?php echo e(Form::label('member_id', __('Member'), ['class' => 'form-label'])); ?>

                                    <?php echo e(Form::select('member_id', $members, request('member_id'), [
                                        'class' => 'form-control hidesearch',
                                        'id' => 'member_id',
                                    ])); ?>

                                </div>

                                <div class="cust-pro">
                                    <?php echo e(Form::label('plan_id', __('Plan'), ['class' => 'form-label'])); ?>

                                    <?php echo e(Form::select('plan_id', $plans, request('plan_id'), [
                                        'class' => 'form-control hidesearch',
                                        'id' => 'plan_id',
                                    ])); ?>

                                </div>

                                <div class="cust-pro">
                                    <?php echo e(Form::label('start_date', __('Start Date'), ['class' => 'form-label'])); ?>

                                    <input class="form-control" name="start_date" type="date"
                                        value="<?php echo e(request('start_date')); ?>">
                                </div>

                                <div class="cust-pro">
                                    <?php echo e(Form::label('end_date', __('End Date'), ['class' => 'form-label'])); ?>

                                    <input class="form-control" name="end_date" type="date"
                                        value="<?php echo e(request('end_date')); ?>">
                                </div>

                                <div class="col-auto">
                                    <button type="submit" class="btn btn-light-secondary px-3">
                                        <i class="ti ti-search"></i>
                                    </button>
                                </div>

                                <div class="col-auto">
                                    <a href="<?php echo e(route('report.income')); ?>" class="btn btn-light-dark px-3">
                                        <i class="ti ti-refresh"></i>
                                    </a>
                                </div>

                            </div>
                        </form>
                    </div>
                </div>

                
                

                
                <div class="card-body pt-0">
                    <div class="dt-responsive table-responsive">
                        <table class="table table-hover advance-datatable">
                            <thead>
                                <tr>
                                    <th><?php echo e(__('Member')); ?></th>
                                    <th><?php echo e(__('Date')); ?></th>
                                    <th><?php echo e(__('Plan')); ?></th>
                                    <th><?php echo e(__('Amount')); ?></th>
                                    <th><?php echo e(__('Payment Method')); ?></th>
                                    <th><?php echo e(__('Status')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><?php echo e($payment->member->first_name ?? '-'); ?></td>
                                        <td><?php echo e(dateformat($payment->payment_date)); ?></td>
                                        <td><?php echo e($payment->plan->plan_name ?? '-'); ?></td>
                                        <td><?php echo e(priceformat($payment->amount)); ?></td>
                                        <td><?php echo e(ucfirst($payment->payment_method)); ?></td>
                                        <td>
                                            <?php if($payment->status == 'Paid'): ?>
                                                <span class="badge bg-light-success"><?php echo e($payment->status); ?></span>
                                            <?php elseif($payment->status == 'Pending'): ?>
                                                <span class="badge bg-light-warning"><?php echo e($payment->status); ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-light-danger"><?php echo e($payment->status); ?></span>
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

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/u417948420/domains/members-triumphwest.triumphtrained.com/public_html/main_file/resources/views/report/income.blade.php ENDPATH**/ ?>