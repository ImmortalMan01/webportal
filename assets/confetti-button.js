let button = document.getElementById('login-button');
let disabled = false;

async function loginAndAnimate() {
  if (disabled) return;
  disabled = true;
  document.getElementById('login-error').style.display = 'none';
  button.classList.add('loading');
  button.classList.remove('ready');
  const formData = new FormData();
  formData.append('username', document.getElementById('username').value);
  formData.append('password', document.getElementById('password').value);
  try {
    const response = await fetch('login_api.php', { method: 'POST', body: formData });
    const result = await response.json();
    if (result.success) {
      setTimeout(() => {
        button.classList.add('complete');
        button.classList.remove('loading');
        showConfetti();
        setTimeout(() => { window.location.href = '../index.php'; }, 2000);
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
  let err = document.getElementById('login-error');
  err.innerText = msg;
  err.style.display = 'block';
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

button.addEventListener('click', loginAndAnimate);
document.body.onkeyup = e => { if(e.keyCode==13||e.keyCode==32) loginAndAnimate(); };
