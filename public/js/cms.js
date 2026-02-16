(function(){
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
  }
  function toggleTheme(){
    const current = document.body.getAttribute('data-theme') || 'light';
    applyTheme(current === 'light' ? 'dark' : 'light');
  }

  // SIDEBAR
  function openSidebar(){
    $('#sidebar').addClass('open');
    $('#overlay').addClass('show');
  }
  function closeSidebar(){
    $('#sidebar').removeClass('open');
    $('#overlay').removeClass('show');
  }
  function setCollapsed(collapsed){
    if(isMobile()) return;
    $('#sidebar').toggleClass('collapsed', !!collapsed);
    localStorage.setItem('cms_sidebar_collapsed', collapsed ? '1' : '0');
    if(collapsed) $('.nav .nav-toggle').removeClass('open');
  }
  function toggleDesktopCollapse(){
    if(isMobile()) return;
    setCollapsed(!$('#sidebar').hasClass('collapsed'));
  }

  // ✅ MODAL helpers (genérico)
  function openModal($m){
    $m.addClass('show');
    $('body').css('overflow','hidden');

    // ✅ Si hay select2 dentro del modal, inicializa con dropdown dentro del modal (evita overlay/z-index)
    $m.find('select.select2modal, select[data-select2modal="1"]').each(function(){
      const $sel = $(this);
      if($sel.data('select2')) return;
      $sel.select2({ width:'100%', dropdownParent: $m.find('.dialog') });
    });
  }
  function closeModal($m){
    // destruir select2 modal
    $m.find('select.select2modal, select[data-select2modal="1"]').each(function(){
      if($(this).data('select2')) $(this).select2('destroy');
    });

    $m.removeClass('show');
    $('body').css('overflow','auto');
  }

  // GLOBALS
  window.CMS = {
    openModalById: function(id){ openModal($('#'+id)); },
    closeModalById: function(id){ closeModal($('#'+id)); },
    toggleTheme
  };

  document.addEventListener('DOMContentLoaded', function(){
    // theme
    applyTheme(localStorage.getItem('cms_theme') || 'light');

    // restore collapsed (desktop)
    if(!isMobile()) setCollapsed(localStorage.getItem('cms_sidebar_collapsed') === '1');

    // hamburger
    $('#btnHamburger').on('click', function(){
      if(isMobile()){
        $('#sidebar').hasClass('open') ? closeSidebar() : openSidebar();
      }else{
        toggleDesktopCollapse();
      }
    });

    // overlay click
    $('#overlay').on('click', function(){
      if(isMobile()) closeSidebar();
      else setCollapsed(true);
    });

    // dropdown
    $('#dropbtn').on('click', function(e){ e.stopPropagation(); $('#menu').toggle(); });
    $(document).on('click', function(){ $('#menu').hide(); });

    // theme menu
    $('#menuTheme, #btnTheme').on('click', function(e){
      e.preventDefault();
      toggleTheme();
      $('#menu').hide();
    });

    // nav toggles
    $('#nav').on('click', '.nav-toggle', function(){
      const $btn = $(this);
      if(!isMobile() && $('#sidebar').hasClass('collapsed')){
        setCollapsed(false);
        setTimeout(() => $btn.addClass('open'), 80);
        return;
      }
      $btn.toggleClass('open');
    });

    // ✅ cierres de modal genéricos: cualquier elemento con data-close="1"
    $(document).on('click', '[data-close="1"]', function(){
      const $m = $(this).closest('.modal');
      if($m.length) closeModal($m);
    });

    // ESC cierra modal
    $(document).on('keydown', function(e){
      if(e.key === 'Escape'){
        const $m = $('.modal.show').last();
        if($m.length) closeModal($m);
      }
    });
  });
})();
