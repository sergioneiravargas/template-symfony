<?php

declare(strict_types=1);

namespace App\Service\Notification\DependencyInjection;

use App\Service\Notification\Handler;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class CompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(Handler::class)) {
            return;
        }
        $definition = $container->findDefinition(Handler::class);
        foreach ($container->findTaggedServiceIds('app.notification.handler_strategy') as $id => $tags) {
            $definition->addMethodCall('addStrategy', [new Reference($id)]);
        }
    }
}
