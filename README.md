# Scout Elasticsearch Driver
[![Travis](https://img.shields.io/travis/novius/laravel-scout-elasticsearch-driver.svg?maxAge=1800&style=flat-square)](https://travis-ci.org/novius/laravel-scout-elasticsearch-driver)
[![Packagist Release](https://img.shields.io/packagist/v/novius/laravel-scout-elasticsearch-driver.svg?maxAge=1800&style=flat-square)](https://packagist.org/packages/novius/laravel-scout-elasticsearch-driver)
[![Licence](https://img.shields.io/packagist/l/novius/laravel-scout-elasticsearch-driver.svg?maxAge=1800&style=flat-square)](https://github.com/novius/laravel-scout-elasticsearch-driver#licence)

This package is an adaptation of [babenkoivan/scout-elasticsearch-driver ](https://github.com/babenkoivan/scout-elasticsearch-driver) to get working with Elasticsearch >= 7.0.0

This package version was created to be compatible with [Elasticsearch "Removal of mapping types"](https://www.elastic.co/guide/en/elasticsearch/reference/7.x/removal-of-types.html#removal-of-types) introduced in Elasticsearch >= 7.0.0

> **WARNING**: this package is currently in development.

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

## Features deleted from original package

* `elastic:update-mapping` command ;
* `elastic:migrate` command ;
* Mapping of models : replaced by `getDefaultMapping()` of index configurator ;


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
indexer | Set to `single` for the single document indexing and to `bulk` for the bulk document indexing. By default is set to `single`.
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
