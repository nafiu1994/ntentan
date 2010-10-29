<?php
namespace ntentan\views\helpers\forms\api;

/**
 * An item that can be added to a selection list.
 * @ingroup Form_API
 */
class SelectionListItem
{
	public $label;
	public $value;

	public function __construct($label="", $value="")
	{
		$this->label = $label;
		$this->value = $value;
	}

	public function __tostring()
	{
		return $this->label;
	}
}

//! The SelectionList represents a list of selectible items. These items
//! can be shown in a combo box or a regular list.
//! \ingroup Form_API
class SelectionList extends Field
{
	//! Array of options.
	protected $options = array();

	//! A boolean value set if multiple selections.
	protected $multiple;

	public function __construct($label="", $name="", $description="")
	{
		Field::__construct($name);
		Element::__construct($label, $description);
		$this->addOption("","");
	}

	//! Sets weather multiple selections could be made.
	public function setMultiple($multiple)
	{
		$this->name.="[]";
		$this->multiple = $multiple;
		return $this;
	}

	//! Add an option to the selection list.
	//! \param $label The label of the options
	//! \param $value The value associated with the label.
	public function addOption($label="", $value="")
	{
		if($value==="") $value=$label;
		$this->options[] = new SelectionListItem($label, $value);
		return $this;
	}

	public function render()
	{
		$this->addAttribute("id",$this->getId());
		$ret = "<select {$this->getAttributes()} class='fapi-list ".$this->getCSSClasses()."' name='".$this->getName()."' ".($this->multiple?"multiple='multiple'":"").">";
		foreach($this->options as $option)
		{
			$ret .= "<option value='$option->value' ".($this->getValue()===$option->value?"selected='selected'":"").">$option->label</option>";
		}
		$ret .= "</select>";
		return $ret;
	}

	public function getDisplayValue()
	{
		foreach($this->options as $option)
		{
			if($option->value == $this->getValue())
			{
				return $option->label;
			}
		}
        return $this->value;
	}

	public function hasOptions()
	{
		return true;
	}

	public function getOptions()
	{
		$options = array();
		foreach($this->options as $option)
		{
			$options += array($option->value=>$option->label);
		}
		return $options;
	}
}
