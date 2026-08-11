CREATE DATABASE cromwell_test;

CREATE TABLE IF NOT EXISTS users (
    id             SERIAL PRIMARY KEY,
    forenames      VARCHAR(100) NOT NULL,
    surname        VARCHAR(100) NOT NULL,
    title          VARCHAR(20)  NOT NULL,
    date_of_birth  DATE         NOT NULL,
    mobile_phone   VARCHAR(30)  NOT NULL,
    other_phone    VARCHAR(30),
    email          VARCHAR(255) NOT NULL UNIQUE,
    password_hash  VARCHAR(255) NOT NULL,
    created_at     TIMESTAMP NOT NULL DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_users_email_lower ON users (LOWER(email));