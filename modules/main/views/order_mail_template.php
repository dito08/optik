<html>
<head>
	<style type="text/css">
	</style>
</head>
<body>
	<table width="100%">
		<tr>
			<td><img src ="http://carpetindonesia.com/assets/nocms/images/custom_logo/logo.png" style="max-width: 130px;" /></td>
			<td style="text-align:right"><h2>Customer Service</h2><h3>+62 21 5367 8290</h3></td>
		</tr>
		<tr>
			<td colspan="2">
				<hr>
				<h3>ORDER CONFIRMATION</h3>
				<hr>
				<p>Dear <?php echo $user ?>,</p>
				<br>
				<p>
				Thank you for shopping at Golden Dragon.<br/>
				Please Transfer your payment within 24 hours, otherwise your order may be canceled.
				</p><br><br>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<table width="100%">
					<tr>
						<td>
							<div class="well" style="background-color: #CACACA;padding: 5px;width: 300px;text-align: center;height: 325px;border-radius: 6px;">
								<h4>ORDER NUMBER</h4>
								Please save it for your references<br/><br/>
								<h1 style="text-align:center;"><?php echo $order_no ?></h1>
							</div>
						</td>
						<td>
							<img src="http://carpetindonesia.com/modules/main/assets/images/arrow.png" alt="">
						</td>
						<td>
							<div class="well" style="background-color: #CACACA;padding: 5px;width: 300px;text-align: center;height: 325px;border-radius: 6px;">
								<h5><strong>TRANSFER YOUR PAYMENT</strong></h5>
								<font style="font-size:20px">IDR <?php echo number_format($grand_total) ?></font>
								<p>to Golden Dragon Account</p>
								<?php if($bank == 'bca'):?>
									<img src="http://carpetindonesia.com/themes/neutral/assets/default/images/bca.jpg" alt="" style="margin-top: 15px; width:65%">
									<div style="font-size:12px;"><br/><p>Bank: BCA
									<br/>Account Number: 069.223.60023
									<br/>Bank Office: Cab.Muara Karang
									<br/>A/N: PT. FLAMINDO CARPETAMA</p></div>
								<?php else:?>
									<img src="http://carpetindonesia.com/themes/neutral/assets/default/images/Mandiri.jpg" alt="" style="margin-top: 15px; width:65%">
									<div style="font-size:12px;"><br/><p>Bank: Mandiri
									<br/>Account Number: 165.0000.323213
									<br/>Bank Office: Cab.Jl.Panjang
									<br/>A/N: FLAMINDO CARPETAMA</p></div>
								<?php endif;?>
							</div>
						</td>
						<td>
							<img src="http://carpetindonesia.com/modules/main/assets/images/arrow.png" alt="">
						</td>
						<td>
							<div class="well" style="background-color: #CACACA;padding: 5px;width: 300px;text-align: center;height: 325px;border-radius: 6px;">
								<h4 style="text-align:center;">CONFIRM PAYMENT AND RECEIVE ITEM(S)</h4>
								<div>
									<img src="http://carpetindonesia.com/modules/main/assets/images/box.png" alt="" style="margin-left:28px">
								</div>
							</div>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<p>Once you have made your payment, you MUST confirm your payment by clicking
				<a href="<?php $site_url?>payment_confirmation" class="btn btn-primary"> Payment Confirmation</a></p>
				<br>
			</td>
		</tr>
		<tr>
			<td colspan="2" style="text-align:center;">
				<h4 style="text-align:center;">FOLLOW US</h4>
	            <div class="row-fluid footer-social">
                    <img class="social" src="http://carpetindonesia.com/themes/neutral/assets/default/images/fb.png" alt="" width="50px">
                    <img class="social" src="http://carpetindonesia.com/themes/neutral/assets/default/images/twitter.png" alt="" width="50px">
                    <img class="social" src="http://carpetindonesia.com/themes/neutral/assets/default/images/pinterests.png" alt="" width="50px">
                    <img class="social" src="http://carpetindonesia.com/themes/neutral/assets/default/images/instagram.png" alt="" width="50px">
	            </div>
	            <br>
	            @2014 Golden Dragon Indonesia <br> PT.FLAMBOO GOLDEN DRAGON <br> Jl. Panjang No. 5E, Jakarta 11530
			</td>
		</tr>
	</table>
</body>
</html>