
This is a fork of threesquared/laravel-wp-api

  

# laravel-wp-api

  

Laravel 9 package for the [Wordpress JSON REST API](https://github.com/WP-API/WP-API)

  

## Install
Run:
`composer require mam4dali/laravel-wp-api` 


## Configuration

  

You will need to add the service provider and optionally the facade alias to your `config/app.php`:

  

```php

'providers' => array(

mam4dali\LaravelWpApi\ServiceProvider::class

)

  

'aliases' => Facade::defaultAliases()->merge([

'WpApi' => mam4dali\LaravelWpApi\Facade::class,

])->toArray(),

```

  

And publish the package config files to configure the location of your Wordpress install:

  

php artisan vendor:publish

  

### Usage

You need to install the following plugin in WordPress:

https://wordpress.org/plugins/jwt-authentication-for-wp-rest-api/

  

Example:

```php

$wp_api = new WpApi('http://localhost/wp/wp-json/wp/v2/', new \GuzzleHttp\Client(), null);

$jwt_token = $wp_api->jwtTokenGenerate('username', 'password');

$wp_api->SetJwtToken($jwt_token['token']);

$get_post = $wp_api->postId(1);

```

**Important: No need to continuously generate tokens. Each token can work up to 7 days. you can save token for 7 days

**

  

The package provides a simplified interface to some of the existing api methods documented [here](http://wp-api.org/).

You can either use the Facade provided or inject the `AstritZeqiri\LaravelWpApi\WpApi` class.

  

#### Posts

```php

$wp_api->posts($page);

  

```

  

#### Pages

```php

$wp_api->pages($page);

  

```

  

#### Post

```php

$wp_api->post($slug);

  

```

  

```php

$wp_api->postId($id);

  

```

  

#### Categories

```php

$wp_api->categories();

  

```

  

#### Tags

```php

$wp_api->tags();

  

```

  

#### Category posts

```php

$wp_api->categoryPosts($slug, $page);

  

```

  

#### Author posts

```php

$wp_api->authorPosts($slug, $page);

  

```

  

#### Tag posts

```php

$wp_api->tagPosts($slug, $page);

  

```

  

#### Search

```php

$wp_api->search($query, $page);

  

```

  

#### Archive

```php

$wp_api->archive($year, $month, $page);

  

```