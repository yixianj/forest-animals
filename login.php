<?php
    require_once('db.php');

    $username_exists_query = "SELECT id, username, password FROM users WHERE username = ?";
    $db_password = "";
    $username_error_message = "";
    $password_error_message = "";
    $username_value = "";
    $password_value = "";

    if ($_SERVER['REQUEST_METHOD'] == "POST") {
    	// Check for username errors
    	// Username entered
    	if (isset($_POST["username"])) {
    		// Set username value so form saves what was entered
	    	$username_value = $_POST["username"];
	    	// Check that username exists
	    	if ($username_exists_stmt = mysqli_prepare($connection, $username_exists_query)) {
	    		mysqli_stmt_bind_param($username_exists_stmt, "s", $_POST["username"]);
	    		$username_exists_stmt->execute();
	    		$username_result_set = $username_exists_stmt->get_result();
	    		$username_exists_result = $username_result_set->fetch_assoc();
                $username_result_set->free();
	    		if (empty($username_exists_result["username"])) {
	    			$username_error_message = "Username is incorrect.";
	    		}
	    		else {
	    			$db_password = $username_exists_result["password"];
	    		}
	    	}
        }
        // Username not entered or empty string
        else {
        	$username_error_message = "Please enter a username.";
        }
        // Check for password errors
        if (empty($username_error_message) && isset($_POST["password"]) && trim($_POST["password"])) {
        	// Set password value so form saves what was entered
        	$password_value = $_POST["password"];
        	// Check that password is correct given the username
        	if (password_verify($password_value, $db_password)) {
                session_id($username_exists_result["id"]);
                session_start();
                $_SESSION["loggedin"] = True;
                $_SESSION["username"] = $username_value;
                if ($username_value == "admin") {
                    $_SESSION["admin"] = True;
                }
                 else {
                    $_SESSION["admin"] = False;
                 }
        		header("location: index.php");
        	}
        	else {
        		$password_error_message = "Password is incorrect.";
        	}
        }
        else {
            $password_error_message = "Please enter a password";
        }
    }


    echo $twig->render('login.html',
    	               ['page_name' => 'Login',
                        'login_status' => isset($_SESSION['loggedin']),
    	                'username_error_message' => $username_error_message,
    	                'password_error_message' => $password_error_message,
    	                'username_value' => $username_value,
    	                'password_value' => $password_value]);
?>