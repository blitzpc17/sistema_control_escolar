@extends('layouts.app')

@section('title','Roles')
@section('crumbTitle','Roles')
@section('crumbSub','Seguridad / Roles')

@section('content')
<div class="card">
  <div class="section-head">
    <div class="title">
      <b>Roles</b>
      <span>Alta/edición y baja lógica</span>
    </div>
    <div class="actions">
      <button class="btn primary" id="btnNew"><i class="fa-solid fa-plus"></i> Nuevo</button>
    </div>
  </div>

  <div style="height:10px"></div>

  <div class="table-wrap">
    <table id="tblRoles" class="display" style="width:100%">
      <thead>
      <tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>Descripción</th>
        <th>Activo</th>
        <th>Acciones</th>
      </tr>
      </thead>
      <tbody>
      @foreach($roles as $r)
        @php
          $payload = [
            "id" => $r->id,
            "name" => $r->name,
            "description" => $r->description,
            "is_active" => $r->is_active ? 1 : 0,
          ];
        @endphp
        <tr data-json='@json($payload, JSON_UNESCAPED_UNICODE)'>
          <td>{{ $r->id }}</td>
          <td>{{ $r->name }}</td>
          <td>{{ $r->description }}</td>
          <td>{{ $r->is_active ? 'Sí' : 'No' }}</td>
          <td style="white-space:nowrap;">
            <button class="btn outline btnEdit" style="padding:8px 10px;border-radius:12px" title="Editar">
              <i class="fa-regular fa-pen-to-square"></i>
            </button>

            <a class="btn outline" style="padding:8px 10px;border-radius:12px" title="Permisos"
               href="{{ route('permissions.index',['role_id'=>$r->id]) }}">
              <i class="fa-solid fa-shield-halved"></i>
            </a>

            <form id="del-role-{{ $r->id }}" action="{{ route('roles.destroy',$r->id) }}" method="POST" style="display:inline;">
              @csrf @method('DELETE')
              <button type="button" class="btn outline danger" style="padding:8px 10px;border-radius:12px"
                onclick="confirmBaja('del-role-{{ $r->id }}','¿Dar de baja rol?','{{ $r->name }}')">
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
<div class="modal" id="mdlRole">
  <div class="backdrop" data-close="1"></div>
  <div class="dialog">
    <div class="dialog-head">
      <b id="mdlTitle">Nuevo rol</b>
      <button class="close" type="button" data-close="1"><i class="fa-solid fa-xmark"></i></button>
    </div>

    <form id="frmRole" method="POST" action="{{ route('roles.store') }}" class="form" autocomplete="off">
      @csrf

      <div class="field col-6">
        <label>Nombre</label>
        <input type="text" name="name" id="r_name" required>
      </div>

      <div class="field col-6">
        <label>Activo</label>
        <select name="is_active" id="r_active">
          <option value="1">Sí</option>
          <option value="0">No</option>
        </select>
      </div>

      <div class="field col-12">
        <label>Descripción</label>
        <textarea name="description" id="r_desc" placeholder="Opcional"></textarea>
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
  $('#tblRoles').DataTable({
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

  function openModal(){ $('#mdlRole').addClass('show'); $('body').css('overflow','hidden'); }
  function closeModal(){ $('#mdlRole').removeClass('show'); $('body').css('overflow','auto'); }

  $('#mdlRole [data-close="1"]').on('click', closeModal);

  $('#btnNew').on('click', function(){
    $('#mdlTitle').text('Nuevo rol');
    $('#frmRole').attr('action', @json(route('roles.store')));
    $('#frmRole').find('input[name="_method"]').remove();

    $('#r_name').val('');
    $('#r_desc').val('');
    $('#r_active').val('1');

    openModal();
  });

  $('#tblRoles').on('click','.btnEdit', function(){
    const row = $(this).closest('tr');
    const data = JSON.parse(row.attr('data-json'));

    $('#mdlTitle').text('Editar rol #' + data.id);
    $('#frmRole').attr('action', @json(url('/roles')) + '/' + data.id);
    if(!$('#frmRole input[name="_method"]').length){
      $('#frmRole').append('<input type="hidden" name="_method" value="PUT">');
    }

    $('#r_name').val(data.name || '');
    $('#r_desc').val(data.description || '');
    $('#r_active').val(String(data.is_active));

    openModal();
  });
})();
</script>
@endpush
