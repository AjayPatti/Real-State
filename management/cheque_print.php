<?php
ob_start();
session_start();

require("../includes/host.php");
require("../includes/kc_connection.php");
require("../includes/common-functions.php");
require("../includes/checkAuth.php");

if(!isset($_GET['cheque']) || !is_numeric($_GET['cheque']) || !($_GET['cheque'] > 0)){
	die;
}

$cheque_id = $_GET['cheque'];
$cheque_details = mysqli_fetch_assoc(mysqli_query($conn,"select * from kc_cheques where id = '".$cheque_id."' and status = '1' limit 0,1 "));
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>WCC Real Estate Pvt. Ltd.</title>
<style type="text/css" media="print">
.hide{
	display:none;
}
</style>
<style type="text/css">
  body{
    font-size: 18px;
  }
.button{
	background-color: #008CBA; border: none;color:white; padding: 15px 32px;text-align: center;text-decoration: none;font-size: 16px;cursor:pointer;
}
</style>
</head>

<body>
	<button onClick="window.print();" class="hide button">Print</button>
  <table style="width:700px; height:400px;">
    <tr>
      <td></td>
      <td>&nbsp;</td>
      <td style="top:40px; left:655px; font-weight: bolder; letter-spacing:11px; position:absolute;"><?php echo date("dmY",strtotime($cheque_details['date'])); ?></td>
    </tr>
    <tr>
      <td></td>
      <td colspan="2" style="top:89px; font-weight: bolder; left:94px; position:absolute;"><?php echo $cheque_details['name']; ?></td>
    </tr>
    <tr>
      <td></td>
      <td style="top:126px; left:157px; font-weight: bolder; position:absolute;"><?php echo numberToWord($cheque_details['amount']); ?></td>
    </tr>
    <tr>
      <td></td>
      <td style="top:148px; left:662px; font-weight: bolder; position:absolute;">**<?php echo $cheque_details['amount']; ?>**</td>
    </tr>
    <tr><td colspan="2" style="top:270px; left:360px; border-bottom:solid 1px; border-top:solid 1px; position:absolute;">Account Payee</td></tr>
  </table>
</body>
</html>