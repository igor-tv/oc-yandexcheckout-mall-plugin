<?php return [
    'plugin' => [
        'name' => 'Yandex Checkout for MALL',
        'description' => 'Payment provider Yandex Checkout for the MALL plugin'
    ],
    'settings' => [
        'yandex_checkout' => 'Yandex Checkout',
        'shop_id' => 'shopId',
        'shop_id_label' => 'Your shop ID',
        'secret_key' => 'The secret key',
        'secret_key_label' => 'API secret key',
        'url_for_notifications' => 'URL for notifications',
        'url_for_notifications_label' => 'URL on your system to which event notifications will come. Example: /yandex-checkout',
        'orders_page_url' => 'URL of frontend orders page',
        'orders_page_url_label' => 'Example: http://site.tld/account/orders',
        'set_payed_virtual_order_as_complete' => 'Change the status of paid virtual orders to "Done"',
    ],
    'messages' => [
        'order_number' => 'Order #',
    ]
];