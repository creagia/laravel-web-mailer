<?php

use Creagia\LaravelWebMailer\Controllers\LaravelWebMailController;
use Illuminate\Support\Facades\Route;

Route::prefix(config('web-mailer.route.prefix'))
    ->middleware(config('web-mailer.route.middleware'))
    ->controller(LaravelWebMailController::class)
    ->group(function () {
        Route::get('/', 'index')->name('laravelWebMailer.index');
        Route::get('/all', 'fetchAll')->name('laravelWebMailer.fetchAll');
        Route::get('/message/{messageId}', 'fetch')->name('laravelWebMailer.fetch');
        Route::get('/message/{messageId}/attachment/{index}', 'downloadAttachment')->name('laravelWebMailer.downloadAttachment');
        Route::delete('/all-messages', 'destroy')->name('laravelWebMailer.destroy');
    });
