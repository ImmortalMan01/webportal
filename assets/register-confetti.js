function showConfetti() {
    var button = document.getElementById('login-button');
    if (!button || !window.confetti) return;
    var rect = button.getBoundingClientRect();
    var x = (rect.left + rect.width / 2) / window.innerWidth;
    var y = (rect.top + rect.height / 2) / window.innerHeight;
    window.confetti({
        particleCount: 80,
        spread: 70,
        origin: { x: x, y: y }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    if (window.registrationSuccess) {
        var button = document.getElementById('login-button');
        if (button) {
            button.classList.add('complete');
        }
        showConfetti();
    }
});
