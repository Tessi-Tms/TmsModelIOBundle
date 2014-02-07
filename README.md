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
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        //
        new Tms\Bundle\ModelIOBundle\TmsModelIOBundle(),
    );
}
```

Configuration
-------------

Edit the configuration file.


```yml
# app/config/config.yml

tms_model_io:
    models:
       participation:                                                   # Define your own model name
           object_manager: doctrine                                     # Tell which object manager you want to use (ex: doctrine, doctrine_mongodb)
           class: Tms\Bundle\OperationBundle\Entity\Participation       # The class name of your object (ie: entity, document)
           modes:
                simple:
                    - onlineEnabled
                    - offlineEnabled
                    - previewBallotBeforeDownloadEnabled
                full:
                    - onlineEnabled
                    - offlineEnabled
                    - previewBallotBeforeDownloadEnabled
                    - eligibilities: {mode: simple}
                    - steps: {mode: simple}
                    - benefits: {mode: simple}
```

You can declare two modes of IO: simple and full.
Then, you have to declare which fields are to be imported/exported.
