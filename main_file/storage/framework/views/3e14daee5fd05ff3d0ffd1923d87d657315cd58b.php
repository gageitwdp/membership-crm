<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Expense')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="<?php echo e(route('dashboard')); ?>">
                <?php echo e(__('Dashboard')); ?>

            </a>
        </li>
        <li class="breadcrumb-item active">
            <a href="#"><?php echo e(__('Expense')); ?></a>
        </li>
    </ul>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('card-action-btn'); ?>
    <?php if(Gate::check('create expense')): ?>
        <a class="btn btn-secondary btn-sm customModal" href="#" data-size="lg"
            data-url="<?php echo e(route('expense.create')); ?>" data-title="<?php echo e(__('Create Expense')); ?>"> <i
                class="ti-plus mr-5"></i><?php echo e(__('Create Expense')); ?></a>
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
                                <?php echo e(__('Expense')); ?>

                            </h5>
                        </div>
                        <?php if(Gate::check('create expense')): ?>
                            <div class="col-auto">
                                <a href="#" class="btn btn-secondary customModal" data-size="lg"
                                    data-url="<?php echo e(route('expense.create')); ?>" data-title="<?php echo e(__('Create Expense')); ?> ">
                                    <i class="ti ti-circle-plus align-text-bottom"></i>
                                    <?php echo e(__('Create Expense')); ?>

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
                                    <th><?php echo e(__('Title')); ?></th>
                                    <th><?php echo e(__('Date')); ?></th>
                                    <th><?php echo e(__('Amount')); ?></th>
                                    <th><?php echo e(__('Type')); ?></th>
                                    <th><?php echo e(__('Receipt')); ?></th>
                                    <?php if(Gate::check('delete expense') || Gate::check('edit expense') || Gate::check('show expense')): ?>
                                        <th><?php echo e(__('Action')); ?></th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $expenses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $expense): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><?php echo e(expensePrefix() . $expense->id); ?></td>
                                        <td><?php echo e($expense->title); ?></td>
                                        <td><?php echo e(dateFormat($expense->date)); ?></td>
                                        <td><?php echo e(priceFormat($expense->amount)); ?></td>
                                        <td><?php echo e(!empty($expense->expenseType) ? $expense->expenseType->type : '-'); ?></td>
                                        <td>
                                            <?php if(!empty($expense->receipt)): ?>
                                                <a href="<?php echo e(asset(Storage::url('upload/receipt')) . '/' . $expense->receipt); ?>"
                                                    download="download"><i data-feather="download"></i></a>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <?php if(Gate::check('delete expense') || Gate::check('edit expense') || Gate::check('show expense')): ?>
                                            <td>
                                                <?php echo Form::open(['url' => 'expense/' . $expense->id, 'method' => 'DELETE']); ?>

                                                <?php if(Gate::check('show expense')): ?>
                                                    <a class="avtar avtar-xs btn-link-warning text-warning customModal" href="#" data-size="lg"
                                                        data-url="<?php echo e(route('expense.show', $expense->id)); ?>"
                                                        data-title="<?php echo e(__('View Expense')); ?>">
                                                        <i data-feather="eye"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <?php if(Gate::check('edit expense')): ?>
                                                    <a class="avtar avtar-xs btn-link-secondary text-secondary customModal" href="#" data-size="lg"
                                                        data-url="<?php echo e(route('expense.edit', $expense->id)); ?>"
                                                        data-title="<?php echo e(__('Edit Expense')); ?>">
                                                        <i data-feather="edit"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <?php if(Gate::check('delete expense')): ?>
                                                    <a class="avtar avtar-xs btn-link-danger text-danger confirm_dialog" href="#">
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

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/u417948420/domains/members-triumphwest.triumphtrained.com/public_html/main_file/resources/views/expense/index.blade.php ENDPATH**/ ?>