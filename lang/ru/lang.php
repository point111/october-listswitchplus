<?php return [
    'plugin' => [
        'name' => 'List Switch Plus',
        'description' => 'Переключатели в списках бэкенда, меняющие булево поле модели. Основан на inetis/listswitch.',
    ],
    'listswitch' => [
        'title_true' => 'Нажмите, чтобы переключить на «Нет»',
        'title_false' => 'Нажмите, чтобы переключить на «Да»',
        'text_true' => 'Да',
        'text_false' => 'Нет',
    ],
    'error' => [
        'params_required' => 'Обязательные параметры: id, field, model.',
        'invalid_signature' => 'Неверная подпись запроса.',
    ],
];
