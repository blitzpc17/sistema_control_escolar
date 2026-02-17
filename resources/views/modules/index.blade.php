@extends('layouts.app')

@section('title','Módulos')
@section('crumbTitle','Módulos')
@section('crumbSub','Seguridad / Módulos')

@section('content')
<div class="card">
  <div class="section-head">
    <div class="title">
      <b>Módulos</b>
      <span>Catálogo del menú (drawer) + rutas + jerarquía</span>
    </div>
    <div class="actions">
      <button class="btn primary" id="btnNew"><i class="fa-solid fa-plus"></i> Nuevo</button>
    </div>
  </div>

  <div style="height:10px"></div>

  <div class="table-wrap">
    <table id="tblModules" class="display" style="width:100%">
      <thead>
      <tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>Ruta</th>
        <th>Icono</th>
        <th>Padre</th>
        <th>Menu</th>
        <th>Activo</th>
        <th>Acciones</th>
      </tr>
      </thead>
      <tbody>
      @foreach($modules as $m)
        @php
          $payload = [
            "id" => $m->id,
            "name" => $m->name,
            "route" => $m->route,
            "icon" => $m->icon,
            "parent_id" => $m->parent_id,
            "is_menu" => $m->is_menu ? 1 : 0,
            "is_active" => $m->is_active ? 1 : 0,
          ];
        @endphp
        <tr data-json='@json($payload, JSON_UNESCAPED_UNICODE)'>
          <td>{{ $m->id }}</td>
          <td>{{ $m->name }}</td>
          <td><code>{{ $m->route }}</code></td>
          <td><i class="fa-solid {{ $m->icon }}"></i> <span style="color:var(--muted)">{{ $m->icon }}</span></td>
          <td>{{ $m->parent?->name ?? '-' }}</td>
          <td>{{ $m->is_menu ? 'Sí' : 'No' }}</td>
          <td>{{ $m->is_active ? 'Sí' : 'No' }}</td>
          <td style="white-space:nowrap;">
            <button class="btn outline btnEdit" style="padding:8px 10px;border-radius:12px" title="Editar">
              <i class="fa-regular fa-pen-to-square"></i>
            </button>

            <form id="del-mod-{{ $m->id }}" action="{{ route('modules.destroy',$m->id) }}" method="POST" style="display:inline;">
              @csrf @method('DELETE')
              <button type="button" class="btn outline danger" style="padding:8px 10px;border-radius:12px"
                onclick="confirmBaja('del-mod-{{ $m->id }}','¿Dar de baja módulo?','{{ $m->name }}')">
                <i class="fa-regular fa-trash-can"></i>
              </button>
            </form>
          </td>
        </tr>
      @endforeach
      </tbody>
    </table>
  </div>
</div>

{{-- MODAL --}}
<div class="modal" id="mdlModule">
  <div class="backdrop" data-close="1"></div>
  <div class="dialog">
    <div class="dialog-head">
      <b id="mdlTitle">Nuevo módulo</b>
      <button class="close" type="button" data-close="1"><i class="fa-solid fa-xmark"></i></button>
    </div>

    <form id="frmModule" method="POST" action="{{ route('modules.store') }}" class="form" autocomplete="off">
      @csrf

      <div class="field col-6">
        <label>Nombre</label>
        <input type="text" name="name" id="m_name" required>
      </div>

      <div class="field col-6">
        <label>Ruta (ej: users, roles, modules)</label>
        <input type="text" name="route" id="m_route" placeholder="users">
      </div>

      <div class="field col-6">
        <label>Icono (FontAwesome class, ej: fa-users)</label>
        <input type="text" name="icon" id="m_icon" placeholder="fa-users">
      </div>

      <div class="field col-6">
        <label>Padre (si es submenú)</label>
        <select name="parent_id" id="m_parent">
          <option value="">(Sin padre)</option>
          @foreach($parents as $p)
            <option value="{{ $p->id }}">{{ $p->name }}</option>
          @endforeach
        </select>
      </div>

      <div class="field col-6">
        <label>¿Es menú?</label>
        <select name="is_menu" id="m_menu">
          <option value="1">Sí</option>
          <option value="0">No</option>
        </select>
      </div>

      <div class="field col-6">
        <label>Activo</label>
        <select name="is_active" id="m_active">
          <option value="1">Sí</option>
          <option value="0">No</option>
        </select>
      </div>

      <div class="form-footer">
        <button type="button" class="btn" data-close="1"><i class="fa-solid fa-ban"></i> Cancelar</button>
        <button type="submit" class="btn primary"><i class="fa-solid fa-floppy-disk"></i> Guardar</button>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
  $('#tblModules').DataTable({
    pageLength: 10,
    language: {
      search: "Buscar:",
      lengthMenu: "Mostrar _MENU_",
      info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
      paginate: { previous: "Anterior", next: "Siguiente" },
      zeroRecords: "No se encontraron registros",
      infoEmpty: "Sin registros"
    }
  });

  function openModal(){ $('#mdlModule').addClass('show'); $('body').css('overflow','hidden'); }
  function closeModal(){ $('#mdlModule').removeClass('show'); $('body').css('overflow','auto'); }
  $('#mdlModule [data-close="1"]').on('click', closeModal);

  $('#btnNew').on('click', function(){
    $('#mdlTitle').text('Nuevo módulo');
    $('#frmModule').attr('action', @json(route('modules.store')));
    $('#frmModule').find('input[name="_method"]').remove();

    $('#m_name,#m_route,#m_icon').val('');
    $('#m_parent').val('');
    $('#m_menu').val('1');
    $('#m_active').val('1');

    openModal();
  });

  $('#tblModules').on('click','.btnEdit', function(){
    const row = $(this).closest('tr');
    const data = JSON.parse(row.attr('data-json'));

    $('#mdlTitle').text('Editar módulo #' + data.id);
    $('#frmModule').attr('action', @json(url('/modules')) + '/' + data.id);
    if(!$('#frmModule input[name="_method"]').length){
      $('#frmModule').append('<input type="hidden" name="_method" value="PUT">');
    }

    $('#m_name').val(data.name || '');
    $('#m_route').val(data.route || '');
    $('#m_icon').val(data.icon || '');
    $('#m_parent').val(data.parent_id ? String(data.parent_id) : '');
    $('#m_menu').val(String(data.is_menu));
    $('#m_active').val(String(data.is_active));

    openModal();
  });
})();
</script>
@endpush
