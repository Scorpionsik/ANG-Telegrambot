<html>
  <head>
    <!--Load the AJAX API-->
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">

<?php
	include "connection_agent.php";
	$dblink = new mysqli($host, $dblogin, $dbpassw, $database); 
	$values = array();
	$queries = array("SELECT * FROM `Not yet registered users` LEFT JOIN `Not binding users` ON `Not yet registered users`.Id_whitelist_user=`Not binding users`.Id_whitelist_user WHERE !isnull(`Not binding users`.Id_whitelist_user) AND `Not yet registered users`.Id_whitelist_user!=10 AND `Not yet registered users`.Id_whitelist_user!=11 ORDER BY `Not yet registered users`.Username;","SELECT * FROM `!Registered users` LEFT JOIN `Not binding users` ON `!Registered users`.Id_whitelist_user=`Not binding users`.Id_whitelist_user WHERE !isnull(`Not binding users`.Id_whitelist_user) AND `!Registered users`.Id_whitelist_user!=10 AND `!Registered users`.Id_whitelist_user!=11 ORDER BY `!Registered users`.Username;","SELECT * FROM `Not yet registered users` LEFT JOIN `Not binding users` ON `Not yet registered users`.Id_whitelist_user=`Not binding users`.Id_whitelist_user WHERE isnull(`Not binding users`.Id_whitelist_user) AND `Not yet registered users`.Id_whitelist_user!=10 AND `Not yet registered users`.Id_whitelist_user!=11 ORDER BY `Not yet registered users`.Username;","SELECT * FROM `!Registered users` LEFT JOIN `Not binding users` ON `!Registered users`.Id_whitelist_user=`Not binding users`.Id_whitelist_user WHERE isnull(`Not binding users`.Id_whitelist_user) AND `!Registered users`.Id_whitelist_user!=10 AND `!Registered users`.Id_whitelist_user!=11 ORDER BY `!Registered users`.Username;");
	
	foreach($queries as $query)
	{
		$result = mysqli_query($dblink, $query) or die("Ошибка " . mysqli_error($dblink));	
		if($result)
		{
			$values[] = mysqli_num_rows($result);
			mysqli_free_result($result);
		}
		else $values[] = 0;
	}
	
	$all_register = $values[1] + $values[3];
	$all_not_register = $values[0] + $values[2];
?>
      // Load the Visualization API and the corechart package.
      google.charts.load('current', {'packages':['corechart']});

      // Set a callback to run when the Google Visualization API is loaded.
      google.charts.setOnLoadCallback(drawChart);

      // Callback that creates and populates a data table,
      // instantiates the pie chart, passes in the data and
      // draws it.
      function drawChart() {

        // Create the data table.
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Состояние');
        data.addColumn('number', 'Количество');
        data.addRows([
          ['Не подключался, нет привязки', <?php echo $values[0]; ?>],
          ['Подключался, нет привязки', <?php echo $values[1]; ?>],
          ['Не подключался, есть привязка', <?php echo $values[2]; ?>],
          ['Подключался, есть привязка', <?php echo $values[3]; ?>]
        ]);
		
		var data_all = new google.visualization.DataTable();
		data_all.addColumn('string', 'Состояние');
        data_all.addColumn('number', 'Количество');
		data_all.addRows([
          ['Подключились', <?php echo $all_register; ?>],
          ['Не подключились', <?php echo $all_not_register; ?>]
        ]);
        // Set chart options
        var options = {'title':'Информация по агентам, подключенным к боту',
                       'width':800,
                       'height':300};
					   
		var options_all = {'title':<?php echo "Всего " . array_sum($values) . " агентов" ?>,
                       'width':800,
                       'height':300};

        // Instantiate and draw our chart, passing in some options.
        var chart = new google.visualization.PieChart(document.getElementById('chart_div'));
        chart.draw(data, options);
		var chart_all = new google.visualization.PieChart(document.getElementById('chart_div_all'));
		chart_all.draw(data_all, options_all);
      }
    </script>
  </head>

  <body>
    <!--Div that will hold the pie chart-->
    <div id="chart_div"></div>
	<div id="chart_div_all"></div>
  </body>
</html>