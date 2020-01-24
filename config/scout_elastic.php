<?php

return [
    'client' => [
        'hosts' => [
            env('SCOUT_ELASTIC_HOST', 'localhost:9200'),
        ],
    ],
    'indexer' => env('SCOUT_ELASTIC_INDEXER', 'single'),
    'document_refresh' => env('SCOUT_ELASTIC_DOCUMENT_REFRESH'),
    'log_enabled' => env('SCOUT_ELASTIC_LOG_ENABLED', false),
    'log_channels' => [],
];
