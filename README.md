Burn After Reading
==================

Securely share one-time messages over the Internet. Messages are encrypted in the browser before being sent to the
server to be stored. A sharable link is then provided, which will then decrypt the message in the browser and delete it
from the server. Messages also have an expiration and are automatically purged from the server when they expire.

Requirements
------------

* Apache
* PHP 8.2 FPM with the following extensions:
  * PDO
  * MySQL
  * JSON
* Composer
* MariaDB

Installation
------------

Install dependencies:
```bash
composer install --no-dev --optimize-autoloader
composer install --no-dev -d tools
```

Create a config.php file at the top level of the source with at least the following, adjusting values as needed:
```php
<?php

return [
  'database' => [
    'database' => 'burnafterreading',
    'username' => 'burnafterreading',
    'password' => 'PutPasswordHere'
  ]
];
```

See public/index.php for additional configuration options.

Database Setup
--------------

This assumes that the database and database user is called 'burnafterreading', and another migration account is called
'burnafterreading_migration'. Adjust the below to match your environment.

```mysql
GRANT SELECT, INSERT, UPDATE, DELETE ON burnafterreading.* TO burnafterreading@localhost;
GRANT SELECT, INSERT, UPDATE, DELETE, ALTER, CREATE, DROP ON burnafterreading.* TO burnafterreading_migration@localhost;
```

Create a `phinx-config.php`:
```php
<?php
return [
  'database' => [
    'database' => 'burnafterreading',
    'username' => 'burnafterreading_migration',
    'password' => 'PutPasswordHere'
  ]
];
```

Run the migration:
```bash
php tools/vendor/bin/phinx migrate
```

Web Server Configuration
------------------------

```apacheconf
<VirtualHost *:80>
  ServerName example.com

  DocumentRoot /srv/bar/public
  <Directory /srv/bar/public>
    Require all granted
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^ index.php [QSA,L]
  </Directory>

  CustomLog /dev/null combined
  ErrorLog /dev/null
</VirtualHost>
```

Cron Job
--------

The ```cron.php``` file should be run every 5 minutes. This deletes unopened messages that have expired.

```
*/5 * * * * php /srv/bar/cron.php >/dev/null 2>&1
```

License
-------
Copyright (C) 2025  Snail Paste, LLC

This program is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public
License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
details.

Some files are distributed under different licenses:
* public/css/bootstrap.min.css and public/css/bootstrap.min.css.map (copied during `composer install`): [MIT License](https://github.com/twbs/bootstrap/blob/main/LICENSE), Copyright (c) 2011-2024 The Bootstrap Authors
* The logo.svg and logo.png files use [Dummy Text from GGBotNet](https://www.dafont.com/dummy-text.font) licensed under Creative Commons Zero v1.0 Universal
