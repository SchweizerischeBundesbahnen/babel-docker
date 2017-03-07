<?php
/*******************************************************************************
 * Copyright (c) 2007-2013 Eclipse Foundation, IBM Corporation and others.
 * All rights reserved. This program and the accompanying materials
 * are made available under the terms of the Eclipse Public License v1.0
 * which accompanies this distribution, and is available at
 * http://www.eclipse.org/legal/epl-v10.html
 *
 * Contributors:
 *    Eclipse Foundation - initial API and implementation
 *    Kit Lo (IBM) - 272661 - Pseudo translations change " to ', breaking link texts
 *    Kit Lo (IBM) - [402215] Extract Orion JavaScript files for translation
 *    Kit Lo (IBM) - [413459] Received "Cannot deactivate string" messages during process_project_source_locations.php
 *******************************************************************************/

class String {
  public $errStrs;
  
  public $string_id		= 0;
  public $file_id		= 0;
  public $name			= '';
  public $value 		= '';
  public $userid		= 0;
  public $created_on	= '';
  public $is_active 	= 0;
  
	function save() {
		$rValue = false;
		if($this->file_id != 0 && $this->name != "" && $this->userid > 0) {
			global $dbh;

			$String = $this->getStringFromName($this->file_id, $this->name);
			if($String->value != $this->value || $String->is_active != $this->is_active) {
				$sql 		= "INSERT INTO";
				$created_on = "NOW()";
				$where 		= "";
				if($String->string_id > 0) {
					$this->string_id = $String->string_id;
					$sql = "UPDATE";
					$where = " WHERE string_id = " . sqlSanitize($this->string_id, $dbh);
					$Event = new EventLog("strings", "string_id:old_value", $this->string_id . ":" . $String->value, "UPDATE");
					$Event->add();
				}

                # Bug 272661 - Pseudo translations change " to ', breaking link texts
                # Use new returnSmartQuotedString function for value string which does not replace " with '.
                # Roger: remove this smartQuotedString function for the value
                #value    = " . returnSmartQuotedString(mysql_real_escape_string($this->value, $dbh)) . ",
				$sql .= " strings 
							SET string_id 	= " . sqlSanitize($this->string_id, $dbh) . ",
								file_id		= " . sqlSanitize($this->file_id, $dbh) . ", 
								name		= " . returnQuotedString(sqlSanitize($this->name, $dbh)) . ",
								value		= '" . mysql_real_escape_string($this->value, $dbh) . "',
								userid		= " . returnQuotedString(sqlSanitize($this->userid, $dbh)) . ",
								created_on	= " . $created_on . ",
								is_active	= " . sqlSanitize($this->is_active, $dbh) . $where;
				#echo "String-Save-SQL: $sql\n";
            if(mysql_query($sql, $dbh)) {
					if($this->string_id == 0) {
						$this->string_id = mysql_insert_id($dbh);
					}
					$rValue = true;
				}
				else {
               $GLOBALS['g_ERRSTRS'][1] = mysql_error();
				}
			}
			else {
				# Imported string is identical.
				$this->string_id = $String->string_id;
				$rValue = true;
			}
		}
		return $rValue;
	}
	
	function saveJs() {
		$rValue = false;
		if($this->file_id != 0 && $this->name != "" && $this->userid > 0) {
			global $dbh;

			if ($this->string_id > 0) {
				$String = $this->getStringFromStringId($this->file_id, $this->string_id);
			} else {
				$String = $this->getStringFromNameJs($this->file_id, returnEscapedQuotedString(sqlSanitize($this->name, $dbh)));
			}
			if($String->value != $this->value || $String->is_active != $this->is_active) {
				$sql 		= "INSERT INTO";
				$created_on = "NOW()";
				$where 		= "";
				if($String->string_id > 0) {
					$this->string_id = $String->string_id;
					$sql = "UPDATE";
					$where = " WHERE string_id = " . sqlSanitize($this->string_id, $dbh);
					$Event = new EventLog("strings", "string_id:old_value", $this->string_id . ":" . $String->value, "UPDATE");
					$Event->add();
				}

				$sql .= " strings 
							SET string_id 	= " . sqlSanitize($this->string_id, $dbh) . ",
								file_id		= " . sqlSanitize($this->file_id, $dbh) . ", 
								name		= " . returnEscapedQuotedString(sqlSanitize($this->name, $dbh)) . ",
                        value		= " . returnEscapedQuotedString(sqlSanitize($this->value, $dbh)) . ",
								userid		= " . returnQuotedString(sqlSanitize($this->userid, $dbh)) . ",
								created_on	= " . $created_on . ",
								is_active	= " . sqlSanitize($this->is_active, $dbh) . $where;
				if(mysql_query($sql, $dbh)) {
					if($this->string_id == 0) {
						$this->string_id = mysql_insert_id($dbh);
					}
					$rValue = true;
				}
				else {
					$GLOBALS['g_ERRSTRS'][1] = mysql_error();
				}
			}
			else {
				# Imported string is identical.
				$this->string_id = $String->string_id;
				$rValue = true;
			}
		}
		return $rValue;
	}
	
