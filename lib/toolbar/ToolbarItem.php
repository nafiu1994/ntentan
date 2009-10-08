<?php
abstract class ToolbarItem
{
	protected $icon;

	public function render()
	{
		return "<div>".($this->icon==null?"":"<img class='toolbar-icon toolbar-linkbutton-icon' src='{$this->icon}' />").$this->_render()."</div>";
	}

	protected abstract function _render();

	public abstract function getCssClasses();
}
?>
