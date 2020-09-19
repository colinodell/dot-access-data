# Dot Access Data

[![Latest Version](https://img.shields.io/packagist/v/colinodell/dot-access-data.svg?style=flat-square)](https://packagist.org/packages/colinodell/dot-access-data)
[![Total Downloads](https://img.shields.io/packagist/dt/colinodell/dot-access-data.svg?style=flat-square)](https://packagist.org/packages/colinodell/dot-access-data)
[![Software License](https://img.shields.io/badge/License-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Build Status](https://img.shields.io/github/workflow/status/colinodell/dot-access-data/Tests/master.svg?style=flat-square)](https://github.com/colinodell/dot-access-data/actions?query=workflow%3ATests+branch%3Amaster)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/colinodell/dot-access-data.svg?style=flat-square)](https://scrutinizer-ci.com/g/colinodell/dot-access-data/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/colinodell/dot-access-data.svg?style=flat-square)](https://scrutinizer-ci.com/g/colinodell/dot-access-data)

[![Sponsor development of this project](https://img.shields.io/badge/sponsor%20this%20package-%E2%9D%A4-ff69b4.svg?style=flat-square)](https://www.colinodell.com/sponsor)

Given a deep data structure, access data by dot- or slash-separated paths.

This is a fork of <https://github.com/dflydev/dflydev-dot-access-data> with some additional features and improvements:

 - Ability to use either `.` or `/` as path delimiters
 - Support for using `Data` objects like arrays (thanks to `ArrayAccess`)
 - Optional ability for `get()` to throw exceptions on missing paths
 - More-specific exception types
 - Tested against the latest versions of PHP
 - More-robust type annotations and purity markers (verified with Psalm)

## Installation

Install this library on PHP 7.2+ via Composer:

```sh
$ composer require colinodell/dot-access-data
```

## Usage Example

```php
use ColinODell\DotAccessData\Data;

$data = new Data([
    'hosts' => [
        'hewey' => [
            'username' => 'hman',
            'password' => 'HPASS',
            'roles'    => ['web'],
        ],
        'dewey' => [
            'username' => 'dman',
            'password' => 'D---S',
            'roles'    => ['web', 'db'],
            'nick'     => 'dewey dman',
        ],
        'lewey' => [
            'username' => 'lman',
            'password' => 'LP@$$',
            'roles'    => ['db'],
        ],
    ],
]);

// hman
$username = $data->get('hosts.hewey.username');
// HPASS
$password = $data->get('hosts.hewey.password');
// ['web']
$roles = $data->get('hosts.hewey.roles');
// dewey dman
$nick = $data->get('hosts.dewey.nick');
// Unknown
$nick = $data->get('hosts.lewey.nick', 'Unknown');

// DataInterface instance
$dewey = $data->getData('hosts.dewey');
// dman
$username = $dewey->get('username');
// D---S
$password = $dewey->get('password');
// ['web', 'db']
$roles = $dewey->get('roles');

// No more lewey
$data->remove('hosts.lewey');

// Add DB to hewey's roles
$data->append('hosts.hewey.roles', 'db');

$data->set('hosts.april', [
    'username' => 'aman',
    'password' => '@---S',
    'roles'    => ['web'],
]);

// Check if a key exists (true to this case)
$hasKey = $data->has('hosts.dewey.username');
```

`Data` may also be used as an array, since it implements `ArrayAccess` interface:

```php
// Get
$data->get('name') === $data['name']; // true

$data['name'] = 'Dewey';
// is equivalent to
$data->set($name, 'Dewey');

isset($data['name']) === $data->has('name');

// Remove key
unset($data['name']);
```

## License

This library is licensed under the MIT License - see the LICENSE file for details.
