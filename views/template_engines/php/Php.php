<?php
namespace ntentan\views\template_engines\php;

use ntentan\views\template_engines\TemplateEngine;

class Php extends TemplateEngine
{
    public function out($template, $data)
    {
        foreach($data as $key => $value)
        {
            $$key = $value;
        }
        ob_start();
        include $template;
        return ob_get_clean();
    }
}