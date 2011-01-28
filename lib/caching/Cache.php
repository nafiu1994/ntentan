<?php
/* 
 * Ntentan PHP Framework
 * Copyright 2010 James Ekow Abaka Ainooson
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace ntentan\caching;

use ntentan\Ntentan;

/**
 * Abstract cache class.
 */
abstract class Cache
{
    const DEFAULT_TTL = 3600;
    private static $instance = null;
    
    private static function instance()
    {
        if(Cache::$instance == null)
        {
            $class = Ntentan::camelize(Ntentan::$cacheMethod);
            require "$class.php";
            $class = "ntentan\\caching\\$class";
            Cache::$instance = new $class();
        }
        return Cache::$instance; 
    }
    
    public static function add($key, $object, $ttl = 0)
    {
        $ttl = $ttl > 2592000 || $ttl == 0 ? $ttl : $ttl + time();
        Cache::instance()->addImplementation($key, $object, $ttl);
    }
    
    public static function get($key)
    {
        return Cache::instance()->getImplementation($key);
    }
    
    public static function exists($key)
    {
        return Cache::instance()->existsImplementation($key);
    }
    
    abstract protected function addImplementation($key, $object, $expires);
    abstract protected function getImplementation($key);
    abstract protected function existsImplementation($key);
}
