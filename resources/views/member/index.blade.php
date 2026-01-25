@extends('layouts.app')
@section('page-title')
    {{ __('Member') }}
@endsection
@section('breadcrumb')
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}">
                {{ __('Dashboard') }}
            </a>
        </li>
        <li class="breadcrumb-item active">
            <a href="#">
                {{ __('Member') }}
            </a>
        </li>
    </ul>
@endsection
@section('card-action-btn')
    @if (Gate::check('create member'))
        <a class="btn btn-secondary btn-sm ml-20" href="{{ route('member.create') }}">
            <i class="ti-plus mr-5"></i>
            {{ __('Create Member') }}
        </a>
    @endif
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card table-card">
                <div class="card-header">
                    <div class="row align-items-center g-2">
                        <div class="col">
                            <h5>
                                {{ __('Member') }}
                            </h5>
                        </div>
                        @if (Gate::check('create member'))
                            <div class="col-auto">
                                <a class="btn btn-secondary"href="{{ route('member.create') }}"
                                    data-title="{{ __('Create Member') }} ">
                                    <i class="ti ti-circle-plus align-text-bottom"></i>
                                    {{ __('Create Member') }}
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="dt-responsive table-responsive">
                        <table class="table table-hover advance-datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Profile') }}</th>
                                    <th>{{ __('Member ID') }}</th>
                                    <th>{{ __('Email') }}</th>
                                    <th>{{ __('Phone No.') }}</th>
                                    <th>{{ __('Membership') }}</th>
                                    <th>{{ __('Expiry Date') }}</th>
                                    <th>{{ __('Gender') }}</th>
                                    @if (Gate::check('edit member') || Gate::check('delete member'))
                                        <th>{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($members as $member)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0 wid-40">
                                                    <img class="img-radius img-fluid wid-40"
                                                        src="{{ !empty($member->image) ? asset(Storage::url('upload/member')) . '/' . $member->image : asset(Storage::url('upload/profile')) . '/avatar.png' }}"
                                                        alt="User image">
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h5 class="mb-1">
                                                        {{ $member->first_name }} {{ $member->last_name }}
                                                        @if($member->is_parent == 1)
                                                            <span class="badge bg-primary">{{ __('Parent') }}</span>
                                                        @elseif($member->parent_member_id > 0)
                                                            <span class="badge bg-info">{{ __('Child') }}</span>
                                                        @endif
                                                    </h5>
                                                    @if($member->parent_member_id > 0 && $member->parentMember)
                                                        <small class="text-muted">{{ __('Parent') }}: {{ $member->parentMember->first_name }} {{ $member->parentMember->last_name }}</small>
                                                    @endif
                                                </div>
                                            </div>

                                        </td>
                                        <td> {{ memberPrefix() . $member->member_id }}</td>


                                        <td>{{ $member->email }} </td>
                                        <td>{{ $member->phone }} </td>
                                        <td>{{ !empty($member->membershipLates) && !empty($member->membershipLates->plans) ? $member->membershipLates->plans->plan_name: '-' }}
                                        </td>
                                        <td>{{ !empty($member->membershipLates) ? dateFormat($member->membershipLates->expiry_date) : '-' }}
                                        </td>
                                        <td>{{ $member->gender }} </td>
                                        @if (Gate::check('edit member') || Gate::check('delete member'))
                                            <td>
                                                <div class="cart-action">
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['member.destroy', $member->id]]) !!}
                                                    @can('show member')
                                                        <a class="avtar avtar-xs btn-link-warning text-warning"
                                                            data-bs-toggle="tooltip"
                                                            data-bs-original-title="{{ __('Show') }}"
                                                            href="{{ route('member.show', Illuminate\Support\Facades\Crypt::encrypt($member->id)) }}">
                                                            <i data-feather="eye"></i></a>
                                                    @endcan
                                                    @can('edit member')
                                                        <a class="avtar avtar-xs btn-link-secondary text-secondary"
                                                            data-bs-toggle="tooltip"
                                                            data-bs-original-title="{{ __('Edit') }}"
                                                            href="{{ route('member.edit', Illuminate\Support\Facades\Crypt::encrypt($member->id)) }}">
                                                            <i data-feather="edit"></i>
                                                        </a>
                                                    @endcan
                                                    @can('delete member')
                                                        <a class=" avtar avtar-xs btn-link-danger text-danger confirm_dialog"
                                                            data-bs-toggle="tooltip"
                                                            data-bs-original-title="{{ __('Detete') }}" href="#"> <i
                                                                data-feather="trash-2"></i></a>
                                                    @endcan
                                                    {!! Form::close() !!}
                                                </div>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
