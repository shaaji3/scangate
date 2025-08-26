document.addEventListener('DOMContentLoaded', function () {
    const updateProfileForm = document.getElementById('update-profile-form');
    if (updateProfileForm) {
        updateProfileForm.addEventListener('submit', (e) => handleProfileFormSubmit(e, 'handles/handle-update-profile.php'));
    }

    const changePasswordForm = document.getElementById('change-password-form');
    if (changePasswordForm) {
        changePasswordForm.addEventListener('submit', (e) => handleProfileFormSubmit(e, 'handles/handle-change-password.php'));
    }
});

function handleProfileFormSubmit(e, handlerUrl) {
    e.preventDefault();
    const form = e.target;
    const submitButton = form.querySelector('button[type="submit"]');
    const spinner = submitButton.querySelector('.spinner-border');

    // Use form-specific error/success divs
    const successMessageDiv = form.closest('.card-body').querySelector('.alert-success');
    const errorMessageDiv = form.closest('.card-body').querySelector('.alert-danger');

    // Show spinner and disable button
    if(spinner) spinner.style.display = 'inline-block';
    submitButton.disabled = true;
    if(errorMessageDiv) errorMessageDiv.style.display = 'none';
    if(successMessageDiv) successMessageDiv.style.display = 'none';

    // Clear previous validation errors
    form.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

    const formData = new FormData(form);

    fetch(handlerUrl, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(spinner) spinner.style.display = 'none';
        submitButton.disabled = false;

        if (data.success) {
            if(successMessageDiv) {
                successMessageDiv.textContent = data.message || 'Success!';
                successMessageDiv.style.display = 'block';
            }
            // Reset form on success, except for the profile form which should keep the new name
            if (form.id !== 'update-profile-form') {
                form.reset();
            }
        } else {
            if (data.errors) {
                for (const field in data.errors) {
                    const input = form.querySelector(`[name="${field}"]`);
                    if (input) {
                        input.classList.add('is-invalid');
                        const feedback = input.closest('.mb-3').querySelector('.invalid-feedback');
                        if (feedback) {
                            feedback.textContent = data.errors[field];
                        }
                    }
                }
            } else if (errorMessageDiv) {
                errorMessageDiv.textContent = data.error || 'An unknown error occurred.';
                errorMessageDiv.style.display = 'block';
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
