const API_PAC = '../api';

const cuerpoTablaPacientes = document.getElementById('tabla-pacientes');
const formAgregarPaciente = document.getElementById('form-agregar-paciente');
const formEditarPaciente = document.getElementById('form-editar-paciente');
const btnEliminarPaciente = document.getElementById('btn-eliminar-paciente');

let pacientes = [];
let modalEditarPaciente;

document.addEventListener('DOMContentLoaded', function () {
  modalEditarPaciente = new bootstrap.Modal(document.getElementById('modalEditarPaciente'));
  cargarPacientes();
});

async function cargarPacientes() {
  try {
    const res = await fetch(API_PAC + '/pacientes.php');
    if (!res.ok) {
      const txt = await res.text();
      console.error('pacientes error http', txt);
      cuerpoTablaPacientes.innerHTML =
        '<tr><td colspan="6" class="text-center text-danger small">error al cargar pacientes</td></tr>';
      return;
    }
    const j = await res.json();
    if (!j.ok) {
      cuerpoTablaPacientes.innerHTML =
        '<tr><td colspan="6" class="text-center text-danger small">error al cargar pacientes</td></tr>';
      return;
    }

    pacientes = j.data || [];
    if (!pacientes.length) {
      cuerpoTablaPacientes.innerHTML =
        '<tr><td colspan="6" class="text-center text-muted small">no hay pacientes registrados</td></tr>';
      return;
    }

    cuerpoTablaPacientes.innerHTML = pacientes.map(function (p) {
      const nombreCompleto = p.nombre + ' ' + p.apellido_paterno + ' ' + p.apellido_materno;
      return '<tr>' +
        '<td>' + p.id + '</td>' +
        '<td>' + nombreCompleto + '</td>' +
        '<td>' + p.edad + '</td>' +
        '<td>' + p.sexo + '</td>' +
        '<td>' + p.direccion + '</td>' +
        '<td class="text-end">' +
          '<button class="btn btn-outline-primary btn-sm" data-id="' + p.id + '">editar</button>' +
        '</td>' +
      '</tr>';
    }).join('');

    cuerpoTablaPacientes.querySelectorAll('button').forEach(function (btn) {
      btn.onclick = function () {
        const id = Number(btn.getAttribute('data-id'));
        abrirEditarPaciente(id);
      };
    });
  } catch (err) {
    console.error('pacientes error catch', err);
    cuerpoTablaPacientes.innerHTML =
      '<tr><td colspan="6" class="text-center text-danger small">error de conexion</td></tr>';
  }
}

formAgregarPaciente.onsubmit = async function (e) {
  e.preventDefault();

  const data = {
    action: 'create',
    nombre: formAgregarPaciente.nombre.value.trim(),
    apellido_paterno: formAgregarPaciente.apellido_paterno.value.trim(),
    apellido_materno: formAgregarPaciente.apellido_materno.value.trim(),
    edad: Number(formAgregarPaciente.edad.value),
    sexo: formAgregarPaciente.sexo.value,
    direccion: formAgregarPaciente.direccion.value.trim()
  };

  if (!data.nombre || !data.apellido_paterno || !data.apellido_materno ||
      !data.edad || !data.sexo || !data.direccion) {
    alert('datos incompletos');
    return;
  }

  try {
    const res = await fetch(API_PAC + '/pacientes.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    });
    const j = await res.json();

    if (!res.ok || !j.ok) {
      alert(j.message || 'no se pudo guardar el paciente');
      return;
    }

    alert('paciente agregado');
    formAgregarPaciente.reset();
    bootstrap.Modal.getInstance(document.getElementById('modalAgregarPaciente')).hide();
    cargarPacientes();
  } catch (err) {
    console.error('agregar paciente error', err);
    alert('error de conexion');
  }
};

function abrirEditarPaciente(id) {
  const p = pacientes.find(function (item) { return item.id === id; });
  if (!p) return;

  formEditarPaciente.id.value = p.id;
  formEditarPaciente.nombre.value = p.nombre;
  formEditarPaciente.apellido_paterno.value = p.apellido_paterno;
  formEditarPaciente.apellido_materno.value = p.apellido_materno;
  formEditarPaciente.edad.value = p.edad;
  formEditarPaciente.sexo.value = p.sexo;
  formEditarPaciente.direccion.value = p.direccion;

  modalEditarPaciente.show();
}

formEditarPaciente.onsubmit = async function (e) {
  e.preventDefault();

  const data = {
    action: 'update',
    id: Number(formEditarPaciente.id.value),
    nombre: formEditarPaciente.nombre.value.trim(),
    apellido_paterno: formEditarPaciente.apellido_paterno.value.trim(),
    apellido_materno: formEditarPaciente.apellido_materno.value.trim(),
    edad: Number(formEditarPaciente.edad.value),
    sexo: formEditarPaciente.sexo.value,
    direccion: formEditarPaciente.direccion.value.trim()
  };

  if (!data.id || !data.nombre || !data.apellido_paterno || !data.apellido_materno ||
      !data.edad || !data.sexo || !data.direccion) {
    alert('datos incompletos');
    return;
  }

  try {
    const res = await fetch(API_PAC + '/pacientes.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    });
    const j = await res.json();

    if (!res.ok || !j.ok) {
      alert(j.message || 'no se pudo actualizar el paciente');
      return;
    }

    alert('paciente actualizado');
    modalEditarPaciente.hide();
    cargarPacientes();
  } catch (err) {
    console.error('editar paciente error', err);
    alert('error de conexion');
  }
};

btnEliminarPaciente.onclick = async function () {
  const id = Number(formEditarPaciente.id.value);
  if (!id) return;

  if (!confirm('seguro que deseas eliminar este paciente?')) {
    return;
  }

  const data = {
    action: 'delete',
    id: id
  };

  try {
    const res = await fetch(API_PAC + '/pacientes.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    });
    const j = await res.json();

    if (!res.ok || !j.ok) {
      alert(j.message || 'no se pudo eliminar el paciente');
      return;
    }

    alert('paciente eliminado');
    modalEditarPaciente.hide();
    cargarPacientes();
  } catch (err) {
    console.error('eliminar paciente error', err);
    alert('error de conexion');
  }
};
