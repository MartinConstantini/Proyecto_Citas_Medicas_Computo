const API = '../api';

const form = document.getElementById('form-cita');

async function loadSelect(url, select, labelProp) {
  try {
    const res = await fetch(url);
    if (!res.ok) {
      return;
    }
    const j = await res.json();
    if (!j.ok) {
      return;
    }
    const data = j.data || [];
    select.innerHTML = data.map(function (i) {
      return '<option value="' + i.id + '">' + i[labelProp] + '</option>';
    }).join('');
  } catch (err) {
    console.error('load select error', err);
  }
}

async function initForm() {
  await loadSelect(API + '/medicos.php', form.medico_id, 'nombre');
  await loadSelect(API + '/pacientes.php', form.paciente_id, 'nombre');
}

form.onsubmit = async function (e) {
  e.preventDefault();

  const body = {
    action: 'create',
    medico_id: Number(form.medico_id.value),
    paciente_id: Number(form.paciente_id.value),
    fecha: form.fecha.value,
    hora_inicio: form.hora_inicio.value,
    hora_fin: form.hora_fin.value,
    motivo: form.motivo.value.trim()
  };

  if (!body.medico_id || !body.paciente_id || !body.fecha || !body.hora_inicio || !body.hora_fin) {
    alert('faltan datos');
    return;
  }

  try {
    const res = await fetch(API + '/citas.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(body)
    });

    const j = await res.json();
    if (!res.ok || !j.ok) {
      alert(j.message || 'error al guardar cita');
      return;
    }

    alert('cita guardada');
    form.reset();
    calendar.refetchEvents();
  } catch (err) {
    console.error('error cita', err);
    alert('error de conexion');
  }
};

let calendar;

document.addEventListener('DOMContentLoaded', function () {
  const calendarEl = document.getElementById('calendar');

  calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'timeGridWeek',
    slotMinTime: '08:00:00',
    slotMaxTime: '20:00:00',
    headerToolbar: {
      left: 'prev,next today',
      center: 'title',
      right: 'dayGridMonth,timeGridWeek,timeGridDay'
    },
    events: API + '/citas.php?mode=calendar'
  });

  calendar.render();
  initForm();
});
