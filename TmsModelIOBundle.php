<?php

/**
 * @author:  TESSI Marketing <contact@tessi.fr>
 */

namespace Tms\Bundle\ModelIOBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Tms\Bundle\ModelIOBundle\DependencyInjection\TmsModelIOBundleExtension;
use Tms\Bundle\ModelIOBundle\DependencyInjection\Compiler\DefineHandlersCompilerPass;

class TmsModelIOBundle extends Bundle
{
    public function __construct()
    {
        $this->extension = new TmsModelIOBundleExtension();
    }

    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new DefineHandlersCompilerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION);
    }
}
