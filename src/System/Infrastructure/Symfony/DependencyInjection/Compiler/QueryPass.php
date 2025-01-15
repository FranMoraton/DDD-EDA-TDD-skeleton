<?php

declare(strict_types=1);

namespace App\System\Infrastructure\Symfony\DependencyInjection\Compiler;

use App\System\Application\Query;
use App\System\Infrastructure\Symfony\Bus\Serializer\Mappers\QueryMapper;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class QueryPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (false === $container->has(QueryMapper::class)) {
            return;
        }

        $listingDefinition = $container->findDefinition(QueryMapper::class);
        $taggedServices = $container->findTaggedServiceIds('messenger.message_handler');

        foreach (\array_keys($taggedServices) as $id) {
            $class = (string)$container->getDefinition($id)->getClass();
            $parameters = (new \ReflectionMethod($class, '__invoke'))->getParameters();

            if (1 !== \count($parameters)) {
                continue;
            }

            $parameter = new \ReflectionClass($parameters[0]->getType()->getName());

            if (false === $parameter->isSubclassOf(Query::class)) {
                continue;
            }

            $listingDefinition->addMethodCall('add', [
                $parameter->getName()::messageName(),
                $parameter->name,
            ]);
        }
    }
}
