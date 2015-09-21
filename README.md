# PayUBundle

The bundle integrate [PayU](http://www.payu.pl/) into [Symfony](http://symfony.com/) Framework.

## Configure

Require the bundle with composer:

.. code-block:: bash

    $ composer require mkurc1/payu-bundle

Enable the bundle in the kernel:

    <?php
    // app/AppKernel.php

    public function registerBundles()
    {
        $bundles = array(
            // ...
            new PayuBundle\PayuBundle(),
            // ...
        );
    }
    
## License

The bundle is released under the [MIT License](LICENSE).
