<?php
function sendMail($to,$name,$amount,$block,$plot,$date,$template){
//$template = "PaymentReceived","NextDueDate","Wishes";
$message = '<table>
<tbody>
	<tr>
		<td style="width:100.0%;border:solid #e2e2e2 1.0pt;border-bottom:none;background:white;padding:11.25pt 0in 11.25pt 0in" width="100%" align="center">
			<p class="MsoNormal"><img src="../img/logo.png" title="WCC" title="WCC" height="150"></p>
		</td>
	</tr>
	<tr>
		<td style="border-top:none;border-left:solid #e2e2e2 1.0pt;border-bottom:none;border-right:solid #e2e2e2 1.0pt;background:white;padding:0in 30.6pt 15.0pt 30.6pt">
			<p style="margin-top:0in"><span style="font-size:10.0pt;font-family:&quot;Arial&quot;,&quot;sans-serif&quot;;color:#524747">Dear '.$name.', <u></u><u></u></span></p>';
			if($template != "Wishes")	{
				$message .= '<p><span style="font-size:10.0pt;font-family:&quot;Arial&quot;,&quot;sans-serif&quot;;color:#524747">Welcome to WCC Family!<u></u><u></u></span></p>';
			}
		$message .= '</td>
	</tr>
	<tr>
		<td style="width:90.0%;border-top:none;border-left:solid #e2e2e2 1.0pt;border-bottom:none;border-right:solid #e2e2e2 1.0pt;background:white;padding:0in 30.6pt 0in 30.6pt" width="90%">
			<div align="center">
				<table style="width:100.0%;background:#fcfcfc;border:solid #ececec 1.0pt" border="1" cellpadding="0" cellspacing="0" width="100%">
					<tbody>
						<tr>
							<td style="width:30.0%;border:none;border-top:solid #f4f4f4 1.0pt;padding:7.5pt 11.25pt 7.5pt 11.25pt" width="30%">';
								
							if($template == "PaymentReceived")	{
								$message .= '<p class="MsoNormal"><span style="font-size:9.0pt;font-family:&quot;Arial&quot;,&quot;sans-serif&quot;;color:gray">We are happy to received your Payment of Rs. '.$amount.' on '.formatDate($date).' against '.$block.' and Plot No '.$plot.'. </span></p>';
								//echo $message; die;
							}else if($template == "NextDueDate")	{
								$message .= '<p class="MsoNormal"><span style="font-size:9.0pt;font-family:&quot;Arial&quot;,&quot;sans-serif&quot;;color:gray">Your Next Due Date is '.formatDate($date).' against '.$block.' and Plot No '.$plot.'. </span></p>';
							}else if($template == "Wishes")	{
								$message .= '<p class="MsoNormal"><span style="font-size:9.0pt;font-family:&quot;Arial&quot;,&quot;sans-serif&quot;;color:gray">Wishing you Happy '.$amount.' from Team WCC.</span></p>';
							}
							
							
							
				$message .= '</td>
						</tr>
					</tbody>
				</table>
			</div>
		</td>
	</tr>
	
	
	<tr>
		<td style="width:487.5pt;border-top:none;border-left:solid #e2e2e2 1.0pt;border-bottom:none;border-right:solid #e2e2e2 1.0pt;background:white;padding:22.5pt 30.6pt 22.5pt 30.6pt" width="650">
			<p><span style="font-size:10.0pt;font-family:&quot;Arial&quot;,&quot;sans-serif&quot;;color:#524747">Thanking you,<br><b>Team WCC</b><u></u><u></u></span></p>
		</td>
	</tr>
	<tr>
		<td style="width:487.5pt;border-top:#f3f3f3;border-left:#e2e2e2;border-bottom:#f3f3f3;border-right:#e2e2e2;border-style:solid;border-width:1.0pt;background:#fdfdfd;padding:0in 7.5pt 0in 7.5pt" width="650">
		</td>
	</tr>
</tbody>
</table>';
	//echo $message; die;
	// Always set content-type when sending HTML email
	$headers = "MIME-Version: 1.0" . "\r\n";
	$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
	
	// More headers
	$headers .= 'From: <info@wcc.in>' . "\r\n";
	//echo $message; die;
	//mail($to,'WCC Transaction',$message,$headers);
	return true;
}
	 ?>