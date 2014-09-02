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

        $importService = $container->get('tms_model_io.importer');

        $object = $importService->createObject(
            'Tms\Bundle\OperationBundle\Entity\Product',
            '{
                "name":"test",
                "shortDescription":"courte description",
                "longDescription":"longue description",
                "ean13":"1",
                "refundable":"1"
            }'
        );
        var_dump(print_r($object));
        $this->assertInstanceOf(
            "Tms\Bundle\OperationBundle\Entity\Product",
            $object,
            "not a Tms\Bundle\OperationBundle\Entity\Product"
        );
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
