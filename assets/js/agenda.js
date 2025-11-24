const API = '../api';

let calendario;
let citasData = [];

const formCita = document.getElementById('form-cita');

document.addEventListener('DOMContentLoaded', function () {
  const calendarEl = document.getElementById('calendar');

  calendario = new FullCalendar.Calendar(calendarEl, {
    initialView: 'dayGridMonth',
    locale: 'es',
    height: 600,
    eventClick: function (info) {
      const id = Number(info.event.id);
      const c = citasData.find(function (x) { return x.id === id; });
      if (!c) return;

      // llenar formulario para editar
      formCita.id.value = c.id;
      formCita.medico_id.value = c.medico_id;
      formCita.paciente_id.value = c.paciente_id;
      formCita.fecha.value = c.fecha;
      formCita.hora_inicio.value = c.hora_inicio;
      formCita.hora_fin.value = c.hora_fin;
      formCita.motivo.value = c.motivo || '';

      if (confirm('quieres borrar esta cita? aceptar borra, cancelar solo edita')) {
        borrarCita(c.id);
      }
    }
  });

  calendario.render();

  cargarMedicosPacientes();
  cargarCitas();
});

async function cargarMedicosPacientes() {
  const selMed = formCita.medico_id;
  const selPac = formCita.paciente_id;

  try {
    const [resM, resP] = await Promise.all([
      fetch(API + '/medicos.php'),
      fetch(API + '/pacientes.php')
    ]);

    const jM = await resM.json();
    const jP = await resP.json();

    if (jM.ok) {
      selMed.innerHTML = '<option value="">seleccionar</option>' + (jM.data || []).map(function (m) {
        const nombre = m.nombre + ' ' + m.apellido_paterno;
        return '<option value="' + m.id + '">' + nombre + ' (' + m.especialidad + ')</option>';
      }).join('');
    }

    if (jP.ok) {
      selPac.innerHTML = '<option value="">seleccionar</option>' + (jP.data || []).map(function (p) {
        const nombre = p.nombre + ' ' + p.apellido_paterno;
        return '<option value="' + p.id + '">' + nombre + '</option>';
      }).join('');
    }
  } catch (err) {
    console.error('error cargar medicos/pacientes', err);
  }
}

async function cargarCitas() {
  try {
    const res = await fetch(API + '/citas.php');
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

  const id = Number(formCita.id.value) || 0;
  const data = {
    action: id ? 'update' : 'create',
    id: id || undefined,
    medico_id: Number(formCita.medico_id.value),
    paciente_id: Number(formCita.paciente_id.value),
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
    const res = await fetch(API + '/citas.php', {
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
    formCita.id.value = '';
    cargarCitas();
  } catch (err) {
    console.error('guardar cita error', err);
    alert('error de conexion');
  }
};

async function borrarCita(id) {
  try {
    const res = await fetch(API + '/citas.php', {
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
    formCita.id.value = '';
    cargarCitas();
  } catch (err) {
    console.error('borrar cita error', err);
    alert('error de conexion');
  }
}
