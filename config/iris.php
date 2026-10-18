<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Setup do banco (php artisan iris:setup)
    |--------------------------------------------------------------------------
    | max_attempts: quantas vezes tentar criar o banco / rodar as migrations
    | antes de desistir. retry_delay: segundos entre as tentativas.
    */
    'setup' => [
        'max_attempts' => (int) env('DB_SETUP_MAX_ATTEMPTS', 5),
        'retry_delay'  => (int) env('DB_SETUP_RETRY_DELAY', 3),
    ],

    /*
    |--------------------------------------------------------------------------
    | Super Admin
    |--------------------------------------------------------------------------
    | Usado pelo SuperAdminBootstrapper. Em produção a senha é obrigatória.
    */
    'super_admin' => [
        'email'     => env('SUPER_ADMIN_EMAIL', 'admin@admin.com'),
        'name'      => env('SUPER_ADMIN_NAME', 'Super Admin'),
        'full_name' => env('SUPER_ADMIN_FULL_NAME', 'Administrator System'),
        'password'  => env('SUPER_ADMIN_PASSWORD'),
    ],

];
