<?php
// Email configuration - loads from .env file for security
// For Gmail: Use App Password (not regular password)
// Generate App Password: https://myaccount.google.com/apppasswords

// Load environment variables
require_once __DIR__ . '/../includes/env_loader.php';
$env = loadEnv(__DIR__ . '/../.env');

// Return config with values from .env, with fallback defaults
return [
    'smtp_host' => $env['SMTP_HOST'] ?? 'smtp.gmail.com',
    'smtp_port' => isset($env['SMTP_PORT']) ? (int)$env['SMTP_PORT'] : 587,
    'smtp_username' => $env['SMTP_USERNAME'] ?? '',
    'smtp_password' => $env['SMTP_PASSWORD'] ?? '',
    'smtp_from_email' => $env['SMTP_FROM_EMAIL'] ?? $env['SMTP_USERNAME'] ?? '',
    'smtp_from_name' => $env['SMTP_FROM_NAME'] ?? 'Movie Library',
    'use_smtp' => isset($env['USE_SMTP']) ? filter_var($env['USE_SMTP'], FILTER_VALIDATE_BOOLEAN) : true,
];

