<?php

use Illuminate\Http\Request;
use Iweb\YandexCheckoutMall\Classes\YandexCheckout;

Route::post('/yandex-checkout', function (Request $request) {

    $yandexCheckout = new YandexCheckout;

    $yandexCheckout->changePaymentState($request);

    return exit();
});