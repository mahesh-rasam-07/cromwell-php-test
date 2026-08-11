<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Register</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="card">
    <h1>Create an account</h1>
    <p class="subtitle">Fill in your details to register.</p>

    <div id="alert" class="alert alert-error hidden"></div>

    <form id="registrationForm" novalidate>
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

        <label for="password">Password</label>
        <input type="password" id="password" name="password">
        <div class="field-error" data-error-for="password"></div>

        <label for="password_confirm">Confirm password</label>
        <input type="password" id="password_confirm" name="password_confirm">
        <div class="field-error" data-error-for="password_confirm"></div>

        <button type="submit" id="submitBtn">Register</button>
    </form>
</div>

<script>
var form = document.getElementById('registrationForm');
var alertBox = document.getElementById('alert');
var submitBtn = document.getElementById('submitBtn');

function clearErrors() {
    alertBox.classList.add('hidden');
    document.querySelectorAll('.field-error').forEach(function (el) { el.textContent = ''; });
    document.querySelectorAll('.invalid').forEach(function (el) { el.classList.remove('invalid'); });
}

function showFieldError(field, message) {
    var input = document.getElementById(field);
    var errorEl = document.querySelector('[data-error-for="' + field + '"]');
    if (input) input.classList.add('invalid');
    if (errorEl) errorEl.textContent = message;
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

    if (data.password.length < 8) {
        errors.password = 'Password must be at least 8 characters';
    }
    if (data.password !== data.password_confirm) {
        errors.password_confirm = 'Passwords do not match';
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
    submitBtn.textContent = 'Registering...';

    fetch('../api/register.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(function (res) { return res.json().then(function (body) { return { status: res.status, body: body }; }); })
    .then(function (result) {
        if (result.body.success) {
            alertBox.classList.remove('hidden');
            alertBox.classList.remove('alert-error');
            alertBox.classList.add('alert-success');
            alertBox.textContent = 'Registration successful! Redirecting...';
            setTimeout(function () {
                window.location.href = 'registration.php';
            }, 1200);
        } else if (result.body.errors) {
            Object.keys(result.body.errors).forEach(function (field) {
                showFieldError(field, result.body.errors[field]);
            });
        } else {
            alertBox.classList.remove('hidden');
            alertBox.textContent = result.body.message || 'Something went wrong';
        }
    })
    .catch(function () {
        alertBox.classList.remove('hidden');
        alertBox.textContent = 'Could not reach the server';
    })
    .finally(function () {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Register';
    });
});
</script>
</body>
</html>