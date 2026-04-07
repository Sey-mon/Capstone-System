{{-- Admin Navigation Partial --}}
<ul>
    @foreach(config('navigation.admin') as $navItem)
        @php
            $currentRouteName = request()->route()->getName();
            
            // Check if current route is the main route
            $isActive = request()->routeIs($navItem['route']);
            
            // If not, check if it's one of the related routes
            if (!$isActive && isset($navItem['related_routes'])) {
                foreach($navItem['related_routes'] as $relatedRoute) {
                    if ($currentRouteName === $relatedRoute) {
                        $isActive = true;
                        break;
                    }
                }
            }
        @endphp
        <li class="nav-item">
            <a href="{{ route($navItem['route']) }}" class="nav-link {{ $isActive ? 'active' : '' }}">
                <i class="{{ $navItem['icon'] }}"></i>
                <span class="nav-text">{{ $navItem['text'] }}</span>
            </a>
        </li>
    @endforeach
</ul>
