TmsModelIOBundle
==========

Symfony2 bundle used to import and export data based on a model

Installation
------------

To install this bundle please follow these steps:

First, add the dependencies in your `composer.json` file:

```json
"repositories": [
    ...,
    {
        "type": "vcs",
        "url": "https://github.com/Tessi-Tms/TmsModelIOBundle.git"
    }
],
"require": {
        ...,
        "tms/modelio-bundle": "dev-master"
    },
```

Then, install the bundle with the command:

```sh
composer update
```

Enable the bundle in your application kernel:

```php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        //
        new Tms\Bundle\ModelIOBundle\TmsModelIOBundle(),
    );
}
```

Load the configuration file

```yml
# app/config/config.yml
    - { resource: @TmsModelIOBundle/Resources/config/config.yml }
```

Finally, Load the bundle routing file

```yml
# app/config/routing.yml
tms_model_io:
    resource: "@TmsModelIOBundle/Controller"
    type:     annotation
```

Configuration
-------------

```yml
# app/config/config.yml
```

Export génère un fichier json (ou un json à envoyer ensuite)

$importExportHandler->export('simple');

Import renvoie un objet du type demandé (à persister)

$participation = $importExportHandler->import();
