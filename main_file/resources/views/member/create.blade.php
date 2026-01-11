@extends('layouts.app')

@section('page-title')
    {{ __('Create Member') }}
@endsection

@section('breadcrumb')
    <ul class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('member.index') }}">{{ __('Member') }}</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{ __('Create Member') }}</li>
    </ul>
@endsection

@section('content')
    <div class="row">
        {{ Form::open(['url' => 'member', 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ __('Create Member') }}</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="form-group col-md-4">
                            {{ Form::label('first_name', __('First Name'), ['class' => 'form-label']) }} <span
                                class="text-danger">*</span>
                            {{ Form::text('first_name', null, ['class' => 'form-control', 'placeholder' => __('Enter first name'), 'required' => 'required']) }}
                        </div>
                        <div class="form-group col-md-4">
                            {{ Form::label('last_name', __('last Name'), ['class' => 'form-label']) }}
                            {{ Form::text('last_name', null, ['class' => 'form-control', 'placeholder' => __('Enter last name')]) }}
                        </div>
                        <div class="form-group col-md-4">
                            {{ Form::label('email', __('email'), ['class' => 'form-label']) }}
                            {{ Form::email('email', null, ['class' => 'form-control', 'placeholder' => __('Enter email')]) }}
                        </div>
                        <div class="form-group col-md-4">
                            {{ Form::label('password', __('Password'), ['class' => 'form-label']) }}
                            {{ Form::password('password', ['class' => 'form-control', 'placeholder' => __('Enter password'), 'required' => 'required', 'minlength' => '6']) }}

                        </div>
                        <div class="form-group col-md-4">
                            {{ Form::label('phone', __('Phone No.'), ['class' => 'form-label']) }} <span
                                class="text-danger">*</span>
                            {{ Form::text('phone', null, ['class' => 'form-control', 'placeholder' => __('Enter phone number'), 'required' => 'required']) }}
                            <small class="form-text text-muted">
                                {{ __('Please enter the number with country code. e.g., +91XXXXXXXXXX') }}
                            </small>
                        </div>
                        <div class="form-group col-md-4">
                            {{ Form::label('dob', __('dob'), ['class' => 'form-label']) }}
                            {{ Form::date('dob', null, ['class' => 'form-control']) }}
                        </div>
                        <div class="form-group col-md-4">
                            {{ Form::label('address', __('address'), ['class' => 'form-label']) }} <span
                                class="text-danger">*</span>
                            {{ Form::text('address', null, ['class' => 'form-control', 'placeholder' => __('Enter address')]) }}
                        </div>
                        <div class="form-group col-md-4">
                            {{ Form::label('gender', __('Gender'), ['class' => 'form-label']) }}<span
                                class="text-danger">*</span>
                            {{ Form::select('gender', ['Male' => 'Male', 'Female' => 'Female'], null, ['class' => 'form-control hidesearch basic-select', 'required' => 'required']) }}
                        </div>
                        <div class="form-group col-md-4">
                            {{ Form::label('image', __('image'), ['class' => 'form-label']) }}
                            {{ Form::file('image', ['class' => 'form-control']) }}
                        </div>
                        <div class="form-group col-md-4">
                            {{ Form::label('emergency_contact_information', __('Emergency Contact Information'), ['class' => 'form-label']) }}
                            {{ Form::textarea('emergency_contact_information', null, ['class' => 'form-control', 'placeholder' => __('Enter emergency contact information'), 'rows' => '2']) }}
                        </div>
                        <div class="form-group col-md-8">
                            {{ Form::label('notes', __('Notes'), ['class' => 'form-label']) }}
                            {{ Form::textarea('notes', null, ['class' => 'form-control', 'placeholder' => __('Enter note'), 'rows' => '1']) }}
                        </div>
                    </div>


                </div>
            </div>

        </div>

        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">{{ __('Assign Membership') }}</h4>
                    <div class="form-check form-switch">
                        <div class="form-check form-switch custom-switch-v1 mb-2">
                            <input type="hidden" name="membership_part" value="off">

                            <input type="checkbox" class="form-check-input input-secondary" name="membership_part"
                                id="toggleMembership">
                        </div>
                    </div>
                </div>
                <div class="card-body" id="membershipSection">
                    <div class="row">
                        <div class="form-group col-md-6">
                            {{ Form::label('start_date', __('Start Date'), ['class' => 'form-label']) }}
                            {{ Form::date('start_date', today(), ['class' => 'form-control', 'id' => 'start_date']) }}
                        </div>
                        <div class="form-group col-md-6">
                            {!! Form::label('plan_id', __('Plan'), ['class' => 'form-label']) !!}
                            {!! Form::select('plan_id', $membership, null, [
                                'class' => 'form-control basic-select',
                                'id' => 'plan_id',
                            ]) !!}
                        </div>

                        <div class="form-group col-md-6">
                            {{ Form::label('expiry_date', __('Expiry Date'), ['class' => 'form-label']) }}
                            {{ Form::date('expiry_date', null, ['class' => 'form-control', 'id' => 'expiry_date', 'readonly' => 'readonly']) }}
                        </div>
                        <div class="form-group col-md-6">
                            {{ Form::label('status', __('Status'), ['class' => 'form-label']) }}
                            {{ Form::select('status', ['Payment Pending' => __('Payment Pending'), 'Active' => __('Active')], null, ['class' => 'form-control basic-select', 'required' => 'required']) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-end">
            {{ Form::submit(__('Create'), ['class' => 'btn btn-secondary']) }}
        </div>
        {{ Form::close() }}
    </div>
@endsection


@push('script-page')
    {{-- <script>
        $(document).ready(function() {

            $(document).on('change', '#plan_id', function() {
                var plan_id = $('#plan_id').val();
                $.ajax({
                    url: "{{ route('getDurations') }}",
                    type: "GET",
                    data: {
                        plan_id: plan_id
                    },

                    success: function(data) {

                        var duration = parseInt(data.duration, 10);
                        var start_date = $('#start_date').val();
                        let startDate = new Date(start_date);

                        if (isNaN(startDate.getTime())) {
                            console.error(
                                "Invalid date format. Please provide a valid date.");
                        } else if (isNaN(duration)) {
                            console.error(
                                "Invalid duration format. Please provide a valid number."
                            );
                        } else {
                            startDate.setMonth(startDate.getMonth() + duration);

                            const year = startDate.getFullYear();
                            const month = String(startDate.getMonth() + 1).padStart(2, '0');
                            const day = String(startDate.getDate()).padStart(2, '0');

                            const formattedEndDate = `${year}-${month}-${day}`;

                            $('#expiry_date').val(formattedEndDate);
                        }
                    },


                });
            });
        });
    </script> --}}


    <script>
        $(document).ready(function() {
            let currentDuration = null;

            function calculateExpiryDate(start_date, duration) {
                let startDate = new Date(start_date);



                startDate.setMonth(startDate.getMonth() + duration);

                const year = startDate.getFullYear();
                const month = String(startDate.getMonth() + 1).padStart(2, '0');
                const day = String(startDate.getDate()).padStart(2, '0');

                const formattedEndDate = `${year}-${month}-${day}`;
                $('#expiry_date').val(formattedEndDate);
            }

            // Trigger when plan changes
            $(document).on('change', '#plan_id', function() {
                var plan_id = $(this).val();
                $.ajax({
                    url: "{{ route('getDurations') }}",
                    type: "GET",
                    data: {
                        plan_id: plan_id
                    },
                    success: function(data) {
                        currentDuration = parseInt(data.duration, 10);
                        let start_date = $('#start_date').val();
                        calculateExpiryDate(start_date, currentDuration);
                    }
                });
            });

            // Trigger when start date changes
            $(document).on('change', '#start_date', function() {
                let start_date = $(this).val();
                if (currentDuration !== null) {
                    calculateExpiryDate(start_date, currentDuration);
                }
            });
        });
    </script>


    <script>
        $(document).ready(function() {
            // Initially hide the card body
            $('#membershipSection').hide();

            $('#toggleMembership').change(function() {
                if ($(this).is(':checked')) {
                    $('#membershipSection').slideDown(); // Show with animation
                } else {
                    $('#membershipSection').slideUp(); // Hide with animation
                }
            });
        });
    </script>
@endpush
