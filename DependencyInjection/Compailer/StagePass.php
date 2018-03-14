<?php

namespace FDevs\Bridge\Pipeline\DependencyInjection\Compailer;

use FDevs\Container\Compiler\ResolveParamTrait;
use FDevs\Container\Exception\RuntimeException;
use League\Pipeline\Pipeline;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class StagePass implements CompilerPassInterface
{
    use ResolveParamTrait;
    public const TAG_PIPELINE = 'f_devs_pipeline.pipeline';
    public const TAG_STAGE = 'f_devs_pipeline.stage';

    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        $pipelines = [];
        foreach ($container->findTaggedServiceIds(self::TAG_STAGE) as $taggedServiceId => $attrs) {
            foreach ($attrs as $attr) {
                if (!isset($attr['pipeline'])) {
                    throw new RuntimeException('attribute "pipeline" is required');
                }
                $pipelineId = $this->resolveParam($container, $attr['pipeline']);
                $priority = $attr['priority'] ?? 0;
                $pipelines[$pipelineId][$priority][] = new Reference($taggedServiceId);
            }
        }
        foreach ($pipelines as $pipelineId => $services) {
            krsort($services);
            $services = call_user_func_array('array_merge', $services);
            $def = new Definition(Pipeline::class, ['$stages' => $services]);
            $stageId = $pipelineId.'_stage_'.ContainerBuilder::hash($def);
            $container->setDefinition($stageId, $def);

            if ($container->hasDefinition($pipelineId)) {
                $def = $container->getDefinition($pipelineId);
                $def->addMethodCall('pipe', ['$stage' => new Reference($stageId)]);
            } else {
                $def->addTag(self::TAG_PIPELINE, ['id' => $pipelineId]);
            }
        }
    }

}