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

use ntentan\exceptions\FileNotFoundException;
use ntentan\Ntentan;

/**
 * A file caching backend. This class stores objects to be cached as files in
 * the cache directory. It is advisable to use it as the default callback for
 * other caching methods.
 */
class File extends Cache
{
    private function getCacheFile($file = '')
    {
        return Ntentan::$config['application']['app_home'] . "/cache/$file";
    }
    
    private function hashKey($key)
    {
        return $key;
    }
    
    protected function addImplementation($key, $object, $expires)
    {
        if(file_exists(self::getCacheFile()) && is_writable(self::getCacheFile()))
        {
            $object = array(
                'expires' => $expires,
                'object' => $object
            );
            $key = $this->hashKey($key);
            file_put_contents(self::getCacheFile("$key"), serialize($object));
        }
        else
        {
            trigger_error("The file cache directory *".self::getCacheFile()."* was not found or is not writable!");
        }
    }
    
    protected function existsImplementation($key)
    {
        $key = $this->hashKey($key);
        if(file_exists(self::getCacheFile("$key")))
        {
            $cacheObject = unserialize(file_get_contents(self::getCacheFile("$key")));
            if($cacheObject['expires'] > time() || $cacheObject['expires'] == 0)
            {
                return true;
            }
            else if($cacheObject['expires'] == -1)
            {
                return false;
            }
        }
        return false;
    }
    
    protected function getImplementation($key)
    {
        $key = $this->hashKey($key);
        $cacheObject = unserialize(file_get_contents(self::getCacheFile("$key")));
        if($cacheObject['expires'] > time() || $cacheObject['expires'] == 0)
        {
            return $cacheObject['object'] ;
        }
        else if($cacheObject['expires'] == -1)
        {
            return false;
        }
        else
        {
            return false;
        }
    }
    
    protected function removeImplementation($key)
    {
        $key = $this->hashKey($key);
        unlink(self::getCacheFile("$key"));
    }
}
