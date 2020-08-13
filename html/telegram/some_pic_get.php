<?php
	if (isset($_GET['entity']))
	{
		$entity_id = htmlentities($_GET['entity']);
		echo $entity_id;
		echo file_get_contents("https://an-gorod-image.com.ua/api/adimages?code=${entity_id}&key=YbKhGDGFn67nHgl5fhFdflr5jGg3kg");
	}
?>