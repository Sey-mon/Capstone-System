{{-- Admin Navigation Partial --}}
<ul>
    @foreach(config('navigation.admin') as $navItem)
        <li class="nav-item">
            <a href="{{ route($navItem['route']) }}" class="nav-link {{ request()->routeIs($navItem['route']) ? 'active' : '' }}">
                <i class="{{ $navItem['icon'] }}"></i>
                <span class="nav-text">{{ $navItem['text'] }}</span>
            </a>
        </li>
    @endforeach
</ul>
