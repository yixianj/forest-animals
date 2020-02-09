<?php
    require_once('db.php');

    session_start();

    echo $twig->render('homepage.html',
    	               ['page_name' => 'Forest Animals',
    	                'login_status' => isset($_SESSION['loggedin'])]);
?>