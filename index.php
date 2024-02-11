<?php
ob_start();
session_start();
require("includes/sendMessage.php");
require("includes/common-functions.php");
require("includes/host.php");
?>
<!DOCTYPE html>
<?php

if(isset($_POST['sign-in'])){
	if(isset($_POST['email']) && isset($_POST['password'])){
		require("includes/kc_connection.php");


		$email = filter_post($conn,$_POST['email']);
		$password = filter_post($conn,$_POST['password']);
    // $_SESSION['csrf_token'] = csrf_token();
    if($_SESSION['csrf_token'] === $_POST['csrf_token']){

  		$login_rs = mysqli_query($conn,"select id, name, login_type,mobile,login_with_otp from kc_login where email = '$email' and password = '$password' and status = '1' ");

  		if(mysqli_num_rows($login_rs) == 1){
        $login_details = mysqli_fetch_assoc($login_rs);

        $_SESSION['loginId'] = $login_details['id'];
        $_SESSION['mobileNo'] = $login_details['mobile'];
  			if($login_details['login_type'] == 'super_admin' || $login_details['login_type'] == 'super2admin' || $login_details['login_with_otp'] == 0){
    			if(isset($_POST['remember'])){
    				ini_set('session.cookie_httponly', 1 );
    				$remember = filter_post($conn,$_POST['remember']);

    				setcookie("email", $email, time() + (86400 * 30), "/"); // 86400 = 1 day
    				setcookie("password", $password, time() + (86400 * 30), "/"); // 86400 = 1 day
    				setcookie($remember, "remember", time() + (86400 * 30), "/"); // 86400 = 1 day
    			}else{
    				setcookie("email", "", time() - 3600,"/");
    				setcookie("password", "", time() - 3600,"/");
    				//setcookie($remember, "", time() - 3600,"/");
    			}
    			$_SESSION['login_id'] = $login_details['id'];
    		
    			mysqli_query($conn, "update kc_login set last_login = '".date("Y-m-d H:i:s")."' where id = '".$login_details['id']."' ");
    			$_SESSION['login_type'] = $login_details['login_type'];     
                $_SESSION['timestamp'] = time();

    			header("Location:management/dashboard.php");
    			die;
   
        }else{
            if($_POST['otp'] == null && $_SESSION['attempts'] <=2){
                // echo '123'; die;
              sendOTP($conn,$login_details['mobile'],$login_details['id']);
            }
            header("Location:otp.php");
            exit;
        }
  		}else{
  			$_SESSION['error'] = 'Wrong Email or Password!';
  		}
    }else{
      $_SESSION['error'] = 'csrf_token is not matched';
    }
	}
}


?>
<html>
  <head>
    <meta charset="UTF-8">
    <title>WCC</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap 3.3.4 -->
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <!-- Font Awesome Icons -->
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <!-- Theme style -->
    <link href="css/AdminLTE.min.css" rel="stylesheet" type="text/css" />
    <!-- iCheck -->
    <link href="plugins/iCheck/square/blue.css" rel="stylesheet" type="text/css" />
    <!-- Login style -->
    <link href="css/login.css" rel="stylesheet" type="text/css" />
    <link rel="icon" type="image/x-icon" href="/<?php echo $host_name; ?>img/logo.png">
    <!-- Login -->

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  
  <body class="login-page login-bg">
    <div class="login-box">
      <div class="login-logo">
     
        <a href="index.php"> <img src="/<?php echo $host_name; ?>img/logo.png" class="img-circle" alt="User Image"></a>
      </div><!-- /.login-logo -->
      <div class="login-box-body">
        <p class="login-box-msg">Sign in to start your session</p>
        <form name="login-frm" id="login-frm" method="post">
          <?php csrf_token(); ?>
          <input type="hidden" name="csrf_token" class="form-control" value="<?php echo $_SESSION['csrf_token']; ?>">
          <div class="form-group has-feedback">
            <input type="email" name="email" id="email" class="form-control" placeholder="Email" value="<?php //if(isset($_COOKIE['email'])){ echo $_COOKIE['email']; } ?>" />
            <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
          </div>
          <div class="form-group has-feedback" id="pass">
            <input type="password" name="password" id="password" class="form-control" placeholder="Password" value="<?php //if(isset($_COOKIE['password'])){ echo $_COOKIE['password']; } ?>" />
            <span class="glyphicon glyphicon-lock form-control-feedback"></span>
          </div>
          <div class="row">
            <div class="col-xs-8">
              <?php /*<div class="checkbox icheck">
                <label>
                  <input type="checkbox" name="remember" value="1" <?php if(isset($_COOKIE['password'])){  ?>checked<?php } ?>> Remember Me
                </label>
              </div>*/ ?>
            </div><!-- /.col -->
            <div class="col-xs-4">
              <button type="submit" class="btn btn-primary btn-block btn-flat" id="check" name="sign-in">Sign In</button>
            </div><!-- /.col -->
          </div>
        </form>
		<?php /*<a href="javascript:;">I forgot my password</a>*/ ?>


      </div><!-- /.login-box-body -->
    </div><!-- /.login-box -->

    <!-- jQuery 2.1.4 -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="plugins/jQuery/jQuery-2.1.4.min.js" type="text/javascript"></script>
    <!-- Bootstrap 3.3.2 JS -->
    <script src="bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
    <!-- iCheck -->
    <script src="plugins/iCheck/icheck.min.js" type="text/javascript"></script>
    <script>
      $(function () {
        $('input').iCheck({
          checkboxClass: 'icheckbox_square-blue',
          radioClass: 'iradio_square-blue',
          increaseArea: '20%' // optional
        });
      });
    </script>
  </body>
</html>
