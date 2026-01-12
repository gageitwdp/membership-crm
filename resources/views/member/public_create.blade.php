@extends('layouts.app')

@section('content')
<div class="container">
    <h2>{{ __('Create Member') }}</h2>

    @if (session('success'))
        <div class="alert alert-success">{!! session('success') !!}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('member.public.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <label for="first_name" class="form-label">{{ __('First name') }}</label>
            <input id="first_name" name="first_name" value="{{ old('first_name') }}" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="last_name" class="form-label">{{ __('Last name') }}</label>
            <input id="last_name" name="last_name" value="{{ old('last_name') }}" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">{{ __('Email') }}</label>
            <input id="email" name="email" value="{{ old('email') }}" type="email" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="phone" class="form-label">{{ __('Phone') }}</label>
            <input id="phone" name="phone" value="{{ old('phone') }}" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">{{ __('Password') }}</label>
            <input id="password" name="password" type="password" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="password_confirmation" class="form-label">{{ __('Confirm Password') }}</label>
            <input id="password_confirmation" name="password_confirmation" type="password" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="dob" class="form-label">{{ __('Date of birth') }}</label>
            <input id="dob" name="dob" type="date" value="{{ old('dob') }}" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="address" class="form-label">{{ __('Address') }}</label>
            <textarea id="address" name="address" class="form-control" required>{{ old('address') }}</textarea>
        </div>

        <div class="mb-3">
            <label for="gender" class="form-label">{{ __('Gender') }}</label>
            <select id="gender" name="gender" class="form-control">
                <option value="">{{ __('Select') }}</option>
                <option value="Male" {{ old('gender')=='Male' ? 'selected' : '' }}>{{ __('Male') }}</option>
                <option value="Female" {{ old('gender')=='Female' ? 'selected' : '' }}>{{ __('Female') }}</option>
                <option value="Other" {{ old('gender')=='Other' ? 'selected' : '' }}>{{ __('Other') }}</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="image" class="form-label">{{ __('Profile image') }}</label>
            <input id="image" name="image" type="file" class="form-control">
        </div>

        @if (!empty($membership) && $membership->count() > 1)
        <div class="mb-3">
            <label for="plan_id" class="form-label">{{ __('Membership plan (optional)') }}</label>
            <select id="plan_id" name="plan_id" class="form-control">
                @foreach ($membership as $id => $name)
                    <option value="{{ $id }}" {{ old('plan_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                @endforeach
            </select>
        </div>
        @endif

        <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
    </form>
</div>
@endsection
