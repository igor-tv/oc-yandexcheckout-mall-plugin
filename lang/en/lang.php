<?php return [
    'plugin' => [
        'name' => 'Yandex Checkout for Mall',
        'description' => 'Payment provider Yandex Checkout for the Mall plugin'
    ],
    'settings' => [
        'yandex_checkout' => 'Yandex Checkout',
        'shop_id' => 'shopId',
        'shop_id_label' => 'Your shop ID',
        'secret_key' => 'The secret key',
        'secret_key_label' => 'API secret key',
        'orders_page_url' => 'URL of frontend orders page',
        'orders_page_url_label' => 'Example: http://site.tld/account/orders',
        'endpoint_url_label' => 'URL for sending notifications from Yandex Checkout',
        'set_payed_virtual_order_as_complete' => 'Change the status of paid virtual orders to "Done"',
    ],
    'messages' => [
        'order_number' => 'Order #',
    ]
];