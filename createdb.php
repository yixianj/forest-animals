<?php
	if (mysqli_connect_errno()) {
		echo "DB Connection Error: " . mysqli_connect_errno();
	}

	$create_animals_table = 'CREATE TABLE IF NOT EXISTS Animals ('
	                        .'id INT AUTO_INCREMENT, '
	                        .'name VARCHAR(80) UNIQUE, '
	                        .'info TEXT, '
	                        .'image_url, '
	                        .'PRIMARY KEY(id))';
    if (mysqli_query($connection, $create_animals_table)) {
    	echo "Success";
    }
    else {
    	echo mysqli_error($connection);
    }
?>