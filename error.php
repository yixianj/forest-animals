<?php
include_once("config/config.php");

session_start();

$error = $_SESSION["error"];
unset($_SESSION["error"]);

echo $twig->render("error.html", ["error_message" => $error["message"],
	                              "return_page_url" => $error["return_page_url"],
	                              "return_button_text" => $error["return_button_text"]]);
?>