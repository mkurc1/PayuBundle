# PayUBundle

The bundle integrate [PayU](http://www.payu.pl/) into [Symfony](http://symfony.com/) Framework.

## Configure

Require the bundle with composer:

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

Create your Order class and implement OrderInterface:

    <?php
    
    namespace AppBundle\Entity;
    
    use Doctrine\ORM\Mapping as ORM;
    use PayuBundle\Entity\OrderInterface;
    
    /**
     * @ORM\Table("subscription")
     * @ORM\Entity()
     */
    class Order implements OrderInterface
    {
        /**
         * @var integer
         *
         * @ORM\Column(type="integer")
         * @ORM\Id
         * @ORM\GeneratedValue(strategy="IDENTITY")
         */
        private $id;
    
    
        public function getName()
        {
            return 'Order';
        }
    
        public function getDescription()
        {
            return 'Payment order number ' . $this->getId();
        }
    
        public function getTotalPrice()
        {
            return 111; // your price
        }
    
        /**
         * @return int
         */
        public function getId()
        {
            return $this->id;
        }
    }
    
Create your PayuOrderRequest class:

    <?php

    namespace AppBundle\Entity;
    
    use Doctrine\ORM\Mapping as ORM;
    use PayuBundle\Entity\PayuOrderRequest as AbstractPayuOrderRequest;
    
    /**
     * @ORM\Table("payu_order_request")
     * @ORM\Entity()
     */
    class PayuOrderRequest extends AbstractPayuOrderRequest
    {
        /**
         * @var Order
         *
         * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Order")
         * @ORM\JoinColumn(nullable=false)
         */
        private $order;
    
        /**
         * @return Order
         */
        public function getOrder()
        {
            return $this->order;
        }
    
        /**
         * @param Order $order
         */
        public function setOrder($order)
        {
            $this->order = $order;
        }
    }
    
Configure your application:

    # app/config/config.yml
    monolog:
        handlers:
            payu:
                type: stream
                path: "%kernel.logs_dir%/payu.log"
                channels: [payu]
    
    payu:
        class:
            request: AppBundle\Entity\PayuOrderRequest # your PayuOrderRequest class
        redirect: app_profile_edit # redirect to route after payment
        environment: secure
        pos_id: 145227
        signature_key: 13a980d4f851f3d9a1cfc792fb1f5e50
    
## License

The bundle is released under the [MIT License](LICENSE).
