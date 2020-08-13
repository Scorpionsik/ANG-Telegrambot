<?php
include "givemyprecious.php";

	if (isset($_GET['entity']))
	{
		$entity_id = htmlentities($_GET['entity']);
		$json_photos = file_get_contents("https://an-gorod-image.com.ua/api/adimages?code=${entity_id}&key=${token}");
		$array_pic = json_decode($json_photos);
		
		foreach($array_pic as $key => $pic_path)
		{
			if($key == "origin")
				echo $pic_path . "<br>";
		}
	}
?>