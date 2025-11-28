<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});



Route::get('/test-email', function () {
    Mail::raw("Test email from Brevo SMTP", function($message) {
        $message->to('chandruganesh00@gmail.com');
        $message->subject('Brevo SMTP Test');
    });

    return "Email sent!";
});
