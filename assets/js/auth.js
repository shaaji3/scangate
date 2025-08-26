document.addEventListener('DOMContentLoaded', function () {
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', handleAuthFormSubmit);
    }

    const otpForm = document.getElementById('otp-form');
    if (otpForm) {
        otpForm.addEventListener('submit', handleAuthFormSubmit);
    }

    const registerForm = document.getElementById('register-form');
    if (registerForm) {
        registerForm.addEventListener('submit', handleAuthFormSubmit);
    }

    const forgotPasswordForm = document.getElementById('forgot-password-form');
    if (forgotPasswordForm) {
        forgotPasswordForm.addEventListener('submit', handleAuthFormSubmit);
    }

    const resetPasswordForm = document.getElementById('reset-password-form');
    if (resetPasswordForm) {
        resetPasswordForm.addEventListener('submit', handleAuthFormSubmit);
    }
});

function handleAuthFormSubmit(e) {
    e.preventDefault();

    const form = e.target;
    const submitButton = form.querySelector('button[type="submit"]');
    const spinner = submitButton.querySelector('.spinner-border');
    const errorMessageDiv = document.getElementById('error-message');
    const successMessageDiv = document.getElementById('success-message');

    let handler;
    switch (form.id) {
        case 'login-form':
            handler = 'handles/handle-login.php';
            break;
        case 'otp-form':
            handler = 'handles/handle-verify-otp.php';
            break;
        case 'register-form':
            handler = 'handles/handle-register.php';
            break;
        case 'forgot-password-form':
            handler = 'handles/handle-forgot-password.php';
            break;
        case 'reset-password-form':
            handler = 'handles/handle-reset-password.php';
            break;
        default:
            console.error('Unknown form ID:', form.id);
            return;
    }

    // Show spinner and disable button
    if(spinner) spinner.style.display = 'inline-block';
    submitButton.disabled = true;
    if(errorMessageDiv) errorMessageDiv.style.display = 'none';
    if(successMessageDiv) successMessageDiv.style.display = 'none';

    // Clear previous validation errors
    form.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

    const formData = new FormData(form);

    fetch(handler, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        // Hide spinner and re-enable button
        if(spinner) spinner.style.display = 'none';
        submitButton.disabled = false;

        if (data.success) {
            if (data.redirect_url) {
                window.location.href = data.redirect_url;
            } else if (data.message && successMessageDiv) {
                successMessageDiv.textContent = data.message;
                successMessageDiv.style.display = 'block';
                form.reset();
            }
        } else {
            if (data.errors) {
                // Handle field-specific validation errors
                for (const field in data.errors) {
                    const input = form.querySelector(`[name="${field}"]`);
                    if (input) {
                        input.classList.add('is-invalid');
                        const feedback = input.nextElementSibling;
                        if (feedback && feedback.classList.contains('invalid-feedback')) {
                            feedback.textContent = data.errors[field];
                        }
                    }
                }
            } else if (errorMessageDiv) {
                // Handle general error
                errorMessageDiv.textContent = data.error || 'An unknown error occurred.';
                errorMessageDiv.style.display = 'block';
            }
            if(data.redirect_url){
                 window.location.href = data.redirect_url;
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if(spinner) spinner.style.display = 'none';
        submitButton.disabled = false;
        if (errorMessageDiv) {
            errorMessageDiv.textContent = 'A network error occurred. Please try again.';
            errorMessageDiv.style.display = 'block';
        }
    });
}
