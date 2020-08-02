<?php return [
    'plugin' => [
        'name' => 'Яндекс Касса для Mall',
        'description' => 'Добавляет платежный провайдер Яндекс.Касса в плагин Mall'
    ],
    'settings' => [
        'yandex_checkout' => 'Яндекс.Касса',
        'shop_id' => 'shopId',
        'shop_id_label' => 'ID магазина',
        'secret_key' => 'Секретный ключ',
        'secret_key_label' => 'Секретный ключ для API',
        'orders_page_url' => 'URL страницы заказов пользователя',
        'orders_page_url_label' => 'Пример: http://site.tld/account/orders',
        'endpoint_url_label' => 'URL для отправки уведомлений со стороны  Яндекс Кассы',
        'set_payed_virtual_order_as_complete' => 'Изменять статус оплаченных виртуальных заказов на "Выполнен"',
    ],
    'messages' => [
        'order_number' => 'Заказ №',
    ]
];