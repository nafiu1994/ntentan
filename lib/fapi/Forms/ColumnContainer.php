<?php
/**
 * A special container which lays out its form elements in columns. The elements
 * are evenly packed into the columns. The ColumnContainer class is an extension
 * of the TableLayout class which has one row but different columns based on the
 * number of columns the container is supposed to have.
 *
 * @author james
 *
 */
class ColumnContainer extends TableLayout
{
	/**
	 * A boolean flag which is set whenever the contents of the table have been
	 * rearranged. Rearranging is normally done right when the container is being
	 * rendered.
	 */
	protected $reArranged;

	public function __construct($num_columns=1)
	{
		parent::__construct(1,$num_columns);
	}
	
	public function add($element,$row=-1,$column=-1)
	{
		foreach(func_get_args() as $element)
		{
			parent::add($element);
		}
		return $this;
	}

	public function render()
	{
		if(!$this->reArranged)
		{
			$elements_per_col = ceil(count($this->elements)/$this->num_columns);
			for($j = 0, $k=0; $j < $this->num_columns; $j++)
			{
				for($i=0;$i<$elements_per_col;$i++,$k++)
				{

					if($k<count($this->elements))
					{
						$this->elements[$k]->parent = null;
						parent::add($this->elements[$k],0,$j);
					}
					else
					{
						break;
					}
				}
			}
			$this->reArranged = true;
		}

		return parent::render();
	}
}
?>
