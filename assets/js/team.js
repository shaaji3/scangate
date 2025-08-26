document.addEventListener('DOMContentLoaded', function () {
    const addMemberForm = document.getElementById('add-member-form');

    if (addMemberForm) {
        addMemberForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const submitButton = document.getElementById('add-member-button');
            const spinner = submitButton.querySelector('.spinner-border');
            const errorMessageDiv = document.getElementById('add-member-error');
            const successMessageDiv = document.getElementById('add-member-success');

            // Show spinner and disable button
            spinner.style.display = 'inline-block';
            submitButton.disabled = true;
            if(errorMessageDiv) errorMessageDiv.style.display = 'none';
            if(successMessageDiv) successMessageDiv.style.display = 'none';

            // Clear previous validation errors
            addMemberForm.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
            addMemberForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

            const formData = new FormData(addMemberForm);

            fetch('handles/handle-add-member.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                spinner.style.display = 'none';
                submitButton.disabled = false;

                if (data.success) {
                    successMessageDiv.textContent = data.message || 'Team member added successfully!';
                    successMessageDiv.style.display = 'block';
                    addMemberForm.reset();
                    // Reload to show the new member in the list
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    if (data.errors) {
                        for (const field in data.errors) {
                            const input = addMemberForm.querySelector(`[name="${field}"]`);
                            if (input) {
                                input.classList.add('is-invalid');
                                const feedback = input.closest('.mb-3').querySelector('.invalid-feedback');
                                if (feedback) {
                                    feedback.textContent = data.errors[field];
                                }
                            }
                        }
                    } else {
                        errorMessageDiv.textContent = data.error || 'An unknown error occurred.';
                        errorMessageDiv.style.display = 'block';
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                spinner.style.display = 'none';
                submitButton.disabled = false;
                errorMessageDiv.textContent = 'A network error occurred. Please try again.';
                errorMessageDiv.style.display = 'block';
            });
        });
    }
});
