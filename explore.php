<?php

require_once('db.php');

session_start();

function add_resource($connection) {
	// Check all fields are filled
	if (!isset($_POST['resource_name']) || empty(trim($_POST['resource_name']))
		|| !isset($_POST['resource_url']) || empty(trim($_POST['resource_url']))
		|| !isset($_POST['resource_description']) || empty(trim($_POST['resource_description'])) ) {

		$_SESSION['error'] = array("message" => "Please fill all fields.",
			                       "return_page_url" => "./explore.php",
			                       "return_button_text" => "Try again");
	    Header("location: ./error.php");
	}
	else {
		// Add resource to database
		$_POST['resource_url'] = "https://" . $_POST['resource_url'];
		$add_resource_query = "INSERT INTO Resources(name, url, description) VALUES(?,?,?)";
		if ($add_resource_stmt = mysqli_prepare($connection, $add_resource_query)) {
			mysqli_stmt_bind_param($add_resource_stmt, "sss",
				                   $_POST['resource_name'],
				                   $_POST['resource_url'],
				                   $_POST['resource_description']);
			$add_resource_stmt->execute();
			while(mysqli_next_result($connection)){;}
		}

        echo mysqli_error($connection);
		// Check for errors in database
		if (mysqli_error($connection)) {
			mysqli_rollback($connection);
			$_SESSION['error'] = array("message" => "Could not insert resource in database. Name or Url may already exist.",
				                       "return_page_url" => "./explore.php",
				                       "return_button_text" => "Try again");
		    Header("location: ./error.php");	
		}
		else {
			$_SESSION["transition"] = array("message" => "Successfully added resource",
				                            "return_page_url" => "./explore.php",
				                            "return_button_text" => "Continue");
			Header('Location: ./transition.php');
		}
	}
}

function delete_resource($connection) {
	$delete_resource_query = "DELETE FROM Resources WHERE id = ?";
	if ($delete_stmt = mysqli_prepare($connection, $delete_resource_query)) {
		mysqli_stmt_bind_param($delete_stmt, "s", $_POST['resource_id']);
		if(!$delete_stmt->execute()) {
			Header('Location: ./error.php');
			$_SESSION['error'] = array("message" => "Could not delete resource.",
				                       "return_page_url" => "./explore.php",
				                       "return_button_text" => "Try again");
		}
		while(mysqli_next_result($connection)){;}
	}
}


if ($_SERVER['REQUEST_METHOD'] == "POST") {
	if (isset($_POST['add_resource'])) {
		add_resource($connection);
	}
	elseif (isset($_POST['delete_resource'])) {
		delete_resource($connection);
	}
}

$get_resources_query = "SELECT * FROM Resources";
$resources_result = mysqli_query($connection, $get_resources_query);
$resources = null;
if ($resources_result) {
	$resources = mysqli_fetch_all($resources_result, MYSQLI_ASSOC);
}
else {
	$resources = array();
}

echo $twig->render('explore.html', ['is_admin' => isset($_SESSION['loggedin']) ? $_SESSION["admin"] : False,
                                    'resources' => $resources,
                                    'page_name' => 'Explore',
                                    'login_status' => isset($_SESSION['loggedin'])]);

?>