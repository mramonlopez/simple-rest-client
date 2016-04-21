<?php
require_once('dbconnection.php');

abstract class RESTmodel
{
	public $properties = array();
	protected $tablename;
	
	function update() {
		$sql = RESTmodel::modifySQL();	
		DBConnection::Modify($sql);
		if ($this->properties["ID"] != null) {
			$response = $this->select();
		} else {
			$response = null;
		}		
		return $response;	
	}
	
	function insert() {
		$sql = RESTmodel::modifySQL(true);
		$this->properties["ID"] = DBConnection::Modify($sql);	
		if ($this->properties["ID"] != null) {
			$response = $this->select();
		} else {
			$response = null;
		}
		return $response;
	}
	
	function select($where = "") {
		$sql = RESTmodel::selectSQL($where, $this->properties["ID"]);
		
		$response = DBConnection::Read($sql);
		
		if (count($response)>0) {
			$this->properties = $response;
		}
		
		return $response;
	}
	
	function selectAll($where = "") {
		$sql = RESTmodel::selectSQL($where);
		
		return DBConnection::Read($sql, true);
	}
	
		
	function delete() {
		$sql = "DELETE FROM " . 
			$this->tablename . 
			" WHERE ID='" . 
			$this->properties["ID"] . "'";
		return DBConnection::Delete($sql);
	}
	
	private function selectSQL($where, $ID = "") {
		$sql = "";
		
		$count = count($this->properties);
		if ($count>0) {
			$fields = array_keys($this->properties);

			$sql = "SELECT ";
			
			$i = 0;			
			foreach($this->properties as $field => $value) {
				if($i>0) {
					$sql = $sql.", ";
				}
					
				$sql = $sql . "`$field`";
				$i++;
			}
			
			$sql = $sql." FROM " . $this->tablename;
			
			if ($ID != "" || $where != "") {
				$sql = $sql . " WHERE ";
				if ($ID != "") {
					$sql = $sql . "ID = '$ID'";
					if ($where != "")
						$sql = $sql. " AND $where";
				}
				else {
					if ($where != "")
						$sql = $sql. "$where";
				}
			}
		}
		
		return $sql;
	}
	
	private function modifySQL($insert = false) {
		$sql = "";
		$ID = $this->properties["ID"];
		
		if (count($this->properties)>0) {
			$fields = array_keys($this->properties);

			$sql = ($insert?"INSERT ":"UPDATE ") . $this->tablename . " SET ";
			$i = 0;
			
			foreach($this->properties as $field => $value) {
				// MOD. 21/7/2013: Añado la posibilidad de proporcionar la clave primaria, para cuando no es autonumerica.
				if($field != "ID" || ($insert && $field == "ID" && $value != null)) { 
					if($i>0) {
						$sql = $sql.", ";
					}
						
					$sql = $sql . "`$field` = '$value'";
					$i++;
				}
			}
			
			if (!$insert)
				$sql = $sql . " WHERE ID = '$ID'";
		}
		
		return $sql;
	}

	
	
}

?>