@extends('layouts.app')

@section('page-title')
    {{ __('Edit Member') }}
@endsection

@section('breadcrumb')
    <ul class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('member.index') }}">{{ __('Member') }}</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{ __('Edit Member') }}</li>
    </ul>
@endsection

@section('content')
    <div class="row">
        {{ Form::model($member, ['route' => ['member.update', $member->id], 'method' => 'PUT', 'enctype' => 'multipart/form-data']) }}

        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ __('Member Details') }}</h4>
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
                        <div class="form-group col-md-12">
                            {{ Form::label('notes', __('Notes'), ['class' => 'form-label']) }}
                            {{ Form::textarea('notes', null, ['class' => 'form-control', 'placeholder' => __('Enter note'), 'rows' => '1']) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>


        @if (empty($plan->plan_id))
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title">{{ __('Assign Membership') }}</h4>
                        <div class="form-check form-switch">
                            <div class="form-check form-switch custom-switch-v1 mb-2">
                                <input type="checkbox" class="form-check-input input-secondary" name="membership_part"
                                    id="toggleMembership"
                                    {{ old('membership_part') || (isset($member) && $member->membership_part === 'on') || !isset($member) ? 'checked' : '' }}>

                            </div>
                        </div>
                    </div>

                    <div class="card-body" id="membershipSection">
                        <div class="row">
                            <div class="form-group col-md-6">
                                {{ Form::label('start_date', __('Start Date'), ['class' => 'form-label']) }}
                                {{ Form::date(
                                    'start_date',
                                    !empty($plan) ? $plan->start_date : \Illuminate\Support\Carbon::today()->toDateString(),
                                    [
                                        'class' => 'form-control',
                                        'id' => 'start_date',
                                        'required' => 'required',
                                    ],
                                ) }}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('plan_id', __('Plan'), ['class' => 'form-label']) !!}
                                {!! Form::select('plan_id', $membership, !empty($plan) ? $plan->plan_id : '', [
                                    'class' => 'form-control basic-select',
                                    'id' => 'plan_id',
                                ]) !!}
                            </div>

                            <div class="form-group col-md-6">
                                {{ Form::label('expiry_date', __('Expiry Date'), ['class' => 'form-label']) }}
                                {{ Form::date('expiry_date', !empty($plan) ? $plan->expiry_date : '', [
                                    'class' => 'form-control',
                                    'id' => 'expiry_date',
                                    'readonly' => 'readonly',
                                    'required' => 'required',
                                    'disabled' => true,
                                ]) }}
                            </div>
                            <div class="form-group col-md-6">
                                {{ Form::label('status', __('Status'), ['class' => 'form-label']) }}
                                {{ Form::select('status', ['Active' => __('Active'), 'Expired' => __('Expired')], !empty($plan) ? $plan->status : '', ['class' => 'form-control basic-select', 'required' => 'required']) }}
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        @endif

        <div class=" text-end">
            {{ Form::submit(__('Update'), ['class' => 'btn btn-secondary']) }}
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

                if (isNaN(startDate.getTime())) {
                    console.error("Invalid start date.");
                    return;
                }

                if (isNaN(duration)) {
                    console.error("Invalid duration.");
                    return;
                }

                startDate.setMonth(startDate.getMonth() + duration);

                const year = startDate.getFullYear();
                const month = String(startDate.getMonth() + 1).padStart(2, '0');
                const day = String(startDate.getDate()).padStart(2, '0');

                const formattedEndDate = `${year}-${month}-${day}`;
                $('#expiry_date').prop('disabled', false).val(formattedEndDate);
            }

            function fetchAndSetDuration(plan_id, start_date) {
                if (!plan_id) {
                    $('#expiry_date').val('').prop('disabled', true);
                    return;
                }

                $.ajax({
                    url: "{{ route('getDurations') }}",
                    type: "GET",
                    data: {
                        plan_id: plan_id
                    },
                    success: function(data) {
                        currentDuration = parseInt(data.duration, 10);
                        calculateExpiryDate(start_date, currentDuration);
                    }
                });
            }

            // On plan change
            $('#plan_id').on('change', function() {
                const planId = $(this).val();
                const startDate = $('#start_date').val();
                fetchAndSetDuration(planId, startDate);
            });

            // On start date change
            $('#start_date').on('change', function() {
                const startDate = $(this).val();
                if (currentDuration !== null) {
                    calculateExpiryDate(startDate, currentDuration);
                } else {
                    const planId = $('#plan_id').val();
                    if (planId) {
                        fetchAndSetDuration(planId, startDate);
                    }
                }
            });

            // On edit load, if values are present
            const existingPlanId = $('#plan_id').val();
            const existingStartDate = $('#start_date').val();
            if (existingPlanId && existingStartDate) {
                fetchAndSetDuration(existingPlanId, existingStartDate);
            } else {
                $('#expiry_date').val('').prop('disabled', true);
            }
        });
    </script>





    <script>
        $(document).ready(function() {
            if ($('#toggleMembership').is(':checked')) {
                $('#membershipSection').show();
            } else {
                $('#membershipSection').hide();
            }

            $('#toggleMembership').change(function() {
                if ($(this).is(':checked')) {
                    $('#membershipSection').slideDown();
                } else {
                    $('#membershipSection').slideUp();
                }
            });
        });
    </script>
@endpush
