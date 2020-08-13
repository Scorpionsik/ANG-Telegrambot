<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="UTF-8">
	<title>Фотографии объекта</title>
	<meta name="description" content="Фотографии объекта">
	    <meta property="og:type" content="article">
    <meta property="og:title" content="[45392] Продаётся 2-комнатная квартира Клочковская">
    <meta property="og:description" content="">
    <meta property="og:image" content="https://ireland.apollo.olxcdn.com:443/v1/files/z3upfb16v0gb2-UA/image;s=1500x1583">
    <meta property="og:site_name" content="Telegraph">
	
	<meta name="twitter:description" content="В прошлой части был показан процесс разработки модели цилиндра. В этой речь пойдет о его материализации. На момент начала этой работы у меня имелся опыт литья не...">
<meta name="twitter:title" content="Свой 2-тактный мотор: песочница, куличики и 10кг расплавленного металла" />
<meta name="twitter:card" content="summary_large_image" />
<meta name="twitter:site" content="@habr_com" />
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