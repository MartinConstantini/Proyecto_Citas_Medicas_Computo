const API = '../api';

async function logout() {
  try {
    await fetch(API + '/logout.php', { method: 'POST' });
  } catch (err) {
    console.error('logout error', err);
  } finally {
    window.location.href = 'login.php';
  }
}

document.addEventListener('DOMContentLoaded', function () {
  const btn = document.getElementById('btn-logout');
  if (btn) {
    btn.onclick = function (e) {
      e.preventDefault();
      logout();
    };
  }
});
