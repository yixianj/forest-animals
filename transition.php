<?php
include_once("config/config.php");

session_start();

#echo $_SESSION["transition"]["return_page_url"];

$transition = $_SESSION["transition"];
unset($_SESSION["transition"]);

echo $twig->render("transition.html", ["message" => $transition["message"],
	                                   "return_page_url" => $transition["return_page_url"],
	                                   "return_button_text" => $transition["return_button_text"]]);
?>