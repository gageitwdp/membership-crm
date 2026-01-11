{!! Form::open(['url' => route('membership.store'), 'method' => 'post', 'enctype' => 'multipart/form-data']) !!}


<div class="modal-body">
    <div class="row">

        <div class="form-group col-md-6">
            {{ Form::label('member_id', __('Member'), ['class' => 'form-label']) }}
            {{ Form::select('member_id', $members, null, ['class' => 'form-control basic-select', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {!! Form::label('plan_id', __('Plan'), ['class' => 'form-label']) !!}
            {!! Form::select('plan_id', $plans, null, [
                'class' => 'form-control basic-select',
                'id' => 'plan_id',
                'required' => 'required',
            ]) !!}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('start_date', __('Start Date'), ['class' => 'form-label']) }}
            {{ Form::date('start_date', today(), ['class' => 'form-control', 'id' => 'start_date', 'required' => 'required', 'readonly' => 'readonly']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('expiry_date', __('Expiry Date'), ['class' => 'form-label']) }}
            {{ Form::date('expiry_date', null, ['class' => 'form-control', 'id' => 'expiry_date', 'required' => 'required','readonly' => 'readonly']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('status', __('Status'), ['class' => 'form-label']) }}
            {{ Form::select('status', ['Active' => __('Active'), 'Expired' => __('Expired'), 'Suspended' => __('Suspended')], null, ['class' => 'form-control basic-select', 'required' => 'required']) }}
        </div>
    </div>
</div>
<div class="modal-footer">
    {{ Form::submit(__('Create'), ['class' => 'btn btn-secondary']) }}
</div>
{{ Form::close() }}


<script>
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
                        const month = String(startDate.getMonth() + 1).padStart(2,'0');
                        const day = String(startDate.getDate()).padStart(2, '0');

                        const formattedEndDate = `${year}-${month}-${day}`;

                        $('#expiry_date').val(formattedEndDate);
                    }
                },


            });
        });
    });
</script>

