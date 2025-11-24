const API = '../api';

const formSignup = document.getElementById('form-signup');

formSignup.onsubmit = async function (e) {
  e.preventDefault();

  const nombre = formSignup.nombre.value.trim();
  const email = formSignup.email.value.trim();
  const password = formSignup.password.value;

  if (!nombre || !email || password.length < 6) {
    alert('verifica los datos, minimo 6 caracteres en contrasena');
    return;
  }

  const body = {
    action: 'register',
    nombre: nombre,
    email: email,
    password: password,
    rol: 'paciente'
  };

  try {
    const res = await fetch(API + '/usuarios.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(body)
    });

    const j = await res.json();

    if (!res.ok || !j.ok) {
      alert(j.message || 'no se pudo crear la cuenta');
      return;
    }

    alert('cuenta creada, ahora puedes iniciar sesion');
    window.location.href = 'login.php';
  } catch (err) {
    console.error('signup error catch', err);
    alert('error de conexion');
  }
};
