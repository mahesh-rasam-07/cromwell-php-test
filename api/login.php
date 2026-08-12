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

    $email    = strtolower(trim($input['email'] ?? ''));
    $password = $input['password'] ?? '';

    $errors = [];

    if ($email === '') {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Enter a valid email address';
    }

    if ($password === '') {
        $errors['password'] = 'Password is required';
    }

    if (count($errors) > 0) {
        http_response_code(422);
        echo json_encode(['success' => false, 'errors' => $errors]);
        exit;
    }

    $result = pg_query_params(
        $db,
        'SELECT id, forenames, surname, email, password_hash FROM users WHERE LOWER(email) = $1',
        [$email]
    );
    $user = pg_fetch_assoc($result);

    if (!$user || !password_verify($password, $user['password_hash'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
        exit;
    }

    $_SESSION['user_id'] = $user['id'];
    unset($user['password_hash']);

    echo json_encode(['success' => true, 'user' => $user]);