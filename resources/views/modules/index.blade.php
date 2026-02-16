@extends('layouts.app')

@section('title','Módulos')
@section('crumbTitle','Módulos')
@section('crumbSub','Seguridad / Módulos')

@section('content')
<div class="card">
  <div class="section-head">
    <div class="title">
      <b>Módulos</b>
      <span>Catálogo + jerarquía (parent/child) + baja lógica</span>
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
        <th>Key</th>
        <th>Nombre</th>
        <th>Ruta</th>
        <th>Icon</th>
        <th>Parent</th>
        <th>Menu</th>
        <th>Activo</th>
        <th>Acciones</th>
      </tr>
      </thead>
      <tbody>
      @php
        $parentsMap = \App\Models\Module::select('id','name')->pluck('name','id')->toArray();
      @endphp
      @foreach($modules as $m)
        <tr data-json='@json([
          "id"=>$m->id,"key"=>$m->key,"name"=>$m->name,"route"=>$m->route,
          "icon"=>$m->icon,"parent_id"=>$m->parent_id,"sort_order"=>$m->sort_order,
          "is_menu"=>$m->is_menu?1:0,"is_active"=>$m->is_active?1:0
        ])'>
          <td>{{ $m->id }}</td>
          <td>{{ $m->key }}</td>
          <td>{{ $m->name }}</td>
          <td>{{ $m->route }}</td>
          <td>{{ $m->icon }}</td>
          <td>{{ $m->parent_id ? ($parentsMap[$m->parent_id] ?? $m->parent_id) : '-' }}</td>
          <td>{{ $m->is_menu ? 'Sí' : 'No' }}</td>
          <td>{{ $m->is_active ? 'Sí' : 'No' }}</td>
          <td style="white-space:nowrap;">
            <button class="btn outline btnEdit" style="padding:8px 10px;border-radius:12px">
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

<div class="modal" id="mdlModule">
  <div class="backdrop" data-close="1"></div>
  <div class="dialog">
    <div class="dialog-head">
      <b id="mdlTitle">Nuevo módulo</b>
      <button class="close" type="button" data-close="1"><i class="fa-solid fa-xmark"></i></button>
    </div>

    <form id="frmModule" method="POST" action="{{ route('modules.store') }}" class="form" autocomplete="off">
      @csrf

      <div class="field col-4">
        <label>Key</label>
        <input type="text" name="key" id="m_key" required>
      </div>

      <div class="field col-8">
        <label>Nombre</label>
        <input type="text" name="name" id="m_name" required>
      </div>

      <div class="field col-6">
        <label>Ruta (ej: /users)</label>
        <input type="text" name="route" id="m_route" placeholder="/users">
      </div>

      <div class="field col-6">
        <label>Icon (fa-solid ...)</label>
        <input type="text" name="icon" id="m_icon" placeholder="fa-users">
      </div>

      <div class="field col-4">
        <label>Parent</label>
        <select name="parent_id" id="m_parent">
          <option value="">(sin parent)</option>
          @foreach(\App\Models\Module::whereNull('parent_id')->orderBy('sort_order')->get() as $p)
            <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->key }})</option>
          @endforeach
        </select>
      </div>

      <div class="field col-4">
        <label>Orden</label>
        <input type="number" name="sort_order" id="m_sort" value="0">
      </div>

      <div class="field col-4">
        <label>Menú</label>
        <select name="is_menu" id="m_menu">
          <option value="1">Sí</option>
          <option value="0">No</option>
        </select>
      </div>

      <div class="field col-4">
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
  $('#tblModules').DataTable({ pageLength: 10 });

  function openModal(){ $('#mdlModule').addClass('show'); $('body').css('overflow','hidden'); }
  function closeModal(){ $('#mdlModule').removeClass('show'); $('body').css('overflow','auto'); }
  $('[data-close="1"]').on('click', closeModal);

  $('#btnNew').on('click', function(){
    $('#mdlTitle').text('Nuevo módulo');
    $('#frmModule').attr('action', @json(route('modules.store')));
    $('#frmModule').find('input[name="_method"]').remove();

    $('#m_key,#m_name,#m_route,#m_icon').val('');
    $('#m_parent').val('');
    $('#m_sort').val('0');
    $('#m_menu').val('1');
    $('#m_active').val('1');

    openModal();
  });

  $('#tblModules').on('click', '.btnEdit', function(){
    const d = $(this).closest('tr').data('json');

    $('#mdlTitle').text('Editar módulo #' + d.id);
    $('#frmModule').attr('action', @json(url('/modules')) + '/' + d.id);
    if(!$('#frmModule input[name="_method"]').length){
      $('#frmModule').append('<input type="hidden" name="_method" value="PUT">');
    }

    $('#m_key').val(d.key || '');
    $('#m_name').val(d.name || '');
    $('#m_route').val(d.route || '');
    $('#m_icon').val(d.icon || '');
    $('#m_parent').val(d.parent_id ? String(d.parent_id) : '');
    $('#m_sort').val(d.sort_order ?? 0);
    $('#m_menu').val(String(d.is_menu));
    $('#m_active').val(String(d.is_active));

    openModal();
  });
})();
</script>
@endpush
