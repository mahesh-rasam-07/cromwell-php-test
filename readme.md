# Cromwell PHP Test - User Registration, Login & Profile

Simple PHP + PostgreSQL user management system built without any framework, using core PHP functions and vanilla JS.

## Features

- User registration with all required fields
- Login with session-based authentication
- Edit profile page (view + update details, password optional)
- Logout with session destroy
- Client-side validation (instant feedback before submitting)
- Server-side validation (all fields re-checked on the backend)
- Passwords hashed using bcrypt via password_hash()
- Parameterized SQL queries to prevent injection
- Duplicate email check on both registration and edit
- JSON API response format
- Responsive layout, gradient UI, loading spinner on buttons

## Tech Used

- PHP (core pg_* functions, no framework/ORM)
- PostgreSQL
- HTML/CSS/JavaScript
- Tested locally using XAMPP

## Folder Structure

cromwell-app/
├── config.example.php
├── api/
│ ├── register.php
│ ├── login.php
│ ├── logout.php
│ └── user.php
├── web/
│ ├── registration.php
│ ├── login.php
│ ├── edit.php
│ └── style.css
└── postgres/
└── schema.sql

## Setup

### Requirements

- PHP 8+ with pgsql extension enabled
- PostgreSQL 12+
- XAMPP (or similar local server)

### 1. Create the database

```bash
createdb cromwell_test
psql -U postgres -d cromwell_test -f postgres/schema.sql
```

### 2. Configure the app

Copy the example config file:

```bash
cp config.example.php config.php
```

Open `config.php` and set your own PostgreSQL password in `$db_pass`.

### 3. Enable pgsql in XAMPP

In `php.ini`, uncomment:
extension=pgsql
extension=pdo_pgsql

Restart Apache after saving.

### 4. Run the project

Place the folder inside `htdocs` and open:

http://localhost/cromwell-app/web/registration.php

### 5. Confirm it worked

```bash
psql -U postgres -d cromwell_test -c "SELECT * FROM users;"
```

## Pages

| Page | Description |
|---|---|
| `/web/registration.php` | Create a new account, redirects to login on success |
| `/web/login.php` | Log in with email + password, redirects to edit profile on success |
| `/web/edit.php` | View/update your details, requires login, has logout button |

## API

### POST /api/register.php

Request body:

```json
{
  "forenames": "John",
  "surname": "Smith",
  "title": "Mr",
  "date_of_birth": "1990-01-01",
  "mobile_phone": "07123456789",
  "other_phone": "",
  "email": "john@example.com",
  "password": "SecurePass123",
  "password_confirm": "SecurePass123"
}
```

Success:

```json
{ "success": true, "user": { "id": 1, "forenames": "John", "surname": "Smith", "email": "john@example.com" } }
```

Error:

```json
{ "success": false, "errors": { "email": "This email is already registered" } }
```

### POST /api/login.php

Request body:

```json
{ "email": "john@example.com", "password": "SecurePass123" }
```

Success:

```json
{ "success": true, "user": { "id": 1, "forenames": "John", "surname": "Smith", "email": "john@example.com" } }
```

Error:

```json
{ "success": false, "message": "Invalid email or password" }
```

### GET /api/user.php

Requires an active session (must be logged in). Returns the logged-in user's details.

```json
{ "success": true, "user": { "id": 1, "forenames": "John", "surname": "Smith", "title": "Mr", "date_of_birth": "1990-01-01", "mobile_phone": "07123456789", "other_phone": "", "email": "john@example.com" } }
```

### PUT /api/user.php

Requires an active session. Updates the logged-in user's own details. Password fields are optional - only validated/updated if provided.

Request body:

```json
{
  "forenames": "John",
  "surname": "Smith",
  "title": "Mr",
  "date_of_birth": "1990-01-01",
  "mobile_phone": "07123456789",
  "other_phone": "",
  "email": "john@example.com",
  "password": "",
  "password_confirm": ""
}
```

### POST /api/logout.php

Destroys the session. No body required.

```json
{ "success": true }
```

## Notes

- config.php is not pushed to GitHub, use config.example.php as reference
- Session-based auth is used throughout - GET/PUT/logout endpoints check `$_SESSION['user_id']`