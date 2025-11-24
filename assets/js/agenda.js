// usamos otro nombre para no chocar con app.js
const API_AGENDA = '../api';

let calendario;
let citasData = [];

const formCita = document.getElementById('form-cita');
const campoId = formCita.querySelector('input[name="id"]');
const selMedico = formCita.medico_id;
const selPaciente = formCita.paciente_id;
const btnEliminarCita = document.getElementById('btn-eliminar-cita');

document.addEventListener('DOMContentLoaded', function () {
  const calendarEl = document.getElementById('calendar');

  try {
    calendario = new FullCalendar.Calendar(calendarEl, {
      initialView: 'dayGridMonth',
      locale: 'es',
      height: 600,
      selectable: true,
      dateClick: function (info) {
        formCita.fecha.value = info.dateStr;
        campoId.value = '';
      },
      eventClick: function (info) {
        const id = Number(info.event.id);
        const c = citasData.find(function (x) { return x.id === id; });
        if (!c) return;

        campoId.value = c.id;
        selMedico.value = String(c.medico_id);
        selPaciente.value = String(c.paciente_id);
        formCita.fecha.value = c.fecha;
        formCita.hora_inicio.value = c.hora_inicio;
        formCita.hora_fin.value = c.hora_fin;
        formCita.motivo.value = c.motivo || '';

        calendario.gotoDate(c.fecha);
      }
    });

    calendario.render();
  } catch (err) {
    console.error('error al inicializar fullcalendar', err);
    return;
  }

  cargarMedicosPacientes();
  cargarCitas();
});

async function cargarMedicosPacientes() {
  try {
    const [resM, resP] = await Promise.all([
      fetch(API_AGENDA + '/medicos.php'),
      fetch(API_AGENDA + '/pacientes.php')
    ]);

    if (!resM.ok || !resP.ok) {
      console.error('error http medicos/pacientes', resM.status, resP.status);
    }

    let jM = { ok: false };
    let jP = { ok: false };

    try {
      jM = await resM.json();
    } catch (e) {
      console.error('error parse json medicos', e);
    }

    try {
      jP = await resP.json();
    } catch (e) {
      console.error('error parse json pacientes', e);
    }

    if (jM.ok) {
      selMedico.innerHTML = '<option value="">seleccionar</option>' +
        (jM.data || []).map(function (m) {
          const nombre = m.nombre + ' ' + m.apellido_paterno;
          return '<option value="' + m.id + '">' + nombre + ' (' + m.especialidad + ')</option>';
        }).join('');
    } else {
      selMedico.innerHTML = '<option value="">error al cargar</option>';
    }

    if (jP.ok) {
      selPaciente.innerHTML = '<option value="">seleccionar</option>' +
        (jP.data || []).map(function (p) {
          const nombre = p.nombre + ' ' + p.apellido_paterno;
          return '<option value="' + p.id + '">' + nombre + '</option>';
        }).join('');
    } else {
      selPaciente.innerHTML = '<option value="">error al cargar</option>';
    }
  } catch (err) {
    console.error('error cargar medicos/pacientes', err);
    selMedico.innerHTML = '<option value="">error</option>';
    selPaciente.innerHTML = '<option value="">error</option>';
  }
}

async function cargarCitas() {
  try {
    const res = await fetch(API_AGENDA + '/citas.php');
    if (!res.ok) {
      const txt = await res.text();
      console.error('citas error http', txt);
      return;
    }
    const j = await res.json();
    if (!j.ok) return;

    citasData = j.data || [];
    calendario.removeAllEvents();

    citasData.forEach(function (c) {
      const titulo = c.medico_nombre + ' / ' + c.paciente_nombre;
      calendario.addEvent({
        id: String(c.id),
        title: titulo,
        start: c.fecha + 'T' + c.hora_inicio,
        end: c.fecha + 'T' + c.hora_fin
      });
    });
  } catch (err) {
    console.error('citas error catch', err);
  }
}

formCita.onsubmit = async function (e) {
  e.preventDefault();

  const id = Number(campoId.value) || 0;

  const data = {
    action: id ? 'update' : 'create',
    id: id || undefined,
    medico_id: Number(selMedico.value),
    paciente_id: Number(selPaciente.value),
    fecha: formCita.fecha.value,
    hora_inicio: formCita.hora_inicio.value,
    hora_fin: formCita.hora_fin.value,
    motivo: formCita.motivo.value.trim()
  };

  if (!data.medico_id || !data.paciente_id || !data.fecha || !data.hora_inicio || !data.hora_fin) {
    alert('completa todos los datos');
    return;
  }

  try {
    const res = await fetch(API_AGENDA + '/citas.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    });
    const j = await res.json();

    if (!res.ok || !j.ok) {
      alert(j.message || 'no se pudo guardar la cita');
      return;
    }

    alert('cita guardada');
    formCita.reset();
    campoId.value = '';
    cargarCitas();
  } catch (err) {
    console.error('guardar cita error', err);
    alert('error de conexion');
  }
};

btnEliminarCita.onclick = async function () {
  const id = Number(campoId.value);
  if (!id) {
    alert('selecciona primero una cita del calendario');
    return;
  }

  if (!confirm('seguro que deseas eliminar esta cita?')) {
    return;
  }

  try {
    const res = await fetch(API_AGENDA + '/citas.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'delete', id: id })
    });
    const j = await res.json();

    if (!res.ok || !j.ok) {
      alert(j.message || 'no se pudo eliminar la cita');
      return;
    }

    alert('cita eliminada');
    formCita.reset();
    campoId.value = '';
    cargarCitas();
  } catch (err) {
    console.error('borrar cita error', err);
    alert('error de conexion');
  }
};
