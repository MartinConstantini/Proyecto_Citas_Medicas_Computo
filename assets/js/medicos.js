const API = '../api';

const cuerpoTablaMedicos = document.getElementById('tabla-medicos');
const formAgregarMedico = document.getElementById('form-agregar-medico');
const formEditarMedico = document.getElementById('form-editar-medico');
const btnEliminarMedico = document.getElementById('btn-eliminar-medico');

let medicos = [];
let modalEditar;

document.addEventListener('DOMContentLoaded', function () {
  modalEditar = new bootstrap.Modal(document.getElementById('modalEditarMedico'));
  cargarMedicos();
});

async function cargarMedicos() {
  try {
    const res = await fetch(API + '/medicos.php');
    if (!res.ok) {
      const txt = await res.text();
      console.error('medicos error http', txt);
      cuerpoTablaMedicos.innerHTML = '<tr><td colspan="5" class="text-center text-danger small">error al cargar medicos</td></tr>';
      return;
    }
    const j = await res.json();
    if (!j.ok) {
      cuerpoTablaMedicos.innerHTML = '<tr><td colspan="5" class="text-center text-danger small">error al cargar medicos</td></tr>';
      return;
    }

    medicos = j.data || [];
    if (!medicos.length) {
      cuerpoTablaMedicos.innerHTML = '<tr><td colspan="5" class="text-center text-muted small">no hay medicos registrados</td></tr>';
      return;
    }

    cuerpoTablaMedicos.innerHTML = medicos.map(function (m) {
      return '<tr>' +
        '<td>' + m.nombre + '</td>' +
        '<td>' + m.email + '</td>' +
        '<td>' + (m.especialidad || '') + '</td>' +
        '<td>' + (m.telefono || '') + '</td>' +
        '<td class="text-end">' +
          '<button class="btn btn-outline-primary btn-sm" data-id="' + m.id + '">editar</button>' +
        '</td>' +
      '</tr>';
    }).join('');

    cuerpoTablaMedicos.querySelectorAll('button').forEach(function (btn) {
      btn.onclick = function () {
        const id = Number(btn.getAttribute('data-id'));
        abrirEditarMedico(id);
      };
    });
  } catch (err) {
    console.error('medicos error catch', err);
    cuerpoTablaMedicos.innerHTML = '<tr><td colspan="5" class="text-center text-danger small">error de conexion</td></tr>';
  }
}

formAgregarMedico.onsubmit = async function (e) {
  e.preventDefault();

  const data = {
    action: 'create',
    usuario_id: Number(formAgregarMedico.usuario_id.value),
    especialidad: formAgregarMedico.especialidad.value.trim(),
    telefono: formAgregarMedico.telefono.value.trim()
  };

  if (!data.usuario_id || !data.especialidad) {
    alert('datos incompletos');
    return;
  }

  try {
    const res = await fetch(API + '/medicos.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    });
    const j = await res.json();

    if (!res.ok || !j.ok) {
      alert(j.message || 'no se pudo guardar el medico');
      return;
    }

    alert('medico agregado');
    formAgregarMedico.reset();
    bootstrap.Modal.getInstance(document.getElementById('modalAgregarMedico')).hide();
    cargarMedicos();
  } catch (err) {
    console.error('agregar medico error', err);
    alert('error de conexion');
  }
};

function abrirEditarMedico(id) {
  const m = medicos.find(function (item) { return item.id === id; });
  if (!m) return;

  formEditarMedico.id.value = m.id;
  formEditarMedico.nombre.value = m.nombre || '';
  formEditarMedico.email.value = m.email || '';
  formEditarMedico.especialidad.value = m.especialidad || '';
  formEditarMedico.telefono.value = m.telefono || '';

  modalEditar.show();
}

formEditarMedico.onsubmit = async function (e) {
  e.preventDefault();

  const data = {
    action: 'update',
    id: Number(formEditarMedico.id.value),
    especialidad: formEditarMedico.especialidad.value.trim(),
    telefono: formEditarMedico.telefono.value.trim()
  };

  if (!data.id || !data.especialidad) {
    alert('datos incompletos');
    return;
  }

  try {
    const res = await fetch(API + '/medicos.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    });
    const j = await res.json();

    if (!res.ok || !j.ok) {
      alert(j.message || 'no se pudo actualizar el medico');
      return;
    }

    alert('medico actualizado');
    modalEditar.hide();
    cargarMedicos();
  } catch (err) {
    console.error('editar medico error', err);
    alert('error de conexion');
  }
};

btnEliminarMedico.onclick = async function () {
  const id = Number(formEditarMedico.id.value);
  if (!id) return;

  if (!confirm('seguro que deseas eliminar este medico?')) {
    return;
  }

  const data = {
    action: 'delete',
    id: id
  };

  try {
    const res = await fetch(API + '/medicos.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    });
    const j = await res.json();

    if (!res.ok || !j.ok) {
      alert(j.message || 'no se pudo eliminar el medico');
      return;
    }

    alert('medico eliminado');
    modalEditar.hide();
    cargarMedicos();
  } catch (err) {
    console.error('eliminar medico error', err);
    alert('error de conexion');
  }
};
