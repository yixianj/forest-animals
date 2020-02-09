<?php
    require_once('db.php');

    $insert_user_query = "INSERT INTO users"
	                     ."(username, password) VALUES(?, ?)";
	$check_user_duplicates = "SELECT EXISTS (SELECT username FROM users "
	                         ."WHERE username = ?)";
	$username_error_message = "";
	$password_error_message = "";
	$username_value = "";
	$password_1_value = "";
	$password_2_value = "";

    if (isset($_POST["submit"])) {
    	// Set variable values
        if (isset($_POST["username"])) {
        	$username_value = $_POST["username"];
        }
        if (isset($_POST["password_1"])) {
        	$password_1_value = $_POST["password_1"];
        }
        if (isset($_POST["password_2_value"])) {
        	$password_2_value = $_POST["password_2"];
        }

        // Check for username errors
        // Spaces and empty strings
    	if (empty(trim($_POST["username"]))) {
    		$username_error_message = "No spaces or empty strings";
    	}
    	// Too short
    	elseif (strlen(trim($_POST["username"])) < 5) {
    		$username_error_message = "Must be at least 5 characters";
    	}
    	// Duplicate username
    	else {
	    	if ($duplicate_stmt = mysqli_prepare($connection, $check_user_duplicates)) {
	    		mysqli_stmt_bind_param($duplicate_stmt, "s", $_POST['username']);
	    		$duplicate_stmt->execute();
    	        $resultSet = $duplicate_stmt->get_result();
    	        $result = $resultSet->fetch_array();
    	        if ((int)$result[0] > 0) {
    	        	$username_error_message = "Username already exists";
    	        }
	    	}
	    	else {
	    		echo mysqli_error($connection);
    	    }
    	}

    	// Check for password errors
    	// Spaces and empty strings
    	if (empty(trim($_POST["password_1"]))) {
    		$password_error_message = "No spaces or empty strings";
    	}
    	// Too short
    	elseif (strlen(trim($_POST["password_1"])) < 5) {
    		$password_error_message = "Must be at least 5 characters";
    	}
    	// Unmatching passwords
    	elseif ($_POST["password_1"] !== $_POST["password_2"]) {
    		$password_error_message = "Passwords do not match";
    	}

        // If no errors, insert user
        $insert_user_stmt = mysqli_prepare($connection, $insert_user_query);
    	if ($insert_user_stmt
    		&& empty($username_error_message)
    		&& empty($password_error_message)) {
    		$hashed_password = password_hash($_POST['password_1'], PASSWORD_DEFAULT);
    	    mysqli_stmt_bind_param($insert_user_stmt, "ss", $_POST['username'], $hashed_password);
    		
    		if(mysqli_stmt_execute($insert_user_stmt)) {
    			// Redirect to login page upon successful table insert
    			header("location: login.php");
    		}
    	}
    	else {
    		echo mysqli_error($connection);
    	}
    }

    echo $twig->render('create_account.html',
    	               ['page_name' => 'Create Account',
    	                'username_error_message' => $username_error_message,
    	                'password_error_message' => $password_error_message,
    	                'username_value' => $username_value,
    	                'password_1_value' => $password_1_value,
    	                'password_2_value' => $password_2_value]);
?>