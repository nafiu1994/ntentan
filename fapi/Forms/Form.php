<?php
/*
 *  
 *  Copyright 2008, James Ainooson 
 *
 *  This file is part of Ntentan.
 *
 *   Ntentan is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   Ntentan is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with Foobar.  If not, see <http://www.gnu.org/licenses/>.
 *
 */


include_once ("Container.php");
include_once ("HiddenField.php");
include_once ("DefaultRenderer.php");

/**
 * The form class. This class represents the overall form class. This
 * form represents the main form for collecting data from the user.
 * 
 * @todo Change all labels from DIVs to LABEL
 * @todo Setup JavaScript hooks
 * @todo Remove the is_form_sent field from the value returned to the validated function
 * @todo Add styling hooks called from the various classes for CSS (Elaborate!)
 * @todo Change all includes to requires
 * @todo add Namespaces to form class for ntentan
 * 
 */
class Form extends Container
{	
	/**
	 * The value to be printed on the submit form.
	 *
	 * @var unknown_type
	 */
	protected $submitValue;
	
	/**
	 * Flag to show wether this form has a reset button.
	 *
	 * @var unknown_type
	 */
	protected $hasReset;
	
	/**
	 * The value to display on the reset button.
	 *
	 * @var unknown_type
	 */
	protected $resetValue;
	
	private $sendValidator;
	
	private $callback;
	
	protected $renderer;
	
	public function __construct($method="")
	{
		parent::__construct();
		if($method=="") $method="POST";
		$this->setMethod($method);
	}
	
	public function validate()
	{
		$form_data = $this->getData();
		
		if($this->getMethod()=="POST") $sent=$_POST['is_form_sent'];
		if($this->getMethod()=="GET") $sent=$_GET['is_form_sent'];
		
		if($sent=="yes")
		{
			//Remove the first data element which is used for checking if the
			//was properly selected. This would actually be stored as 
			//$form_data["is_form_sent"].
			  
			$form_data = array_shift($form_data);
			if(parent::validate())
			{
				$callback = $this->callback;
				$this->saveData();
				if($callback!="") $callback($form_data);
				return true;
			}
		}
		else
		{
			if($this->database_table != "" && 
			   $this->primary_key_field !="" && 
			   $this->primary_key_value !="")
			{
				$data = $this->retrieveData();
				$this->setData($data);
			}
		}
		
		return false;
	}
	
	/**
	 * Renders the form.
	 *
	 */
	public function render()
	{
		if($this->validate()) return;
		$this->addAttribute("method",$this->getMethod());
		$this->addAttribute("id",$this->getId());
		$this->addAttribute("class","fapi-form");
		print '<form '.$this->getAttributes().'>';
		$this->renderElements();
		print '<div id="fapi-submit-area">';
		if($this->getShowField())
		{
			print '<input type="submit" '.($this->submitValue?('value="'.$this->submitValue.'"'):"").' />';
		}
		print '</div>';
		print '<input type="hidden" name="is_form_sent" value="yes" />';
		print '</form>';
	}
	
	/**
     * Sets the value that is written on the submit form.
     */
	public function setSubmitValue($submitValue)
	{
		$this->submitValue = $submitValue;
	}
	
	public function setCallback($callback)
	{
		$this->callback = $callback;
	}
	
}
?>