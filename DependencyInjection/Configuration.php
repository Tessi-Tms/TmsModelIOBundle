<?php

/**
 * @author Jean-Philippe Chateau <jp.chateau@trepia.fr>
 * @license MIT
 */

namespace Tms\Bundle\ModelIOBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        $rootNode = $treeBuilder->root('tms_model_io');

        $rootNode
            ->children()
                ->arrayNode('models')
                    ->useAttributeAsKey('')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('type')->isRequired()->end()
                            ->scalarNode('class')->isRequired()->end()
                            ->arrayNode('modes')
                                ->children()
                                    ->arrayNode('simple')
                                        ->prototype('variable')->end()
                                    ->end()
                                    ->arrayNode('full')
                                        ->prototype('variable')->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
