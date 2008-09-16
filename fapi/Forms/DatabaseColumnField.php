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

include_once "SelectionList.php";

class DatabaseColumnField extends SelectionList
{
	public function __construct($label="",$name="",$description="",$query="")
	{
		parent::__construct($label,$name,$description);
		if($query!="")
		{
			$this->setQuery($query);
		}
	}
	
	public function setQuery($query)
	{
		$result = mysql_query($query);
		while($row=mysql_fetch_array($result))
		{
			$this->addOption($row[1],$row[0]);
		}
	}
}

?>