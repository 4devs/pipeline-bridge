<?php

namespace FDevs\Bridge\Pipeline;

use FDevs\Bridge\Pipeline\DependencyInjection\Compailer\StagePass;
use FDevs\Bridge\Pipeline\DependencyInjection\FDevsPipelineExtension;
use FDevs\Container\Compiler\ServiceLocatorPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class FDevsPipelineBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container
            ->addCompilerPass(new StagePass())
            ->addCompilerPass(new ServiceLocatorPass('f_devs_pipeline.registry', StagePass::TAG_PIPELINE))
        ;
    }

    /**
     * @inheritDoc
     */
    protected function getContainerExtensionClass()
    {
        return FDevsPipelineExtension::class;
    }
}
