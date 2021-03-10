<?php
return [
    'frontend' => [
        'ecentral/ecstyla/redirect' => [
            'target' => \Ecentral\EcStyla\Middleware\Redirect::class,
            'before' => [
                'typo3/cms-frontend/page-resolver',
            ],
            'after' => [
                'typo3/cms-frontend/static-route-resolver',
            ],
        ],
    ],
];
