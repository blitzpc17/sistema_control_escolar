(function(){
  // ===== CSRF for AJAX
  function csrf(){
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
  }
  if (window.jQuery){
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': csrf() } });
  }

  function isMobile(){ return window.matchMedia('(max-width: 980px)').matches; }

  // THEME
  function applyTheme(theme){
    document.body.setAttribute('data-theme', theme);
    localStorage.setItem('cms_theme', theme);
    const isDark = theme === 'dark';
    const icon = document.querySelector('#btnTheme i');
    if(icon){
      icon.classList.toggle('fa-moon', !isDark);
      icon.classList.toggle('fa-sun', isDark);
    }
  }
  function toggleTheme(){
    const current = document.body.getAttribute('data-theme') || 'light';
    applyTheme(current === 'light' ? 'dark' : 'light');
  }

  // SIDEBAR
  function openSidebar(){
    document.getElementById('sidebar')?.classList.add('open');
    document.getElementById('overlay')?.classList.add('show');
  }
  function closeSidebar(){
    document.getElementById('sidebar')?.classList.remove('open');
    document.getElementById('overlay')?.classList.remove('show');
  }
  function setCollapsed(collapsed){
    if(isMobile()) return;
    const sb = document.getElementById('sidebar');
    if(!sb) return;
    sb.classList.toggle('collapsed', !!collapsed);
    localStorage.setItem('cms_sidebar_collapsed', collapsed ? '1' : '0');
    if(collapsed){
      document.querySelectorAll('.nav .nav-toggle').forEach(b => b.classList.remove('open'));
    }
  }
  function toggleDesktopCollapse(){
    if(isMobile()) return;
    const sb = document.getElementById('sidebar');
    setCollapsed(!sb.classList.contains('collapsed'));
  }

  function syncDesktopOverlay(){
    const ov = document.getElementById('overlay');
    const sb = document.getElementById('sidebar');
    if(!ov || !sb) return;

    if(isMobile()){
      ov.classList.toggle('show', sb.classList.contains('open'));
      return;
    }
    const expanded = !sb.classList.contains('collapsed');
    ov.classList.toggle('show', expanded);
  }

  // SWEETALERT full (no toast)
  function infoAlert(title, text){
    return Swal.fire({ icon:'info', title, text, confirmButtonText:'OK' });
  }

  document.addEventListener('DOMContentLoaded', function(){
    // theme
    applyTheme(localStorage.getItem('cms_theme') || 'light');

    // restore collapsed
    const savedCollapsed = localStorage.getItem('cms_sidebar_collapsed') === '1';
    if(!isMobile()) setCollapsed(savedCollapsed);

    // nav toggles
    document.getElementById('nav')?.addEventListener('click', function(e){
      const btn = e.target.closest('.nav-toggle');
      if(!btn) return;

      // si está colapsado en desktop, al click expande y abre ese submenu
      const sb = document.getElementById('sidebar');
      if(!isMobile() && sb.classList.contains('collapsed')){
        setCollapsed(false);
        setTimeout(() => btn.classList.add('open'), 80);
        return;
      }
      btn.classList.toggle('open');
    });

    // hamburger
    document.getElementById('btnHamburger')?.addEventListener('click', function(){
      const sb = document.getElementById('sidebar');
      if(isMobile()){
        sb.classList.contains('open') ? closeSidebar() : openSidebar();
      }else{
        toggleDesktopCollapse();
      }
    });

    // dropdown
    const dropbtn = document.getElementById('dropbtn');
    const menu = document.getElementById('menu');
    dropbtn?.addEventListener('click', function(e){
      e.stopPropagation();
      if(menu) menu.style.display = (menu.style.display === 'block' ? 'none' : 'block');
    });
    document.addEventListener('click', function(){
      if(menu) menu.style.display = 'none';
    });

    // theme buttons
    document.getElementById('btnTheme')?.addEventListener('click', function(e){
      e.preventDefault();
      toggleTheme();
      if(menu) menu.style.display = 'none';
    });
    document.getElementById('menuTheme')?.addEventListener('click', function(e){
      e.preventDefault();
      toggleTheme();
      if(menu) menu.style.display = 'none';
    });

    // click overlay: mobile cierra / desktop colapsa
    document.getElementById('overlay')?.addEventListener('click', function(){
      if(isMobile()) closeSidebar();
      else setCollapsed(true);
    });

    // click fuera desktop => colapsar (cuando esté expandido)
    document.addEventListener('click', function(e){
      if(isMobile()) return;
      const sb = document.getElementById('sidebar');
      const clickedSidebar = e.target.closest('#sidebar');
      const clickedHamb = e.target.closest('#btnHamburger');
      if(!clickedSidebar && !clickedHamb){
        if(sb && !sb.classList.contains('collapsed')) setCollapsed(true);
      }
    });

    // observer para overlay
    const sb = document.getElementById('sidebar');
    if(sb){
      const ob = new MutationObserver(syncDesktopOverlay);
      ob.observe(sb, { attributes:true, attributeFilter:['class'] });
    }
    syncDesktopOverlay();

    // resize rules
    window.addEventListener('resize', function(){
      if(isMobile()){
        document.getElementById('sidebar')?.classList.remove('collapsed');
        document.getElementById('overlay')?.classList.toggle('show', document.getElementById('sidebar')?.classList.contains('open'));
      }else{
        closeSidebar();
        setCollapsed(localStorage.getItem('cms_sidebar_collapsed') === '1');
      }
    });

    // demo
    document.getElementById('btnQuickDemo1')?.addEventListener('click', () => infoAlert('Demo', 'Aquí conectas tu acción real.'));
    document.getElementById('btnQuickDemo2')?.addEventListener('click', () => infoAlert('Demo', 'Aquí conectas exportación real.'));
  });
})();
