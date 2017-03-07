<?php
/*******************************************************************************
* Copyright (c) 2007-2014 Eclipse Foundation, Intalio, Inc., IBM Corporation and others.
* All rights reserved. This program and the accompanying materials
* are made available under the terms of the Eclipse Public License v1.0
* which accompanies this distribution, and is available at
* http://www.eclipse.org/legal/epl-v10.html
*
* Contributors:
*    Denis Roy, Eclipse Foundation - Initial Implementation
*    Antoine Toulme, Intalio - Backend functions
*******************************************************************************/

require_once(dirname(__FILE__) . "/backend_functions.php");
class DBConnection {

	function connect()
	{
		static $dbh;
		global $addon;
		$db_params = $addon->callHook('db_params');
  
		$dbh = mysql_connect($db_params['db_read_host'],$db_params['db_read_user'],$db_params['db_read_pass']);
		if (!$dbh) {
			errorLog("Failed attempt to connect to server - aborting.");
			exitTo("/error.php?errNo=101301","error: 101301 - data server can not be found");
		}

    	$database = $db_params['db_read_name'];
		if (isset($database)) {
			if (!mysql_select_db($database)) {
				errorLog("Failed attempt to open database: $database - aborting \n\t" . mysql_error());
				exitTo("/error.php?errNo=101303","error: 101303 - unknown database name");
			}
		}
      # Roger
      # set connection to utf8
      mysql_set_charset('utf8',$dbh);
		return $dbh;
	}

	function disconnect() {
		mysql_close();
	}
}
?>
