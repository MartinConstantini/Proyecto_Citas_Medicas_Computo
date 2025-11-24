const API = '../api';

const lista = document.getElementById('lista-citas');
const statHoy = document.getElementById('stat-hoy');
const statPend = document.getElementById('stat-pendientes');
const statTotal = document.getElementById('stat-total');

function hoyString() {
  const d = new Date();
  const y = d.getFullYear();
  const m = String(d.getMonth() + 1).padStart(2, '0');
  const day = String(d.getDate()).padStart(2, '0');
  return y + '-' + m + '-' + day;
}

async function loadCitas() {
  try {
    const res = await fetch(API + '/citas.php');
    if (!res.ok) {
      const txt = await res.text();
      console.error('citas error http', txt);
      return;
    }
    const j = await res.json();
    if (!j.ok) {
      return;
    }

    const data = j.data || [];
    const hoy = hoyString();

    let totalHoy = 0;
    let totalPend = 0;
    let total = data.length;

    data.forEach(function (c) {
      if (c.fecha === hoy) {
        totalHoy += 1;
      }
      if (c.estado === 'pendiente') {
        totalPend += 1;
      }
    });

    statHoy.textContent = totalHoy;
    statPend.textContent = totalPend;
    statTotal.textContent = total;

    const recientes = data.slice(0, 6);

    if (!recientes.length) {
      lista.innerHTML = '<li class="text-muted">no hay citas registradas</li>';
      return;
    }

    lista.innerHTML = recientes.map(function (c) {
      return '<li class="dash-item">' +
        '<span class="dash-date">' + c.fecha + ' ' + c.hora_inicio + '</span>' +
        '<span class="dash-main">' + c.nombre_medico + '</span>' +
        '<span class="dash-sub">paciente: ' + c.nombre_paciente + '</span>' +
        '</li>';
    }).join('');
  } catch (err) {
    console.error('citas error catch', err);
  }
}

loadCitas();
