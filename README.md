# DISCLAIMER ⚠️
This package is still under development, might not be developed 
further in the future, and (probably) has too many incompatibilites to count.
**Check out [Laravel Octane](https://github.com/laravel/octane) instead**

## How to use
You can start a server using the `laurel:serve` command.
```shell
php artisan laurel:serve
```
By default `laurel:serve` will serve the application on
your `config(app.url)`, but you can change this by passing 
a socket address to the command.
```php
php artisan laurel:serve 127.0.0.1:4917
```