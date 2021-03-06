<?php
	require_once(dirname(__FILE__).'/includes/functions.php');
	include(dirname(__FILE__).'/includes/head.php');	require_once(dirname(__FILE__).'/includes/db_connect.php');
	if(isset($_GET['id']) && isset($_GET['token']) && extension_loaded('openssl') && PASS_RESET){
		$_GET['token'] = rawurldecode($_GET['token']);
		$stmt = $mysqli->prepare("SELECT timestamp, token, id, reset FROM reset_password WHERE user_id=? ORDER BY id DESC");
		$stmt->bind_param("i", $_GET['id']);
		$stmt->execute();
		$stmt->bind_result($timestamp, $token, $useless_id, $reset1);
		$stmt->fetch();
		$stmt->close();
		if($_GET['token'] == $token && $timestamp > (time()-60*60*4) && $reset1 == 0){
			$stmt = $mysqli->prepare("SELECT rank, name, email FROM users WHERE id=? AND deleted=0");
			$stmt->bind_param("i", $_GET['id']);
			$stmt->execute();
			$stmt->bind_result($rank, $name, $email);
			$stmt->fetch();
			$stmt->close();
			$reset=base64_encode(openssl_random_pseudo_bytes(5));
			$stmt = $mysqli->prepare("UPDATE users SET password=? WHERE id=? AND deleted=0");
			$password = password_hash($reset, PASSWORD_DEFAULT);
			$stmt->bind_param("si", $password, $_GET['id']);
			$stmt->execute();
			$stmt->close();
			if($rank >= 3 && ADMIN_AUTH){
				$mysqli->select_db(MAIN_DB);
				$stmt = $mysqli->prepare("UPDATE users SET password=? WHERE link=?");
				$stmt->bind_param("ss", $password, $name);
				$stmt->execute();
				$stmt->close();
			}
			require_once(dirname(__FILE__).'/PHPMailer/PHPMailerAutoload.php');
			$mail = new PHPMailer(true);
			$mail->isSMTP();
			$mail->Host = SMTP_HOST;
			$mail->SMTPAuth = true;
			$mail->CharSet = 'UTF-8';
			$mail->Username = SMTP_USER;
			$mail->Password = SMTP_PASS;
			$mail->SMTPSecure = SMTP_PROTOCOL;
			$mail->Port = SMTP_PORT; 
			$mail->addAddress($email);
			$mail->setFrom(SMTP_USER, SMTP_NAME);
			$mail->isHTML(false);
			$mail->Subject = 'Swim password reset';
			$mail->Body = "Hi there, \r\nYour new password is: $reset (Please do not reply to this email)";
			if(!$mail->Send()){
				echo $mail->ErrorInfo;
			}
			$stmt = $mysqli->prepare("UPDATE reset_password SET reset = 1 WHERE token = ?");
			$stmt->bind_param("s", $_GET['token']);
			$stmt->execute();
			$stmt->close();
		}
	}
?>
<!DOCTYPE html>
<html>
	<body>
		<?php
		include(dirname(__FILE__).'/includes/nav.php');
		?>
		<div class='container'>
			<div class='jumbotron'>
				<h2 class="text-center">Reset password</h2>
				<?php help("Here you can reset your password. Just enter your email, click enter, and check your email. Unfortunately, if you have not previously logged in and added your email, this form cannot help you.", false); ?>
				
				<form method="post" onsubmit="reset_password(this); return false;">
				<?php
				if(!isset($reset) && PASS_RESET){
					echo '<div class="row bottom2">';
					echo '	<div class="bottom2 col-lg-3 col-lg-offset-3 col-md-8 col-md-offset-2 col-sm-10 col-xs-12">';
					echo '		<input class="form-control" type="text" placeholder="Email" name="username">';
					echo '	</div>';
					echo '	<div class="col-lg-2 col-md-8 col-sm-2 col-xs-12">';
					echo '		<button class="btn btn-primary form-control" type="submit">Reset password</button>';
					echo '	</div>';
					echo '</div>';
				}else if(PASS_RESET){
					echo "<div class='row'><div class='col-lg-10 col-lg-offset-1 col-md-8 col-md-offset-2 col-sm-12 col-xs-12'><div class='alert alert-success alert-dismissible' role='alert'><button type='button' class='close' data-dismiss='alert' aria-label='Close alert'><span aria-hidden='true'>&times;</span></button><span class='glyphicon glyphicon-ok' aria-hidden='true'></span><span class='sr-only'>Success:</span>Your new password is: $reset You have also been sent an email with your password</div></div></div>";
				}else{					echo '<div class="row text-center">Password resets are disabled at this time.</div>';				}
				?>
				</form>
			</div>
		</div>
	<?php
		include(dirname(__FILE__).'/includes/scripts.php');
	?>
	</body> 
</html>