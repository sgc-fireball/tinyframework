<?php declare(strict_types=1);

namespace TinyFramework\ServiceProvider;

use TinyFramework\Core\ContainerInterface;

interface ServiceProviderInterface
{

    public function __construct(ContainerInterface $container);

    public function register(): void;

    public function boot(): void;

}
