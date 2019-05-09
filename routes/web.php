<?php

use Illuminate\Support\Facades\Route;
use OpenDialogAi\Core\Http\Middleware\RequestLoggerMiddleware;

Route::group(['middleware' => 'web'], function() {
    Route::get('/config', function () {
        return config('opendialog.core');
    });

    Route::post(
        '/incoming/webchat',
        'OpenDialogAi\SensorEngine\Http\Controllers\WebchatIncomingController@receive'
    )->middleware(RequestLoggerMiddleware::class);

    Route::get(
        '/chat-init/webchat/{user_id}/{limit}',
        'OpenDialogAi\ConversationLog\Http\Controllers\WebchatInitController@receive'
    );
});
