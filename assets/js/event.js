document.addEventListener('DOMContentLoaded', function () {
    const createEventForm = document.getElementById('create-event-form');
    if (createEventForm) {
        createEventForm.addEventListener('submit', (e) => handleFormSubmit(e, 'handles/handle-create-event.php', 'dashboard.php'));
    }

    const addTicketForm = document.getElementById('add-ticket-form');
    if (addTicketForm) {
        addTicketForm.addEventListener('submit', (e) => handleFormSubmit(e, 'handles/handle-add-ticket.php', window.location.href));
    }
});

function handleFormSubmit(e, handlerUrl, redirectUrl = null) {
    e.preventDefault();
    const form = e.target;
    const submitButton = form.querySelector('button[type="submit"]');
    const spinner = submitButton.querySelector('.spinner-border');

    // Use form-specific error/success divs if they exist, otherwise fallback to general ones
    let errorMessageDiv = form.querySelector('.error-message') || document.getElementById('error-message');
    let successMessageDiv = form.querySelector('.success-message') || document.getElementById('success-message');

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
            form.reset();

            if (redirectUrl) {
                // Reload or redirect to show changes
                setTimeout(() => {
                    window.location.href = redirectUrl;
                }, 1500);
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
