@props(['item'])

@if (!empty($item['children']))
    @php
        $activePatterns = collect($item['children'])->pluck('active')->filter()->all();
        $isActive = !empty($activePatterns) && request()->routeIs($activePatterns);
    @endphp
    <li class="nav-item dropdown {{ $isActive ? 'active' : '' }}">
        <a class="nav-link dropdown-toggle" href="#navbar-{{ \Illuminate\Support\Str::slug($item['label']) }}"
            data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false">
            @if (!empty($item['icon']))
                <span class="nav-link-icon"><i class="bi {{ $item['icon'] }}"></i></span>
            @endif
            <span class="nav-link-title">{{ $item['label'] }}</span>
        </a>
        <div class="dropdown-menu" data-bs-popper="none">
            @foreach ($item['children'] as $child)
                <a href="{{ route($child['route']) }}"
                    class="dropdown-item {{ !empty($child['active']) && request()->routeIs($child['active']) ? 'active' : '' }}">
                    {{ $child['label'] }}
                </a>
            @endforeach
        </div>
    </li>
@else
    <li class="nav-item {{ !empty($item['active']) && request()->routeIs($item['active']) ? 'active' : '' }}">
        <a class="nav-link" href="{{ route($item['route']) }}">
            @if (!empty($item['icon']))
                <span class="nav-link-icon"><i class="bi {{ $item['icon'] }}"></i></span>
            @endif
            <span class="nav-link-title">{{ $item['label'] }}</span>
        </a>
    </li>
@endif
