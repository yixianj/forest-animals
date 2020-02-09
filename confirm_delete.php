<?php
    require_once('db.php');

    session_start();

    if ($_SERVER['REQUEST_METHOD'] == "POST") {

        if (isset($_POST['delete_animal'])) {
            $delete_animal_query = "DELETE FROM Animals WHERE name = ?";
            if ($delete_stmt = mysqli_prepare($connection, $delete_animal_query)) {
                mysqli_stmt_bind_param($delete_stmt, "s", $_SESSION['edited_animal']['name']);
                $delete_stmt->execute();
                while(mysqli_next_result($connection)){;}

                # Delete files
                if(!unlink($_SESSION['edited_animal']['info_url'])) {

                  $_SESSION['error'] = array('message' => "Unable delete info file '" . $_SESSION['edited_animal']['info_url'] ."'",
                                             'return_page_url' => './learn.php',
                                             'return_button_text' => 'Return');
                  Header('Location: ./error.php');
                }

                if(!unlink($_SESSION['edited_animal']['image_url'])) {

                  $_SESSION['error'] = array('message' => "Unable delete info file '" . $_SESSION['edited_animal']['image_url'] ."'",
                                             'return_page_url' => './learn.php',
                                             'return_button_text' => 'Return');
                  Header('Location: ./error.php');
                }
            }
            unset($_SESSION['edited_animal']);
            Header('Location: ./learn.php');            
        }
        else if (isset($_POST['keep_animal'])) {
            unset($_SESSION['edited_animal']);
            Header('Location: ./learn.php');
        }
        else {
            $_SESSION['error'] = array("message" => "Unable to delete animal",
                                       "return_page_url" => "./learn.php",
                                       "return_button_text" => "Try Again");
            Header("Locatoin: ./error.php");
        }

    }
    echo $twig->render('confirm_delete.html', ['animal_name' => $_SESSION['edited_animal']['name']]);
?>