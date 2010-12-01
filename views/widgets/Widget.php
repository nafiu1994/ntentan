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

namespace ntentan\views\blocks;

use ntentan\views\template_engines\Template;

use \ntentan\Ntentan;
use \ntentan\views\Presentation;
/**
 * 
 * Enter description here ...
 * @author ekow
 * @todo Look at the possibility of renaming blocks to widgets
 */
class Block extends Presentation
{
    protected $data = array();
    protected $template;
    protected $name;
    private $filePath;
    
    public function setFilePath($filePath)
    {
        $this->filePath = $filePath;
    }

    public function getName() {
    	return $this->name;
    }
    
    public function setName($name) {
    	$this->name = $name;
    }

    protected function set($params1, $params2 = null) {
    	
        if(is_array($params1)) {
            $this->data = array_merge($this->data, $params1);
        } else {
            $this->data[$params1] = $params2;
        }
    }

    protected function getData()
    {
        return $this->data;
    }

    public function preRender()
    {

    }

    public function postRender()
    {
        
    }

    public function __toString()
    {
        $this->preRender();
        if($this->template == "")
        {
            $block = $this->getName();
            $this->template = $this->filePath . "/$block.tpl.php";
        }
        $output = Template::out($this->template, $this->data);
        $this->postRender();
        return $output;
    }
}