document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    const otpForm = document.getElementById('otpForm');
    const message = document.getElementById('message');
    const otpMessage = document.getElementById('otpMessage');
    let emailOrPhone;

    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
        emailOrPhone = document.getElementById('email').value;

        const formData = new FormData(this);
        fetch('login.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                loginForm.style.display = 'none';
                otpForm.style.display = 'block';
                otpMessage.textContent = data.message;
                otpMessage.style.color = 'green';
            } else {
                message.textContent = data.message;
                message.style.color = 'red';
            }
        })
        .catch(error => {
            message.textContent = "An error occurred. Please try again.";
            message.style.color = 'red';
            console.error('Error:', error);
        });
    });

    const otpInput = document.getElementById('otp');
    otpInput.addEventListener('input', function() {
        if (this.value.length === 6) {
            const formData = new FormData();
            formData.append('email', emailOrPhone);
            formData.append('otp', this.value);
            
            fetch('verify_otp.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    otpMessage.textContent = data.message;
                    otpMessage.style.color = 'green';
                    window.location.href = data.role === 'admin' ? '../admin/index.php' : '../index.php';
                } else {
                    otpMessage.textContent = data.message;
                    otpMessage.style.color = 'red';
                }
            })
            .catch(error => {
                otpMessage.textContent = "An error occurred while verifying OTP. Please try again.";
                otpMessage.style.color = 'red';
                console.error('Error:', error);
            });
        }
    });
});