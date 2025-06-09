let button = document.getElementById('register-button');
let disabled = false;

async function registerAndAnimate() {
  if (disabled) return;
  disabled = true;
  const errorBox = document.getElementById('register-error');
  if (errorBox) errorBox.style.display = 'none';
  button.classList.add('loading');
  button.classList.remove('ready');
  const formData = new FormData();
  formData.append('username', document.getElementById('reg_username').value);
  formData.append('email', document.getElementById('reg_email').value);
  formData.append('password', document.getElementById('reg_password').value);
  formData.append('role', document.getElementById('reg_role').value);
  try {
    const response = await fetch('register_api.php', { method: 'POST', body: formData });
    const result = await response.json();
    if (result.success) {
      setTimeout(() => {
        button.classList.add('complete');
        button.classList.remove('loading');
        showConfetti();
        setTimeout(() => { window.location.href = 'login.php'; }, 2000);
      }, 1200);
    } else {
      showError(result.error || 'Hata');
    }
  } catch (e) {
    showError('Bağlantı hatası');
  }
}

function showError(msg) {
  disabled = false;
  button.classList.remove('loading');
  button.classList.add('ready');
  const err = document.getElementById('register-error');
  if (err) {
    err.innerText = msg;
    err.style.display = 'block';
  }
}

function showConfetti() {
  const rect = button.getBoundingClientRect();
  const x = (rect.left + rect.width / 2) / window.innerWidth;
  const y = (rect.top + rect.height / 2) / window.innerHeight;
  if (window.confetti) {
    window.confetti({
      particleCount: 80,
      spread: 70,
      origin: { x, y }
    });
  }
}

if (button) {
  button.addEventListener('click', registerAndAnimate);
  document.body.onkeyup = e => { if (e.keyCode==13 || e.keyCode==32) registerAndAnimate(); };
}
