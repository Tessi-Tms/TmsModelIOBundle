TmsModelIOBundle
==========

Symfony2 bundle used to import and export data based on a model.

Currently, all the objects (i.e.: entities, documents) are exported into a json file, and can be imported from a json file.

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

Import the configuration file of the bundle into your application.

```yml
# app/config/config.yml

- { resource: @TmsModelIOBundle/Resources/config/config.yml }
```

Configuration
-------------

Edit the configuration file of the bundle you want to import/export objects.

e.g. : TmsOperationBundle


```yml
# Resources/config/config.yml

tms_model_io:
    models:
       participation:                                                   # Define your own model name
           object_manager: doctrine                                     # Tell which object manager you want to use (ex: doctrine, doctrine_mongodb)
           class: Tms\Bundle\OperationBundle\Entity\Participation       # The class name of your object (ie: entity, document)
           aliases: ["participations"]                                  # You can declare an array of aliases in order to get the model from other names 
           modes:
                simple:                                                 # Name of the mode. Below, you will find an array of the fields associated to this mode.
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

You can declare different modes of IO, for example simple and full.

If you declare another mode, be sure to have a model which implements this mode to import/export objects.

Here is an example:

```yml
# Resources/config/config.yml

...
        benefit:
            object_manager: doctrine
            class: Tms\Bundle\OperationBundle\Entity\Benefit
            aliases: ["benefits"]
            modes:
                simple:
                    - handlerServiceId
                    - priority
                    - options
                    - category: {mode: benefit_category}
        category:
            object_manager: doctrine
            class: Tms\Bundle\OperationBundle\Entity\BenefitCategory
            modes:
                benefit_category:
                    - id
```
