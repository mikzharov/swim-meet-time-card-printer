<?php
	require_once( dirname(__FILE__).'/includes/functions.php');
	if(!(isset($_SESSION['name']) && isset($_SESSION['division']) && isset($_SESSION['competes_with']) && isset($_SESSION['rank']))){
		header("HTTP/1.1 303 See Other");
		header('Location: home.php');
		die();
	}
	if(!isset($_GET['id'])){	
?>
<!DOCTYPE html>
<html>
	<body>
		<?php
		<div class='container'>
			<div class='jumbotron'>
				<h2 class="text-center">Print timecards</h2>
				<div style="text-align:center;" class="row bottom3">
					<a class="text-center" data-toggle="collapse" href="#print_widget_help">Help?</a>
				</div>
				<div id="print_widget_help" class="row bottom3 collapse">
					<p class="text-left">Click on a meet name and it will take you to a printer friendly page, then click CTRL + P in order to print the timecards.</p>
				</div>
				<?php
				require_once( dirname(__FILE__).'/includes/db_connect.php');
				$stmt = $mysqli->prepare("SELECT name, id FROM meets WHERE deleted = 0 AND date > CURDATE()");
				$stmt->execute();
				$stmt->bind_result($name, $id);
				while($stmt->fetch()){
					echo "<div class='row bottom2'><div class='col-lg-12 col-md-12 col-sm-12 col-xs-12'><h4 class='text-center'><a href='print2.php?id=$id'>$name</a></h4></div></div>";
				}
				$stmt->close();
				?>
			</div>
		</div>
	</body>
</html>
<?php
	}else{
?>
* { margin: 0; padding: 0;}
</head>
<?php
		require_once( dirname(__FILE__).'/includes/db_connect.php');
		$stmt = $mysqli->prepare("SELECT type FROM meets WHERE deleted = 0 AND id=?");
		$stmt->bind_param('i', $_GET['id']);
		$stmt->execute();
		$stmt->bind_result($type);
		$stmt->fetch();
		$stmt->close();
		
		$stmt = $mysqli->prepare("SELECT text FROM meet_events WHERE deleted=0 AND id=?");
		$stmt->bind_param('i', $type);
		$stmt->execute();
		$stmt->bind_result($text);
		$stmt->fetch();
		$stmt->close();
		
		$stmt->close();
			$stmt = $mysqli->prepare("SELECT name, time, relay_letter, event FROM timecards WHERE deleted=0 AND meet_id = ? AND event = ? ORDER BY CONVERT(time, TIME)");
			$stmt->execute();
			$stmt->bind_result($name, $time, $relay_letter, $event);
			while($stmt->fetch()){
				$tmp->name = name($name);
			}
			$stmt->close();
	}
?>
<script>