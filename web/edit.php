<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Profile</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="card">
    <div class="profile-header">
        <div>
            <div class="card-icon" style="margin: 0 0 12px 0;">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="8" r="4"></circle>
                    <path d="M4 21c0-4 4-6 8-6s8 2 8 6"></path>
                </svg>
            </div>
            <h1 id="welcomeMsg">Your profile</h1>
            <p class="subtitle">View and update your details.</p>
        </div>
        <button id="logoutBtn" type="button" class="logout-btn">Log out</button>
    </div>

    <div id="alert" class="alert alert-error hidden"></div>
    <div id="successAlert" class="alert alert-success hidden">Your details have been updated.</div>

    <form id="editForm" novalidate class="hidden">
        <div class="row">
            <div>
                <label for="title">Title</label>
                <select id="title" name="title">
                    <option value="">Select...</option>
                    <option>Mr</option>
                    <option>Mrs</option>
                    <option>Miss</option>
                    <option>Ms</option>
                    <option>Dr</option>
                </select>
                <div class="field-error" data-error-for="title"></div>
            </div>
            <div>
                <label for="date_of_birth">Date of birth</label>
                <input type="date" id="date_of_birth" name="date_of_birth">
                <div class="field-error" data-error-for="date_of_birth"></div>
            </div>
        </div>

        <div class="row">
            <div>
                <label for="forenames">Forenames</label>
                <input type="text" id="forenames" name="forenames">
                <div class="field-error" data-error-for="forenames"></div>
            </div>
            <div>
                <label for="surname">Surname</label>
                <input type="text" id="surname" name="surname">
                <div class="field-error" data-error-for="surname"></div>
            </div>
        </div>

        <label for="email">Email address</label>
        <input type="email" id="email" name="email">
        <div class="field-error" data-error-for="email"></div>

        <div class="row">
            <div>
                <label for="mobile_phone">Mobile phone</label>
                <input type="tel" id="mobile_phone" name="mobile_phone">
                <div class="field-error" data-error-for="mobile_phone"></div>
            </div>
            <div>
                <label for="other_phone">Other phone (optional)</label>
                <input type="tel" id="other_phone" name="other_phone">
                <div class="field-error" data-error-for="other_phone"></div>
            </div>
        </div>

        <label for="password">New password <span style="font-weight:400;color:#6b7280;">(leave blank to keep current)</span></label>
        <input type="password" id="password" name="password">
        <div class="field-error" data-error-for="password"></div>

        <label for="password_confirm">Confirm new password</label>
        <input type="password" id="password_confirm" name="password_confirm">
        <div class="field-error" data-error-for="password_confirm"></div>

        <button type="submit" id="submitBtn">
            <span id="submitBtnText">Save changes</span>
            <span id="submitBtnSpinner" class="spinner hidden"></span>
        </button>
    </form>
</div>

<script>
var form = document.getElementById('editForm');
var alertBox = document.getElementById('alert');
var successAlert = document.getElementById('successAlert');
var submitBtn = document.getElementById('submitBtn');

function clearErrors() {
    alertBox.classList.add('hidden');
    successAlert.classList.add('hidden');
    document.querySelectorAll('.field-error').forEach(function (el) { el.textContent = ''; });
    document.querySelectorAll('.invalid').forEach(function (el) { el.classList.remove('invalid'); });
}

function showFieldError(field, message) {
    var input = document.getElementById(field);
    var errorEl = document.querySelector('[data-error-for="' + field + '"]');
    if (input) input.classList.add('invalid');
    if (errorEl) errorEl.textContent = message;
}

function loadUser() {
    fetch('../api/user.php', { method: 'GET' })
        .then(function (res) {
            if (res.status === 401) {
                window.location.href = 'login.php';
                return null;
            }
            return res.json();
        })
        .then(function (result) {
            if (!result) return;

            if (!result.success) {
                alertBox.classList.remove('hidden');
                alertBox.textContent = result.message || 'Could not load your details';
                return;
            }

            var user = result.user;
            document.getElementById('welcomeMsg').textContent = 'Welcome, ' + user.forenames + '!';
            form.title.value = user.title;
            form.date_of_birth.value = user.date_of_birth;
            form.forenames.value = user.forenames;
            form.surname.value = user.surname;
            form.email.value = user.email;
            form.mobile_phone.value = user.mobile_phone;
            form.other_phone.value = user.other_phone || '';

            form.classList.remove('hidden');
        })
        .catch(function () {
            alertBox.classList.remove('hidden');
            alertBox.textContent = 'Could not reach the server';
        });
}

function validate(data) {
    var errors = {};

    if (!data.forenames.trim()) errors.forenames = 'Forenames are required';
    if (!data.surname.trim()) errors.surname = 'Surname is required';
    if (!data.title) errors.title = 'Please select a title';

    if (!data.date_of_birth) {
        errors.date_of_birth = 'Date of birth is required';
    } else if (new Date(data.date_of_birth) > new Date()) {
        errors.date_of_birth = 'Date of birth cannot be in the future';
    }

    var phoneRegex = /^[0-9+\-()\s]{7,20}$/;
    if (!data.mobile_phone.trim()) {
        errors.mobile_phone = 'Mobile phone is required';
    } else if (!phoneRegex.test(data.mobile_phone)) {
        errors.mobile_phone = 'Enter a valid phone number';
    }
    if (data.other_phone && !phoneRegex.test(data.other_phone)) {
        errors.other_phone = 'Enter a valid phone number';
    }

    var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!data.email.trim()) {
        errors.email = 'Email is required';
    } else if (!emailRegex.test(data.email)) {
        errors.email = 'Enter a valid email address';
    }

    if (data.password || data.password_confirm) {
        if (data.password.length < 8) {
            errors.password = 'Password must be at least 8 characters';
        }
        if (data.password !== data.password_confirm) {
            errors.password_confirm = 'Passwords do not match';
        }
    }

    return errors;
}

form.addEventListener('submit', function (e) {
    e.preventDefault();
    clearErrors();

    var data = Object.fromEntries(new FormData(form).entries());

    var clientErrors = validate(data);
    if (Object.keys(clientErrors).length > 0) {
        Object.keys(clientErrors).forEach(function (field) {
            showFieldError(field, clientErrors[field]);
        });
        return;
    }

    submitBtn.disabled = true;
    document.getElementById('submitBtnText').textContent = 'Saving...';
    document.getElementById('submitBtnSpinner').classList.remove('hidden');

    fetch('../api/user.php', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(function (res) { return res.json(); })
    .then(function (result) {
        if (result.success) {
            successAlert.classList.remove('hidden');
            form.password.value = '';
            form.password_confirm.value = '';
        } else if (result.errors) {
            Object.keys(result.errors).forEach(function (field) {
                showFieldError(field, result.errors[field]);
            });
        } else {
            alertBox.classList.remove('hidden');
            alertBox.textContent = result.message || 'Something went wrong';
        }
    })
    .catch(function () {
        alertBox.classList.remove('hidden');
        alertBox.textContent = 'Could not reach the server';
    })
    .finally(function () {
        submitBtn.disabled = false;
        document.getElementById('submitBtnText').textContent = 'Save changes';
        document.getElementById('submitBtnSpinner').classList.add('hidden');
    });
});

loadUser();

document.getElementById('logoutBtn').addEventListener('click', function () {
    fetch('../api/logout.php', { method: 'POST' })
        .then(function () {
            window.location.href = 'login.php';
        });
});
</script>
</body>
</html>