{{-- Dynamic Navigation Partial --}}
@php
    $userRole = strtolower(Auth::user()->role->role_name ?? 'guest');
@endphp

@switch($userRole)
    @case('admin')
        @include('partials.admin-navigation')
        @break
    @case('nutritionist')
        @include('partials.nutritionist-navigation')
        @break
    @case('parent')
        @include('partials.parent-navigation')
        @break
    @default
        {{-- Default navigation for unknown roles --}}
        <ul>
            <li class="nav-item">
                <a href="{{ route('login') }}" class="nav-link">
                    <i class="fas fa-sign-in-alt"></i>
                    <span class="nav-text">Login</span>
                </a>
            </li>
        </ul>
@endswitch
