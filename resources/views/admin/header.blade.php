@php
    // Determine current language safely for guests and authenticated users
    $settings = function_exists('settings') ? settings() : [];
@endphp
{{-- Example: set the html lang attribute or load language-specific assets --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_','-', $lang) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>
    {{-- Place your CSS/JS includes here. Example: --}}
    {{-- <link rel="stylesheet" href="{{ asset('css/app.css') }}"> --}}
</head>
<body>
    {{-- Header content (logo, top nav, etc.) can safely reference $lang --}}
    {{-- Example logo / brand --}}
    <header class="app-header">
        <div class="brand">
            <a href="{{ url('/') }}">{{ config('app.name', 'Laravel') }}</a>
        </div>
        {{-- Right-side items (auth-aware) --}}
        <nav class="top-nav">
            @auth
                <span class="user-name">{{ Auth::user()->name }}</span>
                {{-- Language indicator --}}
                <span class="lang">{{ strtoupper($lang) }}</span>
                <a href="{{ route('setting.index') }}">{{ __('Settings') }}</a>
                <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit">{{ __('Logout') }}</button>
                </form>
            @endauth

            @guest
                {{-- Guest links --}}
                <span class="lang">{{ strtoupper($lang) }}</span>
                <a href="{{ route('login') }}">{{ __('Login') }}</a>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}">{{ __('Register') }}</a>
                @endif
            @endguest
        </nav>
    </header>

<header class="pc-header">
    <div class="header-wrapper"><!-- [Mobile Media Block] start -->
        <div class="me-auto pc-mob-drp">
            <ul class="list-unstyled">
                <li class="pc-h-item header-mobile-collapse">
                    <a href="#" class="pc-head-link head-link-secondary ms-0" id="sidebar-hide">
                        <i class="ti ti-menu-2"></i>
                    </a>
                </li>
                <li class="pc-h-item pc-sidebar-popup">
                    <a href="#" class="pc-head-link head-link-secondary ms-0" id="mobile-collapse">
                        <i class="ti ti-menu-2"></i>
                    </a>
                </li>

            </ul>
        </div>
        <!-- [Mobile Media Block end] -->
        <div class="ms-auto">
            <ul class="list-unstyled">

                <li class="dropdown pc-h-item" data-bs-toggle="tooltip" data-bs-original-title="{{__('Language')}}" data-bs-placement="bottom">
                    <a class="pc-head-link head-link-primary dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown"
                        href="#" role="button" aria-haspopup="false" aria-expanded="false" >
                        <i class="ti ti-language"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end pc-h-dropdown">
                        @foreach($languages as $language)
                            @if($language!='en')
                                <a href="{{route('language.change',$language)}}" class="dropdown-item {{ $userLang==$language?'active':'' }}">
                                    <span class="align-middle">{{ucfirst( $language)}}</span>
                                </a>
                            @endif
                        @endforeach


                    </div>
                </li>
                @if (\Auth::user()->type == 'super admin' || \Auth::user()->type == 'owner')
                    <li class="dropdown pc-h-item pc-mega-menu" data-bs-toggle="tooltip" data-bs-original-title="{{__('Theme Settings')}}" data-bs-placement="bottom">
                        <a href="#" class="pc-head-link head-link-secondary dropdown-toggle arrow-none me-0"
                            data-bs-toggle="offcanvas" data-bs-target="#offcanvas_pc_layout">
                            <i class="ti ti-settings"></i>
                        </a>
                    </li>
                @endif
                <li class="dropdown pc-h-item header-user-profile">
                    <a class="pc-head-link head-link-primary dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown"
                        href="#" role="button" aria-haspopup="false" aria-expanded="false">
                        <img src="{{(!empty($users->profile)? $profile.'/'.$users->profile : $profile.'/avatar.png')}}" alt="user-image" class="user-avtar" />
                        <span>
                            <i class="ti ti-user-check"></i>
                        </span>
                    </a>
                    <div class="dropdown-menu dropdown-user-profile dropdown-menu-end pc-h-dropdown">
                        <div class="dropdown-header">
                            <h4>
                                {{ __('Good Morning') }},
                                <span class="small text-muted">{{\Auth::user()->name}}</span>
                            </h4>
                            <p class="text-muted">{{\Auth::user()->type}}</p>

                            <div class="profile-notification-scroll position-relative"
                                style="max-height: calc(100vh - 280px)">
                                <hr />
                                {!! Form::open(['method' => 'DELETE', 'route' => ['setting.account.delete']]) !!}
                                <a href="#" class="dropdown-item common_confirm_dialog" data-actions="Account">
                                    <i class="ti ti-user-x"></i>
                                    <span>{{ __('Account Delete') }}</span>
                                </a>
                                {!! Form::close() !!}
                                @impersonating()
                                <a href="{{ route('impersonate.leave') }}" class="dropdown-item" data-actions="Account">
                                    <i class="ti ti-transfer-out"></i>
                                    <span>{{ __('Leave') }}</span>
                                </a>
                                @endImpersonating
                                <a href="{{ route('logout') }}" class="dropdown-item"  onclick="event.preventDefault(); document.getElementById('frm-logout').submit();">
                                    <i class="ti ti-logout"></i>
                                    <span>{{ __('Logout') }}</span>
                                    <form id="frm-logout" action="{{ route('logout') }}" method="POST" class="d-none">
                                        {{ csrf_field() }}
                                    </form>
                                </a>
                            </div>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</header>
