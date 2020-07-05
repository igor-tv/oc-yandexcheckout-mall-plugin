<?php namespace Iweb\YandexCheckoutMall;

use Iweb\YandexCheckoutMall\Classes\YandexCheckout;
use Iweb\YandexCheckoutMall\Classes\DefaultMoneyRepair;
use OFFLINE\Mall\Classes\Payments\PaymentGateway;
use OFFLINE\Mall\Classes\Utils\Money;
use System\Classes\PluginBase;

class Plugin extends PluginBase
{
    /**
     * @var array Plugin dependencies
     */
    public $require = ['Offline.Mall'];

    public function boot()
    {
        $gateway = $this->app->get(PaymentGateway::class);
        $gateway->registerProvider(new YandexCheckout());

        // For solve this issue https://github.com/OFFLINE-GmbH/oc-mall-plugin/issues/258
        $this->app->singleton(Money::class, function () {
            return new DefaultMoneyRepair();
        });
    }
    public function registerComponents()
    {
    }

    public function registerSettings()
    {
    }

}
