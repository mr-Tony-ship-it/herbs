function validateForm(input) {
    const emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;
    const phonePattern = /^[0-9]{10}$/;
    return emailPattern.test(input) || phonePattern.test(input);
}

document.addEventListener('DOMContentLoaded', function() {
    const registrationForm = document.getElementById('registrationForm');
    const otpForm = document.getElementById('otpForm');
    const otpInput = document.getElementById('otp');
    const message = document.getElementById('message');
    const otpMessage = document.getElementById('otpMessage');
    const resendOtp = document.getElementById('resendOtp');
    let emailOrPhone;
    
    registrationForm.addEventListener('submit', function(e) {
        e.preventDefault();
        emailOrPhone = document.getElementById('email').value;
        if (validateForm(emailOrPhone)) {
            const formData = new FormData(this);
            fetch('register.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    registrationForm.style.display = 'none';
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
        } else {
            message.textContent = 'Please enter a valid email address or 10-digit phone number';
            message.style.color = 'red';
        }
    });
    
    otpInput.addEventListener('input', function() {
        if (this.value.length === 6) {
            const formData = new FormData();
            formData.append('email', emailOrPhone); // Use the email or phone from registration
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
                    window.location.href = '../index.php'; // Update with your actual dashboard path
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

    resendOtp.addEventListener('click', function(e) {
        e.preventDefault();
        const formData = new FormData();
        formData.append('email', emailOrPhone);
        
        fetch('register.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                otpMessage.textContent = data.message;
                otpMessage.style.color = 'green';
            } else {
                otpMessage.textContent = data.message;
                otpMessage.style.color = 'red';
            }
        })
        .catch(error => {
            otpMessage.textContent = "An error occurred while resending OTP. Please try again.";
            otpMessage.style.color = 'red';
            console.error('Error:', error);
        });
    });
});