	/**
	 * Get string object from name of a value
	 *
	 * @param Integer $_file_id
	 * @param String $_name
	 * @return String String object
	 */
	function getStringFromName($_file_id, $_name) {
		$rValue = new String();
		if($_file_id > 0 && $_name != "") {
			global $dbh;

		# Bug 236454 - string token needs to be case sensitive
		$sql = "SELECT *
				FROM 
					strings
				WHERE file_id = " . sqlSanitize($_file_id, $dbh) . "
					AND name = BINARY " . returnQuotedString(sqlSanitize($_name, $dbh));	

			$result = mysql_query($sql, $dbh);
			if($result && mysql_num_rows($result) > 0) {
				$myrow = mysql_fetch_assoc($result);
				$String = new String();
				$String->string_id 	= $myrow['string_id'];
				$String->file_id 	= $myrow['file_id'];
				$String->name 		= $myrow['name'];
				$String->value 		= $myrow['value'];
				$String->userid 	= $myrow['userid'];
				$String->created_on = $myrow['created_on'];
				$String->is_active 	= $myrow['is_active'];
				$rValue = $String;
			}
		}
		return $rValue;
	}
	
	function getStringFromNameJs($_file_id, $_name) {
		$rValue = new String();
		if($_file_id > 0 && $_name != "") {
			global $dbh;

		# Bug 236454 - string token needs to be case sensitive
		$sql = "SELECT *
				FROM 
					strings
				WHERE file_id = " . sqlSanitize($_file_id, $dbh) . "
					AND name = BINARY " . $_name;

			$result = mysql_query($sql, $dbh);
			if($result && mysql_num_rows($result) > 0) {
				$myrow = mysql_fetch_assoc($result);
				$String = new String();
				$String->string_id 	= $myrow['string_id'];
				$String->file_id 	= $myrow['file_id'];
				$String->name 		= $myrow['name'];
				$String->value 		= $myrow['value'];
				$String->userid 	= $myrow['userid'];
				$String->created_on = $myrow['created_on'];
				$String->is_active 	= $myrow['is_active'];
				$rValue = $String;
			}
		}
		return $rValue;
	}
	
	function getStringFromStringId($_file_id, $_string_id) {
		$rValue = new String();
		if($_file_id > 0 && $_string_id != "") {
			global $dbh;

		$sql = "SELECT *
				FROM 
					strings
				WHERE file_id = " . sqlSanitize($_file_id, $dbh) . "
					AND string_id = " . $_string_id;	

			$result = mysql_query($sql, $dbh);
			if($result && mysql_num_rows($result) > 0) {
				$myrow = mysql_fetch_assoc($result);
				$String = new String();
				$String->string_id 	= $myrow['string_id'];
				$String->file_id 	= $myrow['file_id'];
				$String->name 		= $myrow['name'];
				$String->value 		= $myrow['value'];
				$String->userid 	= $myrow['userid'];
				$String->created_on = $myrow['created_on'];
				$String->is_active 	= $myrow['is_active'];
				$rValue = $String;
			}
		}
		return $rValue;
	}

	/**
	* Returns Array of active strings
	* @author droy
	* @param Integer file_id
	* @return Array Array of String objects
	*/
	function getActiveStrings($_file_id) {
		$rValue = Array();
		if($_file_id > 0) {
			global $dbh;

			$sql = "SELECT *
				FROM 
					strings
				WHERE file_id = " . sqlSanitize($_file_id, $dbh) . "
					AND is_active = 1";	

			$result = mysql_query($sql, $dbh);
			while($myrow = mysql_fetch_assoc($result)) {
				$String = new String();
				$String->string_id 	= $myrow['string_id'];
				$String->file_id 	= $myrow['file_id'];
				$String->name 		= $myrow['name'];
				$String->value 		= $myrow['value'];
				$String->userid 	= $myrow['userid'];
				$String->created_on = $myrow['created_on'];
				$String->is_active 	= $myrow['is_active'];
				$rValue[count($rValue)] = $String;
			}
		}
		return $rValue;
	}
	
	/**
	* Sets a string as inactive
	* @author droy
	* @param Integer string_id
	* @return bool success status
	*/
	function deactivate($_string_id) {
		$rValue = 0;
		if($_string_id > 0) {
			global $dbh;

			$sql = "UPDATE strings 
					SET is_active = 0 WHERE string_id = " . sqlSanitize($_string_id, $dbh);	

			$rValue = mysql_query($sql, $dbh);
			
			$Event = new EventLog("strings", "string_id", $_string_id, "DEACTIVATE");
			$Event->add();
		}
		return $rValue;
	}
}
?>
