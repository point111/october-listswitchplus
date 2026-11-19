<?php return [
    'plugin' => [
        'name' => 'List Switch Plus',
        'description' => "Interrupteurs dans les listes de l'administration qui inversent un champ booléen du modèle. Basé sur inetis/listswitch.",
    ],
    'listswitch' => [
        'title_true' => 'Cliquez pour passer le statut à Non',
        'title_false' => 'Cliquez pour passer le statut à Oui',
        'text_true' => 'Oui',
        'text_false' => 'Non',
    ],
    'error' => [
        'params_required' => 'Les paramètres suivants sont requis : id, field, model.',
        'invalid_signature' => 'Signature de la requête invalide.',
    ],
];
