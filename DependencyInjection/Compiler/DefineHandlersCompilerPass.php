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
        $importExportServiceId = 'tms_model_io.handler.import_export';

        if (!$container->hasDefinition($importExportServiceId)) {
            return;
        }

        foreach ($configuration['models'] as $modelName => $model) {
            foreach ($model['modes'] as $modeName => $mode) {
                $serviceDefinition = new DefinitionDecorator($importExportServiceId);
                $serviceDefinition->isAbstract(false);
                $serviceDefinition->replaceArgument(2, $model['class']);
                $container->setDefinition(
                    sprintf('%s.%s.%s', $importExportServiceId, $modelName, $modeName),
                    $serviceDefinition
                );
            }
        }

        /*

        */

    }
}
