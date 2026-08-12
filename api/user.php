<?php
    session_start();
    require '../config.php';

    header('Content-Type: application/json');

    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'You must be logged in']);
        exit;
    }

    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {

        $result = pg_query_params(
            $db,
            'SELECT id, forenames, surname, title, date_of_birth, mobile_phone, other_phone, email
            FROM users WHERE id = $1',
            [$_SESSION['user_id']]
        );

        $user = pg_fetch_assoc($result);

        if (!$user) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'User not found']);
            exit;
        }

        echo json_encode(['success' => true, 'user' => $user]);
        exit;
    }
    if ($method === 'PUT') {

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) $input = [];

        $forenames = trim($input['forenames'] ?? '');
        $surname   = trim($input['surname'] ?? '');
        $title     = trim($input['title'] ?? '');
        $dob       = trim($input['date_of_birth'] ?? '');
        $mobile    = trim($input['mobile_phone'] ?? '');
        $other     = trim($input['other_phone'] ?? '');
        $email     = strtolower(trim($input['email'] ?? ''));
        $password  = $input['password'] ?? '';       // optional on edit
        $password2 = $input['password_confirm'] ?? '';

        // --- Server-side validation (same rules as registration, minus required password) ---
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

        $phoneRegex = '/^[0-9+\-()\s]{7,20}$/';
        if ($mobile === '') {
            $errors['mobile_phone'] = 'Mobile phone is required';
        } elseif (!preg_match($phoneRegex, $mobile)) {
            $errors['mobile_phone'] = 'Enter a valid phone number';
        }
        if ($other !== '' && !preg_match($phoneRegex, $other)) {
            $errors['other_phone'] = 'Enter a valid phone number';
        }

        if ($email === '') {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Enter a valid email address';
        }

        // Password is optional here - only validate it if they're trying to change it
        if ($password !== '') {
            if (strlen($password) < 8) {
                $errors['password'] = 'Password must be at least 8 characters';
            }
            if ($password !== $password2) {
                $errors['password_confirm'] = 'Passwords do not match';
            }
        }

        if (count($errors) > 0) {
            http_response_code(422);
            echo json_encode(['success' => false, 'errors' => $errors]);
            exit;
        }

        // --- Make sure this email isn't already used by a DIFFERENT user ---
        $check = pg_query_params(
            $db,
            'SELECT id FROM users WHERE LOWER(email) = $1 AND id != $2',
            [$email, $_SESSION['user_id']]
        );
        if (pg_num_rows($check) > 0) {
            http_response_code(422);
            echo json_encode(['success' => false, 'errors' => ['email' => 'This email is already in use']]);
            exit;
        }

        // --- Update, with or without a new password ---
        if ($password !== '') {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $update = pg_query_params(
                $db,
                'UPDATE users SET forenames = $1, surname = $2, title = $3, date_of_birth = $4,
                    mobile_phone = $5, other_phone = $6, email = $7, password_hash = $8
                WHERE id = $9
                RETURNING id, forenames, surname, title, date_of_birth, mobile_phone, other_phone, email',
                [$forenames, $surname, $title, $dob, $mobile, $other ?: null, $email, $password_hash, $_SESSION['user_id']]
            );
        } else {
            $update = pg_query_params(
                $db,
                'UPDATE users SET forenames = $1, surname = $2, title = $3, date_of_birth = $4,
                    mobile_phone = $5, other_phone = $6, email = $7
                WHERE id = $8
                RETURNING id, forenames, surname, title, date_of_birth, mobile_phone, other_phone, email',
                [$forenames, $surname, $title, $dob, $mobile, $other ?: null, $email, $_SESSION['user_id']]
            );
        }

        if (!$update) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Could not update user, please try again']);
            exit;
        }

        $user = pg_fetch_assoc($update);
        echo json_encode(['success' => true, 'user' => $user]);
        exit;
    }

    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);