# Memcached emulator  
[![Build Status](https://travis-ci.org/avantarm/memcached-emulator.png?branch=master)](https://travis-ci.org/avantarm/memcached-emulator)
[![Latest Stable Version](https://poser.pugx.org/avantarm/memcached-emulator/v/stable)](https://packagist.org/packages/avantarm/memcached-emulator)
[![Total Downloads](https://poser.pugx.org/avantarm/memcached-emulator/downloads)](https://packagist.org/packages/avantarm/memcached-emulator)
[![Latest Unstable Version](https://poser.pugx.org/avantarm/memcached-emulator/v/unstable)](https://packagist.org/packages/avantarm/memcached-emulator)
[![License](https://poser.pugx.org/avantarm/memcached-emulator/license)](https://packagist.org/packages/avantarm/memcached-emulator)

Memcached PHP extension emulator for Windows environment. 

Emulates Memcached 3.0.4 extension for PHP.

## Installation via Composer

Add `"avantarm/memcached-emulator": "~1.0"` to the require block in your composer.json and then run `composer install`.

```json
{
	"require": {
		"avantarm/memcached-emulator": "~1.0"
	}
}
```

Alternatively, you can simply run the following from the command line:

```sh
composer require avantarm/memcached-emulator
```

Unsupported methods:

```php
Memcached::setSaslAuthData()
Memcached::fetch()
Memcached::fetchAll()
Memcached::getDelayed()
Memcached::getDelayedByKey()
```

`$time` parameter is not supported for `Memcached::delete()` and `Memcached::deleteByKey()`.