<?php
    $config = require_once('config/config.php');
    $connection = mysqli_connect($config['host'],
    	                         $config['db_username'],
    	                         $config['db_password'],
    	                         $config['db_name']);
    // Test Connection
	if (mysqli_connect_errno()) {
		echo "DB Connection Error: " . mysqli_connect_errno();
	}

	$tables_exist_check = "SELECT COUNT(DISTINCT(table_name)) AS num_tables "
	                      ."FROM information_schema.columns "
	                      ."WHERE table_schema = '". $config['db_name']."'";
	$result_set = mysqli_query($connection, $tables_exist_check);
	$result = mysqli_fetch_assoc($result_set);
	mysqli_free_result($result_set);
	mysqli_next_result($connection);

	if ((int)$result["num_tables"] === 0) {
		$create_tables = file_get_contents('sql/create_db.sql');
		if (mysqli_multi_query($connection, $create_tables)) {
			echo mysqli_error($connection);
		}
		while(mysqli_next_result($connection)){;}
	}
?>