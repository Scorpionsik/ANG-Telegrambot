<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="UTF-8">
	<title>Фотографии объекта</title>
	<meta name="description" content="Фотографии объекта">
	<meta name="viewport" content="width=device-width, initial-scale=1"/>
</head>
<body>
<?php
include "givemyprecious.php";

	if (isset($_GET['entity']))
	{
		$entity_id = htmlentities($_GET['entity']);
		$json_photos = file_get_contents("https://an-gorod-image.com.ua/api/adimages?code=${entity_id}&key=${token}");
		$arrays = json_decode($json_photos);
		
		foreach($arrays as $array)
		{
			foreach($array as $key => $value)
			{
				if($key == "origin") echo "<image src='https://an-gorod-image.com.ua/" . $value . "'/><br>";
			}
		}
	}
?>
</body>
</html>