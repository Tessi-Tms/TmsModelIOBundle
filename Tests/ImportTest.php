<?php
namespace Tms\Bundle\ModelIOBundle\Tests\Import;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use JMS\Serializer as Serializer;
use JMS\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManager;

class ImportTest extends WebTestCase
{

    public function testImportService()
    {
        $kernel = static::createKernel();
        $kernel->boot();
        $container = $kernel->getContainer();

        $em =                $container->get('doctrine')->getManager();
        $serializerService = $container->get('jms_serializer');
        $this->assertInstanceOf("Doctrine\ORM\EntityManager", $em, "not a Manager");
        $this->assertInstanceOf("JMS\Serializer\SerializerInterface", $serializerService, "not a Serializer");

        $importService = $container->get('tms_model_io.io.importer');
        $this->assertEquals("exists", $importService->exists());
        $this->assertInstanceOf("JMS\Serializer\SerializerInterface", $importService->getSerializer(), "not a Serializer");

        $entity = $importService->createObject(
            'Tms\Bundle\OperationBundle\Entity\Product',
            '{
                "name":"test",
                "short_description": "courte description",
                "long_description": "longue description",
                "ean13":"whatis an ean13",
                "customer_id": 1,
                "refundable": 1
            }',
            'json'
        );
        $this->assertInstanceOf("Tms\Bundle\OperationBundle\Entity\Product", $entity, "not a Tms\Bundle\OperationBundle\Entity\Product");
        $name = "test" . sha1(date(""));
        $importService->import(
            $em,
            'Tms\Bundle\OperationBundle\Entity\Product',
            '{
                "short_description": "courte description",
                "long_description": "longue description",
                "ean13":"whatis an ean13",
                "customer_id": 1,
                "refundable": 1,
                "name": "'.$name.'"
            }',
            'json'
        );
        // $product  = $em->getRepository("Tms\Bundle\OperationBundle\Entity\Repository\ProductRepository")->findOneBy(array('name'=>$name));
        // var_dump($product);
    }

}