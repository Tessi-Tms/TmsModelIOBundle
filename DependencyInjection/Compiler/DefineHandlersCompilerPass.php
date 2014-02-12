<?php

/**
 * @author Jean-Philippe Chateau <jp.chateau@trepia.fr>
 */

namespace Tms\Bundle\ModelIOBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\DefinitionDecorator;

class DefineHandlersCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $configuration = $container->getParameter('tms_model_io');
        $importExportHandlerServiceId = 'tms_model_io.handler.import_export_handler';
        $importExportServiceId = 'tms_model_io.manager.import_export_manager';

        if (!$container->hasDefinition($importExportHandlerServiceId) || !$container->hasDefinition($importExportServiceId)) {
            return;
        }

        foreach ($configuration['models'] as $modelName => $model) {
            $objectManagerReference = new Reference($model['object_manager']);

            // No Modes found. A default mode has to be created.
            if (!count($model['modes'])) {
                $modeName = 'default';
                $serviceDefinition = $this->createImportExportHandlerService(
                    $importExportHandlerServiceId,
                    $objectManagerReference,
                    $model['class'],
                    $modelName,
                    $modeName,
                    $model['aliases']
                );

                $handlerId = sprintf('%s.%s.%s', $importExportHandlerServiceId, $modelName, $modeName);
                $container->setDefinition($handlerId, $serviceDefinition);

                $importExportService = $container->getDefinition($importExportServiceId);
                $importExportService->addMethodCall(
                    'addHandler',
                    array(new Reference($handlerId))
                );

                continue;
            }

            foreach ($model['modes'] as $modeName => $fields) {
                $serviceDefinition = $this->createImportExportHandlerService(
                    $importExportHandlerServiceId,
                    $objectManagerReference,
                    $model['class'],
                    $modelName,
                    $modeName,
                    $model['aliases'],
                    $fields
                );

                $handlerId = sprintf('%s.%s.%s', $importExportHandlerServiceId, $modelName, $modeName);
                $container->setDefinition($handlerId, $serviceDefinition);

                $importExportService = $container->getDefinition($importExportServiceId);
                $importExportService->addMethodCall(
                    'addHandler',
                    array(new Reference($handlerId))
                );
            }
        }
    }

    /**
     * Creates an Import Export Handler Service
     *
     * @param string     $importExportHandlerServiceId
     * @param Reference  $objectManagerReference
     * @param string     $className
     * @param string     $modelName
     * @param string     $modeName
     * @param array      $fields
     */
    private function createImportExportHandlerService($importExportHandlerServiceId, Reference $objectManagerReference, $className, $modelName, $modeName, $aliases, $fields = array())
    {
        $serviceDefinition = new DefinitionDecorator($importExportHandlerServiceId);
        $serviceDefinition->isAbstract(false);
        $serviceDefinition->replaceArgument(0, $objectManagerReference);
        $serviceDefinition->replaceArgument(1, $className);
        $serviceDefinition->replaceArgument(2, $modelName);
        $serviceDefinition->replaceArgument(3, $modeName);
        $serviceDefinition->replaceArgument(4, $fields);
        $serviceDefinition->replaceArgument(5, $aliases);

        return $serviceDefinition;
    }
}
