<?php

declare(strict_types=1);

namespace App\System\Infrastructure\Symfony\DependencyInjection\Compiler;

use App\System\Application\Command;
use App\System\Infrastructure\Symfony\Bus\Serializer\Mappers\CommandMapper;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class CommandPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (false === $container->has(CommandMapper::class)) {
            return;
        }

        $listingDefinition = $container->findDefinition(CommandMapper::class);
        $taggedServices = $container->findTaggedServiceIds('messenger.message_handler');

        foreach (\array_keys($taggedServices) as $id) {
            $class = (string)$container->getDefinition($id)->getClass();
            $parameters = (new \ReflectionMethod($class, '__invoke'))->getParameters();

            if (1 !== \count($parameters)) {
                continue;
            }

            $parameter = new \ReflectionClass($parameters[0]->getType()->getName());

            if (false === $parameter->isSubclassOf(Command::class)) {
                continue;
            }

            $listingDefinition->addMethodCall('add', [
                $parameter->getName()::messageName(),
                $parameter->name,
            ]);
        }
    }
}
