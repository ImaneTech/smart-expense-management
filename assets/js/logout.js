document.addEventListener('DOMContentLoaded', () => {
    const countdownElement = document.getElementById('countdown');

    // Check if variables exist (passed from PHP)
    if (typeof redirectDelay !== 'undefined' && typeof redirectUrl !== 'undefined') {
        let timeLeft = redirectDelay;

        const interval = setInterval(() => {
            timeLeft--;
            if (countdownElement) {
                countdownElement.textContent = timeLeft;
            }

            if (timeLeft <= 0) {
                clearInterval(interval);
                window.location.href = redirectUrl;
            }
        }, 1000);
    }
});