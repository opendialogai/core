<?php

use Illuminate\Support\Facades\Route;
use OpenDialogAi\Core\Http\Middleware\RequestLoggerMiddleware;

Route::group(['middleware' => 'web'], function () {
    Route::get('/config', function () {
        return config('opendialog.core');
    });

    Route::post(
        '/incoming/webchat',
        'OpenDialogAi\SensorEngine\Http\Controllers\WebchatIncomingController@receive'
    )->middleware(RequestLoggerMiddleware::class);

    Route::get(
        '/user/{user_id}/history',
        'OpenDialogAi\ConversationLog\Http\Controllers\WebchatInitController@receive'
    );
});
