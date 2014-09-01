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
                "shortDescription":"courte description",
                "longDescription":"longue description",
                "ean13":"1",
                "customer":"1",
                "refundable":"1"
            }',
            'json'
        );
        var_dump(print_r($entity));
        $this->assertInstanceOf("Tms\Bundle\OperationBundle\Entity\Product", $entity, "not a Tms\Bundle\OperationBundle\Entity\Product");
        // $name = "test" . sha1(date(""));
        // $importService->import(
        //     $em,
        //     'Tms\Bundle\OperationBundle\Entity\Product',
        //     '{
        //         "customer":"1",
        //         "refundable":"1",
        //         "name": "'.$name.'",
        //         "shortDescription":"courte description",
        //         "longDescription":"longue description",
        //         "ean13":"1"
        //     }',
        //     'json'
        // );
        // $product  = $em->getRepository("Tms\Bundle\OperationBundle\Entity\Repository\ProductRepository")->findOneBy(array('name'=>$name));
        // var_dump($product);
    }

}