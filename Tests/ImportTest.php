<?php
namespace Tms\Bundle\ModelIOBundle\Tests\Import;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ImportTest extends WebTestCase
{
 
     public function testImportServiceExists()
     {
         $kernel = static::createKernel();
         $kernel->boot();
         
         $container = $kernel->getContainer();
         
         $service = $container->get('tms_model_io.io.import');
         
         $result = $service->exists();
         
         $this->assertEquals(true, $result);
     }
 
}