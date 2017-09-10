<?php

namespace ntentan;

use ntentan\atiaa\DriverFactory;
use ntentan\interfaces\ControllerFactoryInterface;
use ntentan\middleware\MvcMiddleware;
use ntentan\nibii\factories\DriverAdapterFactory;
use ntentan\nibii\interfaces\DriverAdapterFactoryInterface;
use ntentan\nibii\interfaces\ModelFactoryInterface;
use ntentan\panie\Container;
use ntentan\interfaces\ContainerBuilderInterface;
use ntentan\Application;
use ntentan\config\Config;
use ntentan\utils\Text;
use ntentan\nibii\interfaces\ValidatorFactoryInterface;
use ntentan\nibii\factories\DefaultValidatorFactory;
use ntentan\kaikai\CacheBackendInterface;
use ntentan\middleware\mvc\DefaultControllerFactory;

/**
 * Wires up the panie IoC container for ntentan.
 * This class contains the default wiring of the IoC container for ntentan. This wiring is primarily used during the
 * initial setup of the application. Any bindings created here are not passed on to the container used for initializing
 * controllers. To add your own custom bindings, you can extend this class and pass your new builder's class name to the
 * application initialization class of your app.
 * @package ntentan
 */
class ContainerBuilder implements ContainerBuilderInterface
{
    public function getContainer() 
    {
        $container = new Container();
        $container->setup([
            ModelClassResolverInterface::class => ClassNameResolver::class,
            ModelJoinerInterface::class => ClassNameResolver::class,
            TableNameResolverInterface::class => nibii\Resolver::class,
            DriverFactory::class => [
                function($container) {
                    $config = $container->resolve(Config::class);
                    return new DriverFactory($config->get('db'));
                }
            ],
            ModelFactoryInterface::class => [
                function() {
                    return new MvcModelFactory(Context::getInstance()->getNamespace());
                }
            ],
            ValidatorFactoryInterface::class => DefaultValidatorFactory::class,
            DriverAdapterFactoryInterface::class => [
                function($container) {
                    $config = $container->resolve(Config::class);
                    return new DriverAdapterFactory($config->get('db'));
                }
            ],
            // Wire up the application class
            Application::class => [
                Application::class,
                'calls' => [
                    'prependMiddleware' => ['middleware' => MvcMiddleware::class],
                    'setModelBinderRegister', 'setDriverFactory', 'setOrmFactories'
                ]
            ],

            // Wire up the resource loader to setup initial loader types
            /*ResourceLoaderFactory::class => [
                ResourceLoaderFactory::class,
                'calls' => ['registerLoader' => ['key' => 'controller', 'class' => ControllerLoader::class]]
            ],*/
                
            // 
            ControllerFactoryInterface::class => DefaultControllerFactory::class,
            
            // Factory for configuration class
            Config::class => [
                function(){
                    $config = new Config();
                    $config->readPath('config');
                    return $config;
                }, 
                'singleton' => true
            ],
                    
            // Factory for cache backends
            CacheBackendInterface::class => [
                function($container){
                    $backend = $container->resolve(Config::class)->get('cache.backend', 'volatile');
                    $classname = '\ntentan\kaikai\backends\\' . Text::ucamelize($backend) . 'Cache';
                    return $container->resolve($classname);
                }, 
                'singleton' => true
            ]
        ]);
        return $container;        
    }
}