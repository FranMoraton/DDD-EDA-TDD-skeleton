<?php

namespace App;

use App\System\Infrastructure\Symfony\DependencyInjection\Compiler\CommandPass;
use App\System\Infrastructure\Symfony\DependencyInjection\Compiler\DomainEventPass;
use App\System\Infrastructure\Symfony\DependencyInjection\Compiler\QueryPass;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    protected function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new DomainEventPass());
        $container->addCompilerPass(new CommandPass());
        $container->addCompilerPass(new QueryPass());
    }
}
