<?php namespace Iweb\YandexCheckoutMall\Classes;

use OFFLINE\Mall\Classes\Payments\PaymentProvider;
use OFFLINE\Mall\Classes\Payments\PaymentResult;
use OFFLINE\Mall\Models\PaymentGatewaySettings;
use OFFLINE\Mall\Models\OrderState;
use OFFLINE\Mall\Models\Order;
use Omnipay\Omnipay;
use Throwable;
use Session;
use Lang;


class YandexCheckout extends PaymentProvider
{
    /**
     * The order that is being paid.
     *
     * @var \OFFLINE\Mall\Models\Order
     */
    public $order;
    /**
     * Data that is needed for the payment.
     * Card numbers, tokens, etc.
     *
     * @var array
     */
    public $data;

    /**
     * Return the display name of your payment provider.
     *
     * @return string
     */
    public function name(): string
    {
        return Lang::get('iweb.yandexcheckoutmall::lang.settings.yandex_checkout');
    }

    /**
     * Return a unique identifier for this payment provider.
     *
     * @return string
     */
    public function identifier(): string
    {
        return 'yandex-kassa';
    }

    /**
     * Validate the given input data for this payment.
     *
     * @return bool
     * @throws \October\Rain\Exception\ValidationException
     */
    public function validate(): bool
    {
        return true;
    }


    /**
     * Process the payment.
     *
     * @param PaymentResult $result
     *
     * @return PaymentResult
     */
    public function process(PaymentResult $result): PaymentResult
    {
        $gateway = $this->getGateway();

        $response = null;
        try {
            $response = $gateway->purchase([
                'amount'        => $this->order->total_in_currency,
                'currency'      => $this->order->currency['code'],
                'capture'       => true,
                'returnUrl'     => $this->returnUrl(),
                'cancelUrl'     => $this->cancelUrl(),
                'transactionId' => uniqid('', true),
                'description'   => Lang::get('iweb.yandexcheckoutmall::lang.messages.order_number').$this->order->order_number,
                'metadata'      => array(
                'order_id'      => $this->order->id,
                ),
            ])->send();
        } catch (Throwable $e) {
            return $result->fail([], $e);
        }

        // PayPal has to return a RedirectResponse if everything went well
        if ( ! $response->isRedirect()) {
            return $result->fail((array)$response->getData(), $response);
        }

        Session::put('mall.payment.callback', self::class);
        Session::put('mall.yandex-kassa.transactionReference', $response->getTransactionReference());

        $this->setOrder($result->order);
        $result->order->payment_transaction_id = $response->getTransactionReference();
        $result->order->save();

        return $result->redirect($response->getRedirectResponse()->getTargetUrl());
    }

    /**
     * Y.K. has processed the payment and redirected the user back.
     *
     * @param PaymentResult $result
     *
     * @return PaymentResult
     */
    public function complete(PaymentResult $result): PaymentResult
    {
        return $result->redirect(PaymentGatewaySettings::get('ordersPage'));
    }

    /***
     * Изменение статуса платежа и статуса заказа
     * по входящему уведомлению от Яндекс.Кассы
     * https://kassa.yandex.ru/developers/using-api/webhooks
     * @param $response
     * @return PaymentResult
     */
    public function changePaymentState ($response)
    {
        $responseAll = $response->all();

        $order = Order::where('payment_transaction_id', $responseAll['object']['id'])->firstOrFail();

        $this->setOrder($response->order);

        $result = new PaymentResult($this, $order);

        try {
            $response = $this->getGateway()->details([
                'transactionReference' => $responseAll['object']['id']
            ])->send();
        } catch (Throwable $e) {
            return $result->fail([], $e);
        }

        $data = (array)$response->getData();

        switch ($responseAll['event']){
            case 'payment.succeeded':
                $result->success($data, $response);

                if ($order->is_virtual === 1 and PaymentGatewaySettings::get('setPayedVirtualOrderAsComplete')) {
                    $order->order_state_id = $this->getOrderStateId(OrderState::FLAG_COMPLETE);
                    $order->save();
                }

                \Event::fire('mall.checkout.succeeded', $result);
                break;
            case 'payment.canceled':
                $result->fail($data, $response);
                $order->order_state_id = $this->getOrderStateId(OrderState::FLAG_CANCELLED);
                $order->save();

                break;
            case 'refund.succeeded':
                $result->pending();
                $order->order_state_id = $this->getOrderStateId(OrderState::FLAG_COMPLETE);
                $order->save();
                break;
            case 'payment.waiting_for_capture':
                // not used
                $result->pending();
            default:
                \Event::fire('mall.checkout.failed', $result);
                return $result->fail($data, $response);
        }
    }

    /**
     * Build the Omnipay Gateway for PayPal.
     *
     * @return \Omnipay\Common\GatewayInterface
     */
    protected function getGateway()
    {
        $gateway = Omnipay::create('YandexKassa');

        $gateway->setShopId(PaymentGatewaySettings::get('shopId'));
        $gateway->setSecret(decrypt(PaymentGatewaySettings::get('api_key')));

        return $gateway;
    }

    /**
     * Return any custom backend settings fields.
     *
     * These fields will be rendered in the backend
     * settings page of your provider.
     *
     * @return array
     */
    public function settings(): array
    {
        return [
            'endpointUrl' => [
                'label'   => '',
                'span'    => 'left',
                'type'    => 'partial',
                'path'    => '$/iweb/yandexcheckoutmall/view/_endpoint_url.htm'
            ],
            'shopId'     => [
                'label'   => Lang::get('iweb.yandexcheckoutmall::lang.settings.shop_id'),
                'comment' => Lang::get('iweb.yandexcheckoutmall::lang.settings.shop_id_label'),
                'span'    => 'left',
                'type'    => 'text',
            ],
            'api_key' => [
                'label'   => Lang::get('iweb.yandexcheckoutmall::lang.settings.secret_key'),
                'comment' => Lang::get('iweb.yandexcheckoutmall::lang.settings.secret_key_label'),
                'span'    => 'left',
                'type'    => 'text',
            ],
            'ordersPage' => [
                'label'   => Lang::get('iweb.yandexcheckoutmall::lang.settings.orders_page_url'),
                'comment' => Lang::get('iweb.yandexcheckoutmall::lang.settings.orders_page_url_label'),
                'span'    => 'left',
                'type'    => 'text',
            ],
            'setPayedVirtualOrderAsComplete' => [
                'label'   => Lang::get('iweb.yandexcheckoutmall::lang.settings.set_payed_virtual_order_as_complete'),
                'span'    => 'left',
                'type'    => 'checkbox',
            ],
        ];
    }

    /**
     * Setting keys returned from this method are stored encrypted.
     *
     * Use this to store API tokens and other secret data
     * that is needed for this PaymentProvider to work.
     *
     * @return array
     */
    public function encryptedSettings(): array
    {
        return ['api_key'];
    }

    /**
     * Getting order state id by flag
     *
     * @param $orderStateFlag
     * @return int
     */
    protected function getOrderStateId($orderStateFlag): int
    {
        $orderStateModel = OrderState::where('flag', $orderStateFlag)->first();

        return $orderStateModel->id;
    }
}
