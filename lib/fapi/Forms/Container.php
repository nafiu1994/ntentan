<?php

require_once ("Element.php");
require_once ("DatabaseInterface.php");
require_once ("ValidatableInterface.php");


/**
 * The container class. This abstract class provides the necessary
 * basis for implementing form element containers. The container
 * is a special element which contains other form elements. The container
 * implements the DatabaseInterface interface which makes it possible
 * for the container to dump its data directly into the database and
 * also automatically retrieve its data from the database.
 *
 * For this database interaction to be possible, the $database_table
 * property and the $database_schema property must be set. The following
 * code shows how this could be done using the Form class which is a
 * subclass of the container class.
 *
 * \ingroup Form_API
 */
abstract class Container extends Element implements DatabaseInterface, Validatable
{
	const STORE_DATABASE = "database";
	const STORE_MODEL = "model";
	const STORE_NONE = "none";

	protected $store = Container::STORE_NONE;

	/**
	 * The array which holds all the elements contained in this container.
	 */
	protected $elements = array();

	/**
	 * The name of the renderer currently in use.
	 */
	protected $renderer;

	/**
	 * The header function for the current renderer. This function contains the
	 * name of the renderer post-fixed with "_renderer_head"
	 */
	protected $renderer_head;

	/**
	 * The footer function for the renderer currently in use. This function
	 * contains the name of the renderer post-fixed with "_renderer_foot".
	 */
	protected $renderer_foot;

	/**
	 * The element function for the renderer currently in use.
	 */
	protected $renderer_element;

	/**
	 * The database table into which all the data represented in this
	 * container is to be dumped.
	 */
	protected $database_table;

	/**
	 * The database schema in which the table into which the data is to
	 * be dumped is found.
	 */
	private $database_schema;

	/**
	 * The primary key field of the database table.
	 */
	protected $primary_key_field;

	//! The primary key value of the database table.
	protected $primary_key_value;

	//! When set to false the fields are not shown for editing.
	protected $showfields = true;

	//! Stores the name of a custom function to call when the form is
	//! being rendered.
	protected $onRenderCallback;

	protected $model;

	public function __construct($renderer="table")
	{
		$this->setRenderer($renderer);
	}

	/**
	 * Sets the current renderer being used by the container. The renderer
	 * is responsible for rendering the HTML code used for the form.
	 */
	public function setRenderer($renderer)
	{
		$this->renderer = $renderer;
		include_once "Renderers/$this->renderer.php";
		$this->renderer_head = $renderer."_renderer_head";
		$this->renderer_foot = $renderer."_renderer_foot";
		$this->renderer_element = $renderer."_renderer_element";
	}

	/**
	 * Returns the renderer which is currently being used by the class.
	 */
	public function getRenderer()
	{
		return $this->renderer;
	}

	/**
	 * Method for adding an element to the form container.
	 *
	 * @param unknown_type $element
	 */
	public function add($element)
	{
		//Check if the element has a parent. If it doesnt then add it
		//to this container. If it does throw an exception.
		if($element->parent==null)
		{
			//throw new Exception("Why");
			array_push($this->elements, $element);
			$element->setMethod($this->getMethod());
			$element->setShowField($this->getShowField());
			$element->parent = $this;
			$element->setNameEncryption($this->getNameEncryption());
			$element->setNameEncryptionKey($this->getNameEncryptionKey());
			$element->ajax = $this->ajax;
			$this->hasFile |= $element->getHasFile();
		}
		else
		{
			throw new Exception("Element added already has a parent");
		}
		return $this;
	}


	/**
	 * Method for removing a particular form element from the
	 * container.
	 *
	 * @param $index The index of the element to be removed.
	 * @todo Implement the method to remove an element from the Container.
	 */
	public function remove($index)
	{

	}

	//! This method sets the data for the fields in this container. The parameter
	//! passed to this method is a structured array which has field names as keys
	//! and the values as value.
	public function setData($data)
	{
		foreach($this->elements as $element)
		{
			$element->setData($data);
		}
	}

	public function isFormSent()
	{
		if($this->getMethod()=="POST") $sent=$_POST['is_form_sent'];
		if($this->getMethod()=="GET") $sent=$_GET['is_form_sent'];
		if($sent=="yes") return true; else return false;
	}

