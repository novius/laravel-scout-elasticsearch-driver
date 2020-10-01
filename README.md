# Scout Elasticsearch Driver
[![Travis](https://img.shields.io/travis/novius/laravel-scout-elasticsearch-driver.svg?maxAge=1800&style=flat-square)](https://travis-ci.org/novius/laravel-scout-elasticsearch-driver)
[![Packagist Release](https://img.shields.io/packagist/v/novius/laravel-scout-elasticsearch-driver.svg?maxAge=1800&style=flat-square)](https://packagist.org/packages/novius/laravel-scout-elasticsearch-driver)
[![Licence](https://img.shields.io/packagist/l/novius/laravel-scout-elasticsearch-driver.svg?maxAge=1800&style=flat-square)](https://github.com/novius/laravel-scout-elasticsearch-driver#licence)

This package is an adaptation of [babenkoivan/scout-elasticsearch-driver ](https://github.com/babenkoivan/scout-elasticsearch-driver) to get working with Elasticsearch >= 7.0.0

This package version was created to be compatible with [Elasticsearch "Removal of mapping types"](https://www.elastic.co/guide/en/elasticsearch/reference/7.x/removal-of-types.html#removal-of-types) introduced in Elasticsearch >= 7.0.0

## Features added

* Model's type is now saved in `type` field by default according to [Elasticsearch recommendations](https://www.elastic.co/guide/en/elasticsearch/reference/7.x/removal-of-types.html#_custom_type_field)

* After a model search, an attribute `_score` will be hydrated on your result Model.

Example : 

```php
$results = MyModel::search('keywords')->get();
foreach ($results as $result) {
    // Score is now available in : $result->_score
}
```

* Logs : you can now use Laravel loggers to log ElasticSearch's requests

To enable logs you have to set `SCOUT_ELASTIC_LOG_ENABLED` to `true` and specify which log's channel(s) to use.

Example :

***config/logging.php***
```php
<?php

return [

    ...
    
    'channels' => [

        ...

        'es' => [
            'driver' => 'daily',
            'path' => storage_path('logs/es.log'),
            'level' => 'debug',
            'days' => 5,
        ],
    ],
];
```

* `elastic:reindex` a new command to build and populate a new index with 0 downtime ;

***config/scout_elastic.php***
```php
<?php

return [
    'client' => [
        'hosts' => [
            env('SCOUT_ELASTIC_HOST', 'localhost:9200'),
        ],
    ],
    'document_refresh' => env('SCOUT_ELASTIC_DOCUMENT_REFRESH'),
    'log_enabled' => env('SCOUT_ELASTIC_LOG_ENABLED', false),
    'log_channels' => [],
];
```

## Features deleted from original package

* `elastic:update-mapping` command ;
* `elastic:migrate` command ;
* `elastic:update` command ;
* Mapping of models : replaced by `getDefaultMapping()` of index configurator ;
* Single indexer ;

## Requirements

* PHP >= 7.2
* Laravel Framework >= 5.8
* Elasticsearch >= 7.0.0

## Installation

```sh
composer require novius/laravel-scout-elasticsearch-driver:dev-master
```

## Configuration

To configure the package you need to publish settings first:

```
php artisan vendor:publish --provider="Laravel\Scout\ScoutServiceProvider"
php artisan vendor:publish --provider="Novius\ScoutElastic\ScoutElasticServiceProvider"
```

Then, set the driver setting to `elastic` in the `config/scout.php` file and configure the driver itself in the `config/scout_elastic.php` file.
The available options are:

Option | Description
--- | ---
client | A setting hash to build Elasticsearch client. More information you can find [here](https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/configuration.html#_building_the_client_from_a_configuration_hash). By default the host is set to `localhost:9200`.
document_refresh | This option controls when updated documents appear in the search results. Can be set to `'true'`, `'false'`, `'wait_for'` or `null`. More details about this option you can find [here](https://www.elastic.co/guide/en/elasticsearch/reference/current/docs-refresh.html). By default set to `null`.

Note, that if you use the bulk document indexing you'll probably want to change the chunk size, you can do that in the `config/scout.php` file.

## Usage

Please read the [original package documentation](https://github.com/babenkoivan/scout-elasticsearch-driver). 

## Lint

Run php-cs with:

```sh
composer run-script lint
```

## Contributing

Contributions are welcome!
Leave an issue on Github, or create a Pull Request.


## Licence

This package is under MIT Licence.
