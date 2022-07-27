This is a fork of threesquared/laravel-wp-api

# laravel-wp-api

Laravel 8 package for the [Wordpress JSON REST API](https://github.com/WP-API/WP-API)

## Install

Simply add the following line to your `composer.json` and run install/update:

    "rk/laravel-wp-api": "^4.0"

## Configuration

You will need to add the service provider and optionally the facade alias to your `config/app.php`:

```php
'providers' => array(
  mam4dali\LaravelWpApi\ServiceProvider::class
)

'aliases' => array(
  'WpApi' => mam4dali\LaravelWpApi\Facade::class
),
```

And publish the package config files to configure the location of your Wordpress install:

    php artisan vendor:publish

### Usage

The package provides a simplified interface to some of the existing api methods documented [here](http://wp-api.org/).
You can either use the Facade provided or inject the `AstritZeqiri\LaravelWpApi\WpApi` class.

#### Posts
```php
WpApi::posts($page);

```

#### Pages
```php
WpApi::pages($page);

```

#### Post
```php
WpApi::post($slug);

```

```php
WpApi::postId($id);

```

#### Categories
```php
WpApi::categories();

```

#### Tags
```php
WpApi::tags();

```

#### Category posts
```php
WpApi::categoryPosts($slug, $page);

```

#### Author posts
```php
WpApi::authorPosts($slug, $page);

```

#### Tag posts
```php
WpApi::tagPosts($slug, $page);

```

#### Search
```php
WpApi::search($query, $page);

```

#### Archive
```php
WpApi::archive($year, $month, $page);

```