	//! This method returns a structured array which represents the data stored
	//! in all the fields in the class. This method is recursive so one call
	//! to it extracts all the fields in all nested containers within this
	//! container.
	//! \param $storable This variable is set as true only when data for
	//!                  storable fields is required. A storable field
	//!                  is field which can be stored in the database.
	public function getData($storable=false)
	{
		$data = array();

		if($this->getMethod()=="POST") $sent=$_POST['is_form_sent'];
		if($this->getMethod()=="GET") $sent=$_GET['is_form_sent'];

		if($sent=="yes")
		{
			foreach($this->elements as $element)
			{
				if($storable)
				{
					if($element->getStorable()==true) $data+=$element->getData($storable);
				}
				else
				{
					$data+=$element->getData();
				}
			}
		}
		else
		{
			foreach($this->elements as $element)
			{
				if($element->getType()=="Container")
				{
					$data+=$element->getData();
				}
				else
				{
					$data+=array($element->getName(false) => $element->getValue());
				}
			}
		}

		return $data;
	}

	//! This method sets the method of transfer for this container. The method
	//! could either be "GET" or "POST".
	public function setMethod($method)
	{
		$this->method = strtoupper($method);
		foreach($this->elements as $element)
		{
			$element->setMethod($method);
		}
	}

	//! Validates all the elements in the container. This method is recursive
	//! so all other containers within this container also validate their
	//! internal elements.
	public function validate()
	{
		$retval = true;
		foreach($this->elements as $element)
		{
			if($element->validate()==false)
			{
				$retval=false;
			}
		}
		return $retval;
	}

	//! Retrieve the all the data for this form from the database. This
	//! method is called whenever the form has its $primary_key_field,
	//! $primary_key_value and $database_table fields set.
	public function retrieveData()
	{
		global $db;

		// The variable to be returned after the whole retrieval process.
		$data = array();

		// Check if all the necessary fields contain values. If they do,
		// select all the values and assign the results to the data variable.
		if($this->database_table!="" && $this->primary_key_field!="" && $this->primary_key_value!="")
		{
			$query = "SELECT * FROM ".($this->database_schema!=""?$this->database_schema.".":"")." {$this->database_table} WHERE {$this->primary_key_field}='{$this->primary_key_value}'";
			$result = $db->query($query);
			//print mysql_error();
			$data = $result->fetch_assoc();
		}
		else
		{
			// If the values are not set, check if they are set for
			// any other containers found within this container and
			// recursively retrieve the data from them.
			foreach($this->getElements() as $element)
			{
				if($element->getType()=="Container")
				{
					$data += $element->retrieveData();
				}
			}
		}
		return $data;
	}

	public function retrieveModelData()
	{
		/*var_dump($this->model);
		var_dump($this->primary_key_field);
		var_dump($this->primary_key_value);*/

		if($this->model!=null && $this->primary_key_field!=null && $this->primary_key_value!=null)
		{
			$data = $this->model->getWithField($this->primary_key_field,$this->primary_key_value);
			$this->setData($data[0]);
		}
	}

