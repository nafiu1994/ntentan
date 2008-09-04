<?php
/**
 * A special container for containing Tab elements. This makes it possible
 * to layout form elements in a tab fashion. The TabLayout container takes
 * the tab as the elements it contains.
 *
 */
class TabLayout extends Container
{
	protected $tabs = array();
	
	/**
	 * Constructor for the Tab Layout.
	 * 
	 */
	public function __construct()
	{
		
	}
	
	/**
	 * Adds a tab to the tab layout.
	 * @param $tab The tab to be added to the tab layout.
	 * 
	 */
	public function add($tab)
	{
		array_push($this->tabs,$tab->getLegend());
		array_push($this->elements,$tab);
		$tab->setMethod($this->getMethod());
		$tab->addAttribute("id","fapi-tab-".strval(count($this->tabs)-1));
		if(count($this->tabs)==1)
		{
			$tab->addCSSClass("fapi-tab-seleted");
		}
		else
		{
			$tab->addCSSClass("fapi-tab-unselected");
		}
	}
	
	public function validate()
	{
		$retval = true;
		foreach($this->elements as $element)
		{
			if($element->validate()==false) 
			{
				$retval=false;
				$element->addCSSClass("fapi-tab-error");
				$this->error = true;
				array_push($this->errors,"There were some errors on the ".$element->getLegend()." tab");
			}
		}
		return $retval;
	}
	
	/**
	 * Renders all the tabs.
	 */
	public function render()
	{
		print "<ul class='fapi-tab-list ".$this->getCSSClasses()."'>";
		for($i=0; $i<count($this->tabs); $i++)
		{
			print "<li id='fapi-tab-top-$i' class='".($i==0?"fapi-tab-selected":"fapi-tab-unselected")."'><a href='#' onclick='fapiSwitchTabTo($i)'>".$this->tabs[$i]."</a></li>";
		}
		print "</ul><p style='clear:both' ></p>";
		foreach($this->elements as $element)
		{
			$element->render();
		}
	}
}
?>