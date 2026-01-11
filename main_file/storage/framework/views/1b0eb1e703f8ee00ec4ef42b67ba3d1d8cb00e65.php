<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Contact Diary')); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Dashboard')); ?></a></li>
    <li class="breadcrumb-item" aria-current="page"> <?php echo e(__('Contact Diary')); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center g-2">
                        <div class="col">
                            <h5><?php echo e(__('Contact List')); ?></h5>
                        </div>
                        <?php if(Gate::check('create contact')): ?>
                            <div class="col-auto">
                                <a href="#" class="btn btn-secondary customModal" data-size="md"
                                    data-url="<?php echo e(route('contact.create')); ?>" data-title="<?php echo e(__('Create Contact')); ?>"> <i
                                        class="ti ti-circle-plus align-text-bottom"></i> <?php echo e(__('Create Contact')); ?></a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php $__currentLoopData = $contacts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $contact): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="col-xxl-3 col-xl-4 col-md-6">
                                <div class="card follower-card">
                                    <div class="card-body p-3">
                                        <?php if(Gate::check('edit contact') || Gate::check('delete contact')): ?>
                                            <div class="d-flex align-items-start mb-3">
                                                <div class="flex-grow-1 mx-2"></div>
                                                <div class="flex-shrink-0">
                                                    <div class="dropdown">
                                                        <a class="dropdown-toggle text-primary opacity-50 arrow-none"
                                                            href="#" data-bs-toggle="dropdown" aria-haspopup="true"
                                                            aria-expanded="false">
                                                            <i class="ti ti-dots f-16"></i>
                                                        </a>
                                                        <div class="dropdown-menu dropdown-menu-end">
                                                            <?php if(Gate::check('edit contact')): ?>
                                                                <a class="dropdown-item customModal" href="#" data-url="<?php echo e(route('contact.edit', $contact->id)); ?>"
                                                                    data-title="<?php echo e(__('Edit Contact')); ?>">   <i class="ti ti-edit"></i><?php echo e(__('Edit')); ?></a>

                                                            <?php endif; ?>
                                                            <?php if(Gate::check('delete contact')): ?>
                                                            <?php echo Form::open(['method' => 'DELETE', 'route' => ['contact.destroy', $contact->id],'id'=>'user-'.$contact->id]); ?>

                                                                <a href="#" class="dropdown-item confirm_dialog"><i class="ti ti-trash"></i> <?php echo e(__('Delete')); ?></a>
                                                                <?php echo Form::close(); ?>



                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        <h3 class="mb-1 text-truncate"><?php echo e($contact->name); ?></h3>
                                        <h6 class="text-truncate text-muted d-flex align-items-center mb-4">
                                            <?php echo e($contact->email); ?>

                                        </h6>
                                        <div class="row">
                                            <div class="col-sm-6 mb-4">
                                                <p class="mb-0 text-muted text-sm"><?php echo e(__('Contact Number')); ?></p>
                                                <h6 class="mb-0"><?php echo e($contact->contact_number); ?> </h6>
                                            </div>
                                            <div class="col-sm-6 mb-4">
                                                <p class="mb-0 text-muted text-sm"><?php echo e(__('Created Date')); ?></p>
                                                <h6 class="mb-0"><?php echo e(dateFormat($contact->created_at)); ?></h6>
                                            </div>
                                            <div class="col-12 mb-4">
                                                <p class="mb-0 text-muted text-sm"><?php echo e(__('Subject')); ?></p>
                                                <h6 class="mb-0"><?php echo e($contact->subject); ?></h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/u417948420/domains/members-triumphwest.triumphtrained.com/public_html/main_file/resources/views/contact/index.blade.php ENDPATH**/ ?>