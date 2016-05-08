<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ntentan\controllers;

use ntentan\panie\InjectionContainer;

/**
 * Description of ModelBinders
 *
 * @author ekow
 */
class ModelBinders
{
    private static $binders = [];
    private static $customBinderInstances = [];
    
    private static function getCustomBinder($binder)
    {
        if(!isset(self::$customBinderInstances[$binder])) {
            self::$customBinderInstances[$binder] = new $binder();
        }
        return self::$customBinderInstances[$binder];
    }
    
    public static function register($type, $binder) 
    {
        self::$binders[$type] = $binder;
    }
    
    public static function get($type)
    {
        if(isset(self::$binders[$type])) {
            return self::getCustomBinder(self::$binders[$type]);
        } else {
            return InjectionContainer::singleton(ModelBinderInterface::class);
        }
    }
}
