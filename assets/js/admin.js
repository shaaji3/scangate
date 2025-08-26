document.addEventListener('DOMContentLoaded', function () {
    const usersTableEl = document.getElementById('users-table');
    let usersTable;

    if (usersTableEl) {
        usersTable = new DataTable('#users-table', {
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "handles/handle-get-users.php",
                "type": "POST"
            },
            "columns": [
                { "data": "id" },
                { "data": "name" },
                { "data": "email" },
                { "data": "role" },
                {
                    "data": "status",
                    "render": function(data, type, row) {
                        let badgeClass = data === 'active' ? 'badge-success' : 'badge-danger';
                        return `<span class="badge light ${badgeClass}">${data}</span>`;
                    }
                },
                {
                    "data": "created_at",
                    "render": function(data, type, row) {
                        return new Date(data).toLocaleDateString();
                    }
                },
                {
                    "data": null,
                    "orderable": false,
                    "render": function(data, type, row) {
                        const suspendText = row.status === 'active' ? 'Suspend' : 'Activate';
                        return `
                            <div class="d-flex">
                                <button class="btn btn-primary shadow btn-xs sharp me-1 edit-btn" data-id="${row.id}"><i class="fas fa-pencil-alt"></i></button>
                                <button class="btn btn-warning shadow btn-xs sharp me-1 suspend-btn" data-id="${row.id}">${suspendText}</button>
                                <button class="btn btn-danger shadow btn-xs sharp delete-btn" data-id="${row.id}"><i class="fa fa-trash"></i></button>
                            </div>
                        `;
                    }
                }
            ]
        });
    }

    // --- Modal and Form Handling ---
    const addUserModal = new bootstrap.Modal(document.getElementById('addUserModal'));
    const editUserModal = new bootstrap.Modal(document.getElementById('editUserModal'));
    const suspendUserModal = new bootstrap.Modal(document.getElementById('suspendUserModal'));
    const deleteUserModal = new bootstrap.Modal(document.getElementById('deleteUserModal'));

    document.getElementById('add-user-form').addEventListener('submit', (e) => handleFormSubmit(e, 'handles/handle-add-user.php', usersTable, addUserModal));
    document.getElementById('edit-user-form').addEventListener('submit', (e) => handleFormSubmit(e, 'handles/handle-edit-user.php', usersTable, editUserModal));
    document.getElementById('confirm-suspend-btn').addEventListener('click', () => handleConfirmationSubmit('suspend-user-form', 'handles/handle-suspend-user.php', usersTable, suspendUserModal));
    document.getElementById('confirm-delete-btn').addEventListener('click', () => handleConfirmationSubmit('delete-user-form', 'handles/handle-delete-user.php', usersTable, deleteUserModal));

    // --- Action Button Click Listeners ---
    usersTableEl.addEventListener('click', function(e) {
        const target = e.target.closest('.edit-btn, .suspend-btn, .delete-btn');
        if (!target) return;

        const userId = target.dataset.id;

        if (target.classList.contains('edit-btn')) {
            fetch(`handles/handle-get-user.php?id=${userId}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const form = document.getElementById('edit-user-form');
                        form.querySelector('#edit-user-id').value = data.user.id;
                        form.querySelector('#edit-name').value = data.user.name;
                        form.querySelector('#edit-email').value = data.user.email;
                        form.querySelector('#edit-role').value = data.user.role;
                        form.querySelector('#edit-status').value = data.user.status;
                        editUserModal.show();
                    } else {
                        alert('Error: ' + data.error);
                    }
                });
        } else if (target.classList.contains('suspend-btn')) {
            document.getElementById('suspend-user-id').value = userId;
            suspendUserModal.show();
        } else if (target.classList.contains('delete-btn')) {
            document.getElementById('delete-user-id').value = userId;
            deleteUserModal.show();
        }
    });

    // --- Rate Limit Form Handler ---
    const rateLimitForm = document.getElementById('ratelimit-form');
    if (rateLimitForm) {
        rateLimitForm.addEventListener('submit', (e) => handleFormSubmit(e, 'handles/handle-save-ratelimit-rule.php', null, null, true));
    }
});

// Generic handler for modal forms (Add, Edit)
function handleFormSubmit(form, handlerUrl, dataTable, modal) {
    const submitButton = form.querySelector('button[type="submit"]');
    const errorMessageDiv = form.querySelector('.alert-danger');

    submitButton.disabled = true;
    errorMessageDiv.style.display = 'none';

    const formData = new FormData(form);

    fetch(handlerUrl, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        submitButton.disabled = false;
        if (data.success) {
            modal.hide();
            dataTable.ajax.reload();
        } else {
            errorMessageDiv.textContent = data.error || Object.values(data.errors).join(', ');
            errorMessageDiv.style.display = 'block';
        }
    });
}

// Generic handler for confirmation modals (Suspend, Delete)
function handleConfirmationSubmit(formId, handlerUrl, dataTable, modal) {
    const form = document.getElementById(formId);
    const submitButton = modal._element.querySelector('.btn-warning, .btn-danger');

    submitButton.disabled = true;
    const formData = new FormData(form);

    fetch(handlerUrl, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        submitButton.disabled = false;
        if (data.success) {
            modal.hide();
            dataTable.ajax.reload();
        } else {
            alert('Error: ' + data.error);
            modal.hide();
        }
    });
}
