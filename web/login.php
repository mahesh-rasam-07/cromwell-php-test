<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="card">
    <div class="card-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="11" width="18" height="11" rx="2"></rect>
            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
        </svg>
    </div>
    <h1>Welcome back</h1>
    <p class="subtitle">Log in with your email and password.</p>

    <div id="alert" class="alert alert-error hidden"></div>

    <form id="loginForm" novalidate>
        <label for="email">Email address</label>
        <input type="email" id="email" name="email">
        <div class="field-error" data-error-for="email"></div>

        <label for="password">Password</label>
        <input type="password" id="password" name="password">
        <div class="field-error" data-error-for="password"></div>

        <button type="submit" id="submitBtn">
            <span id="submitBtnText">Log in</span>
            <span id="submitBtnSpinner" class="spinner hidden"></span>
        </button>
    </form>

    <p class="subtitle" style="text-align:center; margin-top:20px;">
        Don't have an account? <a href="registration.php">Register here</a>
    </p>
</div>

<script>
var form = document.getElementById('loginForm');
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
    var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (!data.email.trim()) {
        errors.email = 'Email is required';
    } else if (!emailRegex.test(data.email)) {
        errors.email = 'Enter a valid email address';
    }

    if (!data.password) {
        errors.password = 'Password is required';
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
    document.getElementById('submitBtnText').textContent = 'Logging in...';
    document.getElementById('submitBtnSpinner').classList.remove('hidden');

    fetch('../api/login.php', {
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
            alertBox.textContent = 'Login successful! Redirecting...';
            setTimeout(function () {
                window.location.href = 'edit.php';
            }, 800);
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
        document.getElementById('submitBtnText').textContent = 'Log in';
        document.getElementById('submitBtnSpinner').classList.add('hidden');
    });
});
</script>
</body>
</html>