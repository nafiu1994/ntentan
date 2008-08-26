<?php
/**
 * The form element class. An element can be anything from the form
 * itself to the objects that are put in the form. Provides an 
 * abstract render class that is used to output the HTML associated
 * with this form element. All visible elements of the form must be 
 * subclasses of the element class. 
 *
 */
abstract class Element
{
	/**
	 * The id of the form useful for CSS styling and DOM access.
	 *
	 * @var string
	 */
	protected $id;
	
	/**
	 * The label of the form element.
	 *
	 * @var string
	 */
	protected $label;
	
	/**
	 * The description of the form element.
	 *
	 * @var string
	 */
	protected $description;
	
	/**
	 * The method by which the form should be submitted.
	 *
	 * @var string
	 */
	protected $method;
		
	public function __construct($label="", $description="", $id="")
	{
		$this->setLabel($label);
		$this->setDescription($description);
		$this->setId($id);
	}
	
	/**
	 * Public accessor for setting the ID of the element. 
	 *
	 * @param $id The id of the element.
	 */
	public function setId($id)
	{
		$this->id = $id;
	}
	
	/**
	 * Public accessor for getting the Id of the element.
	 *
	 * @return The id of the form element.
	 */
	public function getId()
	{
		return $this->id;
	}
	
	public function setLabel($label)
	{
		$this->label = $label;
	}
	
	public function getLabel()
	{
		return $this->label;
	}
	
	public function getDescription()
	{
		return $this->description;
	}
	
	public function setDescription($description)
	{
		$this->description = $description;
	}
	
	/**
	 * Sets the method for the form.
	 */
	public function setMethod($method)
	{
		$this->method = strtoupper($method);
	}
	
	/**
     * Gets the method being used by the form.
     */
	public function getMethod()
	{
		return $this->method;
	}
	
	public function hasError()
	{
		return false;
	}
	
	public function getType()
	{
		return __CLASS__;
	}
	
	/**
	 * Renders the form element by outputing the HTML associated with
	 * the element.
	 *
	 */
	abstract public function render();
	
	abstract public function getData();
	
	abstract public function validate();
	
	public function getClasses()
	{
		return "";
	}
	
}
?>