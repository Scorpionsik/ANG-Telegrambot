<?php
require_once "givemyprecious.php";

	if (isset($_GET['entity']))
	{
		$entity_id = htmlentities($_GET['entity']);
		$json_photos = file_get_contents("https://an-gorod-image.com.ua/api/adimages?code=${entity_id}&key=${token}");
		echo json_decode($json_photos);
	}
?>