<?php return [
    'plugin' => [
        'name' => 'List Switch Plus',
        'description' => 'Switch buttons in backend lists that toggle a boolean model field. Based on inetis/listswitch.',
    ],
    'listswitch' => [
        'title_true' => 'Click to switch to No',
        'title_false' => 'Click to switch to Yes',
        'text_true' => 'Yes',
        'text_false' => 'No',
    ],
    'error' => [
        'params_required' => 'Following parameters are required: id, field, model.',
        'invalid_signature' => 'Invalid request signature.',
    ],
];