	//! Save all the data found in this class into the database. This
	//! method is only called when the $database_table field in this
	//! container is set.
	//!
	//! \param $force_save This value is set to true then the list of
	//!                    fields are passed through the field array.
	//!                    They are not extracted from the form data
	//!                    since the form is not submitted.
	//! \param $field An array of all the fields that are to be stored.
	protected function saveDatabaseData($force_save = false, $field=array(),$field_element=array())
	{
		global $db;

		// Check if the $database_table value is set.
		if($this->database_table!="")
		{
			//Force the saving. This is used when the data is manually
			//passed to the container. In certain cases the data comes
			//from other sources such as CVS files.
			if(!$force_save)
			{
				//Get Data from the sent form data.
				$data = $this->getData(true);
				//print_r($data); die();
				//Extract Fields and build query
				$field = array_keys($data);
			}
			else
			{
				if($this->primary_key_field != "")
				{
					$data = array();
					foreach($field_element as $element)
					{
						$data[$element->getName(false)] = $element->getValue();
					}
				}
			}

			//If the primary key field is not set then the operation
			//expected to be performed is an insersion.
			if($this->primary_key_field=='')
			{
				//Create the first part of the query which is the INSERT blah blah part.
				$query = "INSERT INTO ".($this->database_schema!=""?$this->database_schema.".":"").$this->database_table."(";

				//Loop through all the fieles and apend them to the query.
				//This builds the (field1,field2,..) part of the query.
				for($i=0; $i<count($field); $i++)
				{
					if($field[$i]!="")
					{
						if($i!=0) $query.=",";
						$query.=$field[$i];
					}
				}

				//Loop through all the values and append them to the query
				//string whiles escaping any possible threats.
				$query.=") VALUES(";
				for($i=0; $i<count($field); $i++)
				{
					if($field[$i]!="")
					{
						if($i!=0) $query.=",";
						if($force_save)
						{
							$query.="\"".mysql_real_escape_string($field_element[$i]->getValue())."\"";
						}
						else
						{
							$query.="\"".mysql_real_escape_string($data[$field[$i]])."\"";
						}
					}
				}
				$query.=")";
			}
			else
			{
				// If the primary_key_field and the primary_key_values are set
				// then an update is to be performed. Build an update query.
				$query = "UPDATE ".($this->database_schema!=""?$this->database_schema.".":"").$this->database_table.
				         " SET ";
				for($i=0; $i<count($field); $i++)
				{
					if($field[$i]!="" && $field[$i]!=$this->primary_key_field)
					{
						$query.=$field[$i]."=\"".mysql_real_escape_string($data[$field[$i]])."\" ";
						if($i!=count($field)-1) $query.=", ";
					}
				}

				$query .= " WHERE {$this->primary_key_field}='{$this->primary_key_value}'";
			}
			$db->query($query) or die($db->error." - ".$query);
		}
		else
		{
			// If none of the above conditions are met then recursively
			// perform all these operations on the containers found nested
			// within this container.
			foreach($this->getElements() as $element)
			{
				if($element->getType()=="Container")
				{
					$element->saveData();
				}
			}
		}
	}

	protected function saveModelData()
	{
		//$model = model::load($this->model);
		if($this->primary_key_value==null)
		{
			$errors = $this->model->setData($this->getData());
			if($errors===true)
			{
				$this->model->save();
			}
			return $errors;
		}
		else
		{
			$errors = $this->model->setData($this->getData());
			if($errors===true)
			{
				$this->model->update($this->primary_key_field,$this->primary_key_value);
			}
			return $errors;
		}
	}

	public function saveData($force_save = false, $field=array(),$field_element=array())
	{
		switch($this->store)
		{
			case Container::STORE_DATABASE:
				$this->saveDatabaseData($force_save,$field,$field_element);
				break;
			case Container::STORE_MODEL:
				return $this->saveModelData($force_save,$field,$field_element);
				break;
		}
	}

	public function getType()
	{
		return __CLASS__;
	}

	//! Render all the Elements found within this container. The Elements
	//! are rendered using the current renderer.
	protected function renderElements()
	{
		$renderer_head = $this->renderer_head;
		$renderer_foot = $this->renderer_foot;
		$renderer_element = $this->renderer_element;
		$ret = "";

		// Call the callback function for the container.
		if($this->onRenderCallback!="")
		{
			$callback = $this->onRenderCallback;
			$data = $this->getData();
			$callback($this,$data);
		}

		if($renderer_head!="") $ret .= $renderer_head();
		foreach($this->elements as $element)
		{
			$ret .= $renderer_element($element,$this->getShowField());
		}
		if($renderer_head!="") $ret .= $renderer_foot();
		return $ret;
	}

	//! Sets the $database_table field.
	public function setDatabaseTable($database_table)
	{
		$this->database_table = $database_table;
	}

	public function setModel($model)
	{
		$this->store = Container::STORE_MODEL;
		$this->model = $model;
	}

	//! Gets the $database_table field.
	public function getDatabaseTable()
	{
		if($this->database_table=="")
		{
			if($this->parent != null) return $this->parent->getDatabaseTable();
		}
		else
		{
			return $this->database_table;
		}
	}


