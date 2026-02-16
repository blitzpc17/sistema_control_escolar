<aside class="sidebar" id="sidebar">
  <div class="brand">
    <img class="logo-img" src="{{ asset('images/logo.png') }}" alt="Logo"
         onerror="this.style.display='none';">
    <div class="meta">
      <b>CMS Escolar</b>
      <span>Panel administrativo</span>
    </div>
  </div>

  <div class="side-scroll">
    <div class="nav-group-title">Navegación</div>

    <nav class="nav" id="nav">
      @php
        $current = request()->path(); // ej: dashboard, users, roles...
        $isActive = function($route) use ($current){
          if(!$route) return false;
          $route = ltrim($route,'/');
          return $current === $route || str_starts_with($current, $route.'/');
        };

        $iconClass = function($icon){
          $icon = trim((string)$icon);
          if($icon === '') return 'fa-circle';
          // si ya trae fa-... déjalo, si no, agrega prefijo
          if(str_starts_with($icon, 'fa-')) return $icon;
          // si viene "users-gear" -> "fa-users-gear"
          return 'fa-'.$icon;
        };
      @endphp

      @foreach(($drawerMenu ?? []) as $m)
        @php
          $hasChildren = !empty($m['children']);
          $parentKey = $m['key'] ?? Str::slug($m['name'] ?? 'menu');
          $parentIcon = $iconClass($m['icon'] ?? 'fa-circle');
        @endphp

        @if(!$hasChildren)
          <a href="{{ !empty($m['route']) ? url($m['route']) : '#' }}"
             class="{{ $isActive($m['route'] ?? null) ? 'active' : '' }}"
             title="{{ $m['name'] ?? '' }}">
            <div class="left">
              <i class="fa-solid {{ $parentIcon }}"></i>
              <span>{{ $m['name'] ?? '' }}</span>
            </div>
          </a>
        @else
          @php
            $childActive = false;
            foreach($m['children'] as $c){
              if($isActive($c['route'] ?? null)) { $childActive = true; break; }
            }
          @endphp

          <button type="button"
                  class="nav-toggle {{ $childActive ? 'parent-active open' : '' }}"
                  data-parent="{{ $parentKey }}"
                  title="{{ $m['name'] ?? '' }}">
            <div class="left">
              <i class="fa-solid {{ $parentIcon }}"></i>
              <span>{{ $m['name'] ?? '' }}</span>
            </div>
            <i class="fa-solid fa-chevron-right caret"></i>
          </button>

          <div class="submenu" data-parent="{{ $parentKey }}">
            @foreach($m['children'] as $c)
              @php $childIcon = $iconClass($c['icon'] ?? 'fa-circle'); @endphp
              <a href="{{ !empty($c['route']) ? url($c['route']) : '#' }}"
                 class="{{ $isActive($c['route'] ?? null) ? 'active' : '' }}"
                 data-parent="{{ $parentKey }}"
                 title="{{ $c['name'] ?? '' }}">
                <div class="left">
                  <i class="fa-solid {{ $childIcon }}"></i>
                  <span>{{ $c['name'] ?? '' }}</span>
                </div>
              </a>
            @endforeach
          </div>
        @endif
      @endforeach
    </nav>
  </div>

  <div class="side-footer">
    <div class="pill">
      <div class="userbox">
        <div class="avatar">{{ strtoupper(substr(($authUser->name ?? 'U'),0,1)) }}</div>
        <div class="userinfo">
          <b>{{ $authUser->name ?? 'Usuario' }}</b>
          <span>{{ $authUser->role->description ?? ($authUser->role->name ?? '') }}</span>
        </div>
      </div>

      <button class="iconbtn" id="btnTheme" title="Cambiar tema">
        <i class="fa-solid fa-moon"></i>
      </button>
    </div>
  </div>
</aside>
