const API = '../api';

const formLogin = document.getElementById('form-login');

formLogin.onsubmit = async function (e) {
  e.preventDefault();

  const email = formLogin.email.value.trim();
  const password = formLogin.password.value;

  if (!email || !password) {
    alert('llena todos los campos');
    return;
  }

  const body = {
    action: 'login',
    email: email,
    password: password
  };

  try {
    const res = await fetch(API + '/usuarios.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(body)
    });

    if (!res.ok) {
      const txt = await res.text();
      console.error('login error http', txt);
      alert('error al iniciar sesion');
      return;
    }

    const j = await res.json();
    if (!j.ok) {
      alert(j.message || 'error');
      return;
    }

    window.location.href = 'dashboard.php';
  } catch (err) {
    console.error('login error catch', err);
    alert('error de conexion');
  }
};
