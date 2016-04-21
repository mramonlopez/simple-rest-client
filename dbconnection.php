<?php
require_once('config.php');

class DBConnection {
	private static $mysqli;
	
	static function GetConnection() {
		if (!isset(DBConnection::$mysqli) || DBConnection::$mysqli == null)
			DBConnection::$mysqli = new mysqli(Config::$db_server, Config::$db_user, Config::$db_password, Config::$db_name);
			
                return DBConnection::$mysqli;
	}
	
	static function Read($sql, $set=false) {
		$mysqli = DBConnection::GetConnection();
		$result = array();
		
		if($mysqli) {		
			$data = $mysqli->query($sql); 
			
			if(mysqli_num_rows($data)>0) {
				if($set) {
					$i = 0;
					while($row = $data->fetch_assoc()) {
						$result[$i] = $row;
						$i++;
					}	
				} else 
					$result = $data->fetch_assoc();
			}
		}
		
		return $result;
	}
	
	static function Modify($sql) {
		$mysqli = DBConnection::GetConnection();
				
		if($mysqli && $mysqli->query($sql)) {
			$result = $mysqli->insert_id;
		} else {	
			$result = null;
		}
			
		return $result;
	}
	
	static function Delete($sql) {
		$mysqli = DBConnection::GetConnection();
				
		if($mysqli) {
			$result = $mysqli->query($sql);
		} else {	
			$result = null;
		}
		
		return $result;
	}
}
?>
