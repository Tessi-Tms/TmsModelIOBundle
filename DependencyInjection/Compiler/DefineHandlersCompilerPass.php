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
        $importExportServiceId = 'tms_model_io.handler.import_export';

        if (!$container->hasDefinition($importExportHandlerServiceId) || !$container->hasDefinition($importExportServiceId)) {
            return;
        }

        foreach ($configuration['models'] as $modelName => $model) {
            foreach ($model['modes'] as $modeName => $fields) {
                $objectManagerReference = new Reference($model['object_manager']);

                $serviceDefinition = new DefinitionDecorator($importExportHandlerServiceId);
                $serviceDefinition->isAbstract(false);
                $serviceDefinition->replaceArgument(0, $objectManagerReference);
                $serviceDefinition->replaceArgument(1, $model['class']);
                $serviceDefinition->replaceArgument(2, $modeName);
                $serviceDefinition->replaceArgument(3, $fields);

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
}
