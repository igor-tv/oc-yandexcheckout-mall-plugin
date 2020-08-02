<?php

use Illuminate\Http\Request;
use OFFLINE\Mall\Models\PaymentGatewaySettings;
use Iweb\YandexCheckoutMall\Classes\YandexCheckout;

Route::post(PaymentGatewaySettings::get('events_url_endpoint'), function (Request $request) {

    $yandexCheckout = new YandexCheckout;

    $yandexCheckout->changePaymentState($request);

    return exit();
});