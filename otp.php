<?php
ob_start();
session_start();

require("includes/sendMessage.php");
require("includes/kc_connection.php");
require("includes/common-functions.php");

if(!isset($_SESSION['loginId'])){
  header("location:/wcc_real_estate/index.php");
  exit();
}
?>
<!DOCTYPE html>
<?php

if(isset($_POST['sign-in']) && isset($_POST['otpval'])){  
  // && isset($_POST['otp'])
if(isset($_POST['otpval'])){  
    $optval = $_POST['otpval'];
  //   echo "<pre>";
  //  print_r($optval);
  //  echo "</pre><br>";
   $otpsrt = $optval[0].$optval[1].$optval[2].$optval[3].$optval[4].$optval[5];  
   $_POST['otp'] = $otpsrt;
   //  echo "OTP: ".$otpsrt."<br>";
  //  echo "Post['otp']: ".$_POST['otp'];
}
  if($_POST['otp'] != null){
    $otp = mysqli_fetch_assoc(mysqli_query($conn,"select * from kc_login_otp where otp = '".$_POST['otp']."' and user_id = '".$_SESSION['loginId']."' and expired = 0 and attempts < 3 order by id desc"));
    if($otp != null){
      $login_rs = mysqli_query($conn,"select id, name, login_type,mobile from kc_login where id = '".$otp['user_id']."' ");
      if(mysqli_num_rows($login_rs) == 1){
        $login_details = mysqli_fetch_assoc($login_rs);
          if(isset($_POST['remember'])){
            
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
          $_SESSION['login_type'] = $login_details['login_type'];//'super_admin';
          $_SESSION['timestamp'] = time();
          header("Location:management/dashboard.php");
          // die;
        
      }
    }else{
      $otp_failure = mysqli_fetch_assoc(mysqli_query($conn,"select * from kc_login_otp where user_id = '".$_SESSION['loginId']."' and expired = 0 order by user_id desc, id desc limit 0,1"));
      if($otp_failure['attempts'] < 3){
        mysqli_query($conn,"UPDATE kc_login_otp set attempts = '".$otp_failure['attempts']."'+1 where user_id = '".$otp_failure['user_id']."' and id = '".$otp_failure['id']."'");
      }else{
        $_SESSION['attempts'] = $otp_failure['attempts'];
        $_SESSION['otp_error'] = 'Now you can not login before 24 hours otherwise you can contact your administration';
      }
     
    }
  }
}

if (isset($_POST['resend_otp'])) {
  sendOTP($conn,$_SESSION['mobileNo'],$_SESSION['loginId']);
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
    <!-- Login -->

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <style>
      .design-tab{
        display:inline-block;
        width:15%;
      }
    </style>
  </head>
  <body class="login-page login-bg">
    <div class="login-box">
      <div class="login-logo">
        <a href="index.php"><b>WCC</b></a>
      </div><!-- /.login-logo -->
      <div class="login-box-body">
        <p class="login-box-msg">Sign in to start your session</p>
        <form name="login-frm" id="login-frm" method="post">
          
          <!-- <div class="form-group has-feedback">
            <input type="text" name="otp" id="otp" class="form-control" placeholder="OTP" value="<?php if(isset($_COOKIE['password'])){ echo $_COOKIE['password']; } ?>" autocomplete="off"/>
            <span class="glyphicon glyphicon-phone form-control-feedback"></span>
          </div> -->
          <div class="mb-6 text-center">
            <div id="otp" class="flex justify-center">
              <input class="m-2 text-center form-control form-control-solid rounded focus:border-blue-400 focus:shadow-outline design-tab" name="otpval[]" type="text" id="first" maxlength="1" />
              <input class="m-2 text-center form-control form-control-solid rounded focus:border-blue-400 focus:shadow-outline design-tab" name="otpval[]" type="text" id="second" maxlength="1" />
              <input class="m-2 text-center form-control form-control-solid rounded focus:border-blue-400 focus:shadow-outline design-tab" name="otpval[]" type="text" id="third" maxlength="1" />
              <input class="m-2 text-center form-control form-control-solid rounded focus:border-blue-400 focus:shadow-outline design-tab" name="otpval[]" type="text" id="fourth" maxlength="1" />
              <input class="m-2 text-center form-control form-control-solid rounded focus:border-blue-400 focus:shadow-outline design-tab" name="otpval[]" type="text" id="fifth" maxlength="1" />
              <input class="m-2 text-center form-control form-control-solid rounded focus:border-blue-400 focus:shadow-outline design-tab" name="otpval[]" type="text" id="sixth" maxlength="1" />
            </div>
          </div>


          <?php // print_r($_SESSION['attempts']); die; ?>
          <h5 class="text-danger"><?php if(isset($_SESSION['otp_error']) && $_SESSION['otp_error'] != "" && $_SESSION['attempts'] >= 3){ print_r($_SESSION['otp_error']); }else{
            ?><span>OTP expire after : <span class="countdown text-right" style="font-size:17px; font-weight:bold;"></span></span><?php
          } ?></h5>
          <div class="row">
            <div class="col-xs-8">
              <?php //$l $_SESSION['loginId']; die; ?>
              
              <form method="post">
                <button type="submit" name="resend_otp" class="btn text-danger">Resend OTP</button>
                
              </form>
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

      $(document).ready(function(){
        var timer2 = "10:01";
        var interval = setInterval(function() {
          var timer = timer2.split(':');
          //by parsing integer, I avoid all extra string processing
          var minutes = parseInt(timer[0], 10);
          var seconds = parseInt(timer[1], 10);
          --seconds;
          minutes = (seconds < 0) ? --minutes : minutes;
          if (minutes < 0) clearInterval(interval);
          seconds = (seconds < 0) ? 59 : seconds;
          seconds = (seconds < 10) ? '0' + seconds : seconds;
          //minutes = (minutes < 10) ?  minutes : minutes;
          $('.countdown').html(minutes + ':' + seconds);
          timer2 = minutes + ':' + seconds;
        }, 1000);
      });

      // function OTPInput() {
      //   const inputs = document.querySelectorAll('#otp > *[id]');
      //   for (let i = 0; i < inputs.length; i++) {
      //     inputs[i].addEventListener('keydown', function(event) {
      //       if (event.key === "Backspace") {
      //         inputs[i].value = '';
      //         if (i !== 0)
      //           inputs[i - 1].focus();
      //       } else {
      //         if (i === inputs.length - 1 && inputs[i].value !== '') {
      //           return true;
      //         } else if (event.keyCode > 47 && event.keyCode < 58) {
      //           inputs[i].value = event.key;
      //           if (i !== inputs.length - 1)
      //             inputs[i + 1].focus();
      //           event.preventDefault();
      //         } else if (event.keyCode > 64 && event.keyCode < 91) {
      //           inputs[i].value = String.fromCharCode(event.keyCode);
      //           if (i !== inputs.length - 1)
      //             inputs[i + 1].focus();
      //           event.preventDefault();
      //         }
      //       }
      //     });
      //   }
      // }
      // OTPInput();

      $(function() {
          $("input.design-tab").keyup(function () {
              if (this.value.length == 1) {
                $(this).next('input.design-tab').focus();
              }
          });
      });
    </script>
  </body>
</html>