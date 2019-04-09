<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'web'], function() {
    Route::get('/config', function () {
        return config('opendialog.core');
    });

    Route::post('/incoming/webchat', 'OpenDialogAi\SensorEngine\Http\Controllers\WebchatIncomingController@receive');
});
