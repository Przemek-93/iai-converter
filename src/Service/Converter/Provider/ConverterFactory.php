<?php

namespace App\Service\Converter\Provider;

use Symfony\Component\DependencyInjection\ContainerInterface;

class ConverterFactory
{
    protected ContainerInterface $container;

    public function __construct(
        ContainerInterface $container
    ) {
        $this->container = $container;
    }

    public function getConverterByName(string $converterName): ConverterProvider
    {
        $name = ucfirst(strtolower($converterName)) . 'ConverterProvider';
        if (!$this->container->has($name)) {
            throw new \Exception('Wrong converter provider name.');
        }

        return $this->container->get($name);
    }
}