<?php

return [
    'client' => [
        'hosts' => [
            env('SCOUT_ELASTIC_HOST', 'localhost:9200'),
        ],
    ],
    'document_refresh' => env('SCOUT_ELASTIC_DOCUMENT_REFRESH'),
    'searchable_models' => [],
    'log_enabled' => env('SCOUT_ELASTIC_LOG_ENABLED', false),
    'log_channels' => [],
];
