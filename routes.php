<?php

use Illuminate\Http\Request;
use Iweb\YandexCheckoutMall\Classes\YandexCheckout;

Route::post('/yandex-checkout', function (Request $request) {

    $yandexKassa = new YandexCheckout;

    $yandexKassa->changePaymentState($request);

    return exit();
});