<?php
    session_start();
    require '../config.php';

    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) $input = [];

    $forenames  = trim($input['forenames'] ?? '');
    $surname    = trim($input['surname'] ?? '');
    $title      = trim($input['title'] ?? '');
    $dob        = trim($input['date_of_birth'] ?? '');
    $mobile     = trim($input['mobile_phone'] ?? '');
    $other      = trim($input['other_phone'] ?? '');
    $email      = strtolower(trim($input['email'] ?? ''));
    $password   = $input['password'] ?? '';
    $password2  = $input['password_confirm'] ?? '';

    $errors = [];

    if ($forenames === '') $errors['forenames'] = 'Forenames are required';
    if ($surname === '') $errors['surname'] = 'Surname is required';
    if ($title === '') $errors['title'] = 'Title is required';

    if ($dob === '') {
        $errors['date_of_birth'] = 'Date of birth is required';
    } else {
        $d = DateTime::createFromFormat('Y-m-d', $dob);
        if (!$d || $d->format('Y-m-d') !== $dob) {
            $errors['date_of_birth'] = 'Enter a valid date';
        } elseif ($d > new DateTime()) {
            $errors['date_of_birth'] = 'Date of birth cannot be in the future';
        }
    }

    if ($mobile === '') {
        $errors['mobile_phone'] = 'Mobile phone is required';
    } elseif (!preg_match('/^[0-9+\-()\s]{7,20}$/', $mobile)) {
        $errors['mobile_phone'] = 'Enter a valid phone number';
    }

    if ($other !== '' && !preg_match('/^[0-9+\-()\s]{7,20}$/', $other)) {
        $errors['other_phone'] = 'Enter a valid phone number';
    }

    if ($email === '') {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Enter a valid email address';
    }

    if (strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters';
    }
    if ($password !== $password2) {
        $errors['password_confirm'] = 'Passwords do not match';
    }

    if (count($errors) > 0) {
        http_response_code(422);
        echo json_encode(['success' => false, 'errors' => $errors]);
        exit;
    }

    $check = pg_query_params($db, 'SELECT id FROM users WHERE LOWER(email) = $1', [$email]);
    if (pg_num_rows($check) > 0) {
        http_response_code(422);
        echo json_encode(['success' => false, 'errors' => ['email' => 'This email is already registered']]);
        exit;
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    $insert = pg_query_params(
        $db,
        'INSERT INTO users (forenames, surname, title, date_of_birth, mobile_phone, other_phone, email, password_hash)
        VALUES ($1, $2, $3, $4, $5, $6, $7, $8)
        RETURNING id, forenames, surname, email',
        [$forenames, $surname, $title, $dob, $mobile, $other ?: null, $email, $password_hash]
    );

    if (!$insert) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Could not save user, please try again']);
        exit;
    }

    $user = pg_fetch_assoc($insert);
    $_SESSION['user_id'] = $user['id'];

    http_response_code(201);
    echo json_encode(['success' => true, 'user' => $user]);