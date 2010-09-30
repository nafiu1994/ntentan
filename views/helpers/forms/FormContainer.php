<?php

namespace ntentan\views\helpers\forms;

/**
 * The form class. This class represents the overall form class. This
 * form represents the main form for collecting data from the user.
 *
 * @todo Change all labels from DIVs to LABEL
 * @todo Change all includes to requires
 *
 * @ingroup Form_API
 *
 */
use ntentan\Ntentan;

class FormContainer extends Container
{
    public $submitValue;
	public $showSubmit = true;
	public $successUrl;
    protected $method = "POST";
    
    private static $numForms;
	
	//! Constructor for initialising the forms. This constructor accepts
	//! the method of the form.
	public function __construct($id="", $method="POST")
	{
		$this->setId($id);
        $this->method = $method;
        $this->addAttribute("action", Ntentan::getUrl(Ntentan::$route));
	}

    public function addFileUploadSupport($maxFileSize = "")
    {
        $this->addAttribute("enctype", "multipart/form-data");
        $this->add(new HiddenField("MAX_FILE_SIZE", "10485760"));
    }

	public function render()
	{
		$this->addAttribute("method",$this->method);
		$this->addAttribute("id",$this->getId());
		$this->addAttribute("class","fapi-form");

		$ret = '<form '.$this->getAttributes().'>';

		$ret .= $this->renderElements();

		if($this->showSubmit)
		{
			$ret .= '<div class="fapi-submit-area">';
			$submitValue = $this->submitValue?("value='{$this->submitValue}'"):"";
			if($this->ajaxSubmit)
			{
				$ret .= sprintf('<input class="fapi-submit" type="button" %s onclick="%s"  />',$submitValue,$onclickFunction);
			}
			else
			{
				$ret .= sprintf('<input class="fapi-submit" type="submit" %s />',$submitValue);
			}
			$ret .= '</div>';
		}
		$ret .= '</form>';
		return $ret;
	}

	public function setShowFields($show_field)
	{
		Container::setShowField($show_field);
		$this->setShowSubmit($show_field);
	}
	/*public function setSubmitValue($submitValue)
	{
	    die("w");
	    $this->submitValue = $submitValue;
	}*/

    public function __toString()
    {
        return $this->render();
    }

    public function setId($id)
    {
        parent::setId($id == "" ? "form" . FormContainer::$numForms++ : $id);
        return $this;
    }
}