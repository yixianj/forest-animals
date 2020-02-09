<?php
    require_once('db.php');

    session_start();

    $get_animal_data = "SELECT * FROM Animals";
    $animals_set = mysqli_query($connection, $get_animal_data);
    if (!$animals_set) { echo mysqli_error($connection); }
    $animals_data = mysqli_fetch_all($animals_set, MYSQLI_BOTH);
    mysqli_free_result($animals_set);
    mysqli_next_result($connection);

    for ($i = 0; $i < count($animals_data); $i++) {
        $animals_data[$i]['image_url'] = $config['images_location']
    	                                 . $animals_data[$i]['image_url'];
    	  $animals_data[$i]['info_url'] = $config['info_location']
                                        . $animals_data[$i]['info_url'];
        $animals_data[$i]['index'] = $i;
        if (file_exists($animals_data[$i]['info_url'])) {
        	$animals_data[$i]['info'] = file_get_contents($animals_data[$i]['info_url']);
        }
    }

    function add_new_animal($config, $connection) {
        // Check if all fields are filled
        if (!isset($_POST['animal_name']) || empty(trim($_POST['animal_name'], ' '))
            || !isset($_POST['animal_description']) || empty(trim($_POST['animal_description'], ' '))
            || empty($_FILES)) {
                $_SESSION['error'] = array('message' => 'Fill all fields.',
                                           'return_page_url' => './learn.php',
                                           'return_button_text' => 'Return');
                Header('Location: ./error.php');              
        }
        
        $_POST['animal_name'] = str_replace(" ", "_", strtolower($_POST['animal_name']));

        // Check if animal already exists
        $info_filename = $config['info_location'] . $_POST['animal_name'] . ".txt";
        if (file_exists($info_filename)) {
            $_SESSION['error'] = array('message' => 'The animal ' . $_POST['animal_name'] . ' is already included.',
                                       'return_page_url' => './learn.php',
                                       'return_button_text' => 'Return');
            Header('Location: ./error.php');
        }
        
       // Check if image already exists
        $image_filename = $config['images_location'] . $_FILES['upload_image']['name'];
        if (file_exists($image_filename)) {
            $_SESSION['error'] = array('message' => 'File: ' . $image_filename . ' already exists.',
                                       'return_page_url' => './learn.php',
                                       'return_button_text' => 'Return');
            Header('Location: ./error.php');
        }

        // Check if file is appropriate
       $image_file_type = strtolower(pathinfo($image_filename,PATHINFO_EXTENSION));
       $file_size = $config['images_location'] . $_FILES['upload_image']['size'];
       if (!in_array($image_file_type, array('jpg', 'jpeg', 'png', 'gif')) 
           || $file_size > 500000) {
                $_SESSION['error'] = array('message' => 'Inappropriate file type or file size too large',
                                           'return_page_url' => './learn.php',
                                           'return_button_text' => 'Return');     
                Header('Location: ./error.php');       
       }

       // If no errors, add animal to database
       $add_animal_query = 'INSERT INTO Animals (name, info_url, image_url) VALUES (?, ?, ?)';
        if ($add_animal_stmt = mysqli_prepare($connection, $add_animal_query)) {
            mysqli_stmt_bind_param($add_animal_stmt,"sss", $_POST["animal_name"],
                                   basename($info_filename), $_FILES['upload_image']['name']);
            $add_animal_stmt->execute();
            while(mysqli_next_result($connection)){;}

            file_put_contents($info_filename, $_POST['animal_description']);


            if (!move_uploaded_file($_FILES['upload_image']['tmp_name'],
                                    $config['images_location'] . $_FILES['upload_image']['name'])
                ) {
                   $_SESSION['error'] = array('message' => 'Failed to upload image.',
                                             'return_page_url' => './learn.php',
                                             'return_button_text' => 'Try again');     
                   Header('Location: ./error.php');              
            }

            $_SESSION['transition'] = array("message" => "Animal '" . $_POST['animal_name'] . "' added.",
                                            "return_button_text" => "Add another animal.",
                                            "return_page_url" => "./learn.php");
            Header('Location: ./transition.php');
        }
    }

    function edit_animal($config, $connection, $animals_data) {;
      $prev_animal_data = $animals_data[$_POST['array_index']];

      $new_info_filepath = $_POST['animal_name'] . ".txt";
      $new_image_filepath = $_POST['animal_name'] . "." . pathinfo($prev_animal_data['image_url'], PATHINFO_EXTENSION);

      // Check if animal already exists
      if (file_exists($new_info_filepath)) {
          $_SESSION['error'] = array('message' => 'The animal ' . $_POST['animal_name'] . ' is already included. Try a different name',
                                     'return_page_url' => './learn.php',
                                     'return_button_text' => 'Return');
          Header('Location: ./error.php');
      }

      // Rename info file
      if (!rename($prev_animal_data['info_url'], $config['info_location'] . $new_info_filepath)) {
          $_SESSION['error'] = array('message' => 'Unable to rename info file from ' . " "
                                     . $prev_animal_data['info_url'] . " to " . $config['info_location'] . $new_info_filepath,
                                     'return_page_url' => './learn.php',
                                     'return_button_text' => 'Return');
          Header('Location: ./error.php');
      }

      // Rename image file
      if (!rename($prev_animal_data['image_url'], $config['images_location'] . $new_image_filepath))
      {
          $_SESSION['error'] = array('message' => 'Unable to rename image file ' . "/" . $prev_animal_data['image_url']
                                                  . " to " . $new_image_filepath,
                                     'return_page_url' => './learn.php',
                                     'return_button_text' => 'Return');
          Header('Location: ./error.php');
      }


      // Update table to reflect file and animal name changes
      $change_name_query = "UPDATE Animals SET name = ?, info_url = ?, image_url = ? WHERE name = ?";
      if ($change_name_stmt = mysqli_prepare($connection, $change_name_query)) {
          mysqli_stmt_bind_param($change_name_stmt, "ssss", $_POST['animal_name'],
                                 $new_info_filepath,
                                 $new_image_filepath,
                                 $prev_animal_data['name']);
          $change_name_stmt->execute();
          while(mysqli_next_result($connection)){;}
      }
      else {
        echo mysqli_error($connection);
      }

      // Update animal info
      if (file_put_contents($config['info_location'] . $new_info_filepath, $_POST['edit_info_textarea'])) {
          $_SESSION['transition'] = array("message" => "Animal '" . $_POST['animal_name'] . "' successfully edited",
                                          "return_button_text" => "Continue",
                                          "return_page_url" => "./learn.php");     
          Header('Location: ./transition.php');
      }
      else {
          $_SESSION['error'] = array("message" => "Failed to save edits for " . $_POST['animal_name'],
                                     "return_button_text" => "Return",
                                     "return_page_url" => "./learn.php");
          Header('Location: ./error.php');
      }
    }


    // Handle requests to add or edit animals accordingly
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
      if (isset($_POST['add_animal'])) {
        add_new_animal($config, $connection);
      }
      if (isset($_POST['edit_animal'])) {
        edit_animal($config, $connection, $animals_data);
      }
      if (isset($_POST['delete_animal'])) {
        $_SESSION['edited_animal'] = $animals_data[$_POST['array_index']];
        Header('Location: ./confirm_delete.php');
      }

    }

    echo $twig->render('learn.html',
    	                 ['page_name' => 'Learn',
                        'login_status' => isset($_SESSION['loggedin']),
    	                  'animals' => $animals_data,
                        'is_admin' => isset($_SESSION['loggedin']) ? $_SESSION["admin"]:False]);

?>