	//! Sets the $database_schema field.
	public function setDatabaseSchema($database_schema)
	{
		$this->database_schema = $database_schema;
	}


	//! Gets the $database_schema field.
	public function getDatabaseSchema()
	{
		if($this->database_schema=="")
		{
			if($this->parent != null) return $this->parent->getDatabaseSchema();
		}
		else
		{
			return $this->database_schema;
		}
	}

	//! Sets the $primary_key_field and the $primary_key_value.
	public function setPrimaryKey($primary_key_field,$primary_key_value)
	{
		$this->primary_key_field = $primary_key_field;
		$this->primary_key_value = $primary_key_value;
	}

	//! Returns the $primary_key_field.
	public function getPrimaryKeyField()
	{
		if($this->primary_key_field=="")
		{
			if($this->parent != null) return $this->parent->getPrimaryKeyField();
		}
		else
		{
			return $this->primary_key_field;
		}
	}

	//! Returns the $primary_key_value.
	public function getPrimaryKeyValue()
	{
		if($this->primary_key_field=="")
		{
			if($this->parent != null) return $this->parent->getPrimaryKeyValue();
		}
		else
		{
			return $this->primary_key_value;
		}
	}

	//! Sets whether the fields should be exposed for editing. If this
	//! field is set as true then the values of the fields as retrieved
	//! from the database are showed.
	public function setShowField($showfield)
	{
		Element::setShowField($showfield);
		foreach($this->getElements() as $element)
		{
			$element->setShowField($showfield);
		}
	}

	//! Returns an array of all the Elements found in this container.
	public function getElements()
	{
		return $this->elements;
	}

	//! Sets whether the form names should be encrypted. If this method
	//! is called with a parameter true, all the names that are rendered
	//! in the HTML code are encrypted so that the database internals
	//! are not exposed in anyway to the end users.
	public function setNameEncryption($nameEncryption)
	{
		Element::setNameEncryption($nameEncryption);
		foreach($this->getElements() as $element)
		{
			$element->setNameEncryption($nameEncryption);
		}
	}

	public function setNameEncryptionKey($nameEncryptionKey)
	{
		Element::setNameEncryptionKey($nameEncryptionKey);
		foreach($this->getElements() as $element)
		{
			$element->setNameEncryptionKey($nameEncryptionKey);
		}
	}

	//! Returns all Elements found in this container which are subclasses
	//! of the Field class.
	public function getFields()
	{
		$elements = $this->getElements();
		$fields = array();
		foreach($elements as $element)
		{
			if($element->getType()=="Field" || $element->getType()=="Checkbox")
			{
				//print "Field\n";
				array_push($fields,$element);
			}
			else if($element->getType()=="Container")
			{
				//$container_fields =
				//array_merge($fields,$element->getFields());
				//$fields += $element->getFields();
				//print "Container Out\n";

				foreach($element->getFields() as $field)
				{
					//$fields += $field;
					array_push($fields,$field);
					//print $field->getType();
				}
				//print count($element->getFields())." - ";
				//print count($fields)."\n";
			}
		}
		//print "Count ".count($fields);
		return $fields;
	}

	//! Sets the value of the render callback function.
	public function setRenderCallback($onRenderCallback)
	{
		$this->onRenderCallback = $onRenderCallback;
	}

	//! Returns the value of the render callback function.
	public function getRenderCallback()
	{
		return $this->onRenderCallback;
	}

	//! Returns an element in the container with a particular name.
	//! \param $name The name of the element to be retrieved.
	public function getElementByName($name)
	{
		foreach($this->getElements() as $element)
		{
			if($element->getType()!="Container")
			{
				if($element->getName(false)==$name) return $element;
			}
			else
			{
				try
				{
					return $element->getElementByName($name);
				}
				catch(Exception $e){}
			}
		}
		throw new Exception("No element with name $name found in array");
	}

	public function getElementById($id)
	{
		foreach($this->getElements() as $element)
		{
			if($element->getType()!="Container")
			{
				if($element->getId()==$id) return $element;
			}
			else
			{
				try
				{
					return $element->getElementById($id);
				}
				catch(Exception $e){}
			}
		}
		throw new Exception("No element with id $id found in array");
	}
}
?>
