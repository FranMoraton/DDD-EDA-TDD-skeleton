<?php

declare(strict_types=1);

namespace App\System\Infrastructure\Symfony\DependencyInjection\Compiler;

use App\System\Domain\Event\DomainEvent;
use App\System\Infrastructure\Symfony\Bus\Serializer\Mappers\EventMapper;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

final class DomainEventPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (false === $container->has(EventMapper::class)) {
            return;
        }

        $eventMapperDefinition = $container->findDefinition(EventMapper::class);
        $projectDir = $container->getParameter('kernel.project_dir');

        $finder = new Finder();
        $finder->files()->in($projectDir . '/src')->name('*.php');

        foreach ($finder as $file) {
            $className = $this->getClassNameFromFile($file, $projectDir);
            if ($className && is_subclass_of($className, DomainEvent::class)) {
                $reflectionClass = new \ReflectionClass($className);
                if (false === $reflectionClass->isAbstract()) {
                    $eventMapperDefinition->addMethodCall('add', [
                        $className::messageName(),
                        $className,
                    ]);
                }
            }
        }
    }

    private function getClassNameFromFile(SplFileInfo $file, string $projectDir): ?string
    {
        $relativePath = str_replace($projectDir . '/src/', '', $file->getRealPath());
        $className = str_replace('/', '\\', rtrim($relativePath, '.php'));
        return 'App\\' . $className;
    }
}
