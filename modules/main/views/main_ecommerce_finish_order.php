<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<div class="col-md-6">
	<h3>THANK YOU FOR BUYING!</h3><hr>
	<p>Your order will immediately be processed and your confirmation order have been sent to your email, <strong><?php echo $user_email ?></strong></p><br/>
	<p>Order Number: <?php echo $order_number ?>
	<br/>Quantity Order: <?php echo $qty_order ?>
</div>
<div class="col-md-6">
	<h3>SHARE YOUR PURCHASE WITH FRIENDS!</h3><hr>
	<?php
		$link	= base_url().'main/main_product/'.$product_id;
		$text	= 'I just bought a shiny new carpet - '.$product_name.' by '.$brand.' at www.GoldenDragonCarpets.com';
		$image	= base_url().'modules/main/assets/uploads/'.$url;
	?>
	<div class="col-sm-12">
		<div class="row-fluid">
			<div class="col-sm-4">
				<img src="<?php echo base_url()?>modules/main/assets/uploads/<?php echo $url?>" alt="" class="img-responsive">
			</div>
			<div class="col-sm-8">
				<p>
					<?php echo $text ?>
				</p>
				<p>Share with: </p>
				<div class="row-fluid footer-social">
                    <div class="col-sm-3">
                        <a href="https://www.facebook.com/dialog/share?app_id=1378288949127495&display=popup&href=<?php echo $link;?>&redirect_uri=<?echo $link?>" target="blank"><img class="img-responsive" src="{{ base_url }}/themes/neutral/assets/default/images/fb.png" alt=""></a>
                    </div>
                    <div class="col-sm-3">
                        <a href="<?=share_url('twitter', array('url'=>$link, 'text'=>$text, 'via'=>'goldenDragon'))?>" target="blank"><img class="img-responsive" src="{{ base_url }}/themes/neutral/assets/default/images/twitter.png" alt=""></a>
                    </div>
                    <div class="col-sm-3">
                        <a href="http://www.pinterest.com/pin/create/button/?url=<?php echo $link;?>&media=<?php echo $image;?>&description=<?php echo $text?>"data-pin-do="buttonPin"data-pin-config="above" target="blank"><img class="img-responsive" src="{{ base_url }}/themes/neutral/assets/default/images/pinterests.png" alt=""></a>
                    </div>
                </div>
			</div>
		</div>
	</div>
</div>
<div class="col-md-12">
	<br/><br/><h3>ORDER PAYMENT AND CONFIRMATION</h3><hr>
	<p>Dear <?php echo $full_name ?>,</p>
	<p>Thank you for shopping at Golden Dragon.</p>
	<p>Please transfer your payment within 24 hours, otherwise your order may be canceled.</p>
	<br>
</div>
<div class="col-md-12">
	<div class="row-fluid payment-step">
		<div class="col-md-3 well">
			<h4>ORDER NUMBER</h4>
			Please save it for your references<br/><br/>
			<h3 class="text-center"><?php echo $order_number ?></h3>
		</div>
		<div class="col-md-1 text-center arrow">
			<img class="img-responsive" src="{{ base_url }}modules/main/assets/images/arrow.png" alt="">
		</div>
		<div class="col-md-3 well">
			<h5><strong>TRANSFER YOUR PAYMENT</strong></h5>
			<font style="font-size:20px">IDR <?php echo number_format($grand_total) ?></font>
			<p>to Golden Dragon Account</p>
			<?php if($bank == 'bca'):?>
				<img class="img-responsive" src="{{ base_url }}/themes/neutral/assets/default/images/bca.jpg" alt="" style="margin-top: 15px; width:65%">
				<div style="font-size:12px;"><br/><p>Bank: BCA
				<br/>Account Number: 069.223.60023
				<br/>Bank Office: Cab.Muara Karang
				<br/>A/N: PT. FLAMINDO CARPETAMA</p></div>
			<?php else:?>
				<img class="img-responsive" src="{{ base_url }}/themes/neutral/assets/default/images/mandiri.jpg" alt="" style="margin-top: 15px; width:65%">
				<div style="font-size:12px;"><br/><p>Bank: Mandiri
				<br/>Account Number: 165.0000.323213
				<br/>Bank Office: Cab.Jl.Panjang
				<br/>A/N: PT. FLAMINDO CARPETAMA</p></div>
			<?php endif;?>
		</div>
		<div class="col-md-1 text-center arrow">
			<img class="img-responsive" src="{{ base_url }}modules/main/assets/images/arrow.png" alt="">
		</div>
		<div class="col-md-3 well">
			<h4 class="text-center">CONFIRM PAYMENT AND RECEIVE ITEM(S)</h4>
			<div>
				<img class="img-responsive" src="{{ base_url }}modules/main/assets/images/box.png" alt="" style="margin-left:28px">
			</div>
		</div>
	</div>
</div>
<div class="clearfix"></div>
<p>Once you have made your payment, you MUST confirm your payment by clicking the button below.</p>
<a href="<?php echo base_url().'main/payment_confirmation/'.$order_number ?>" class="btn btn-primary">Payment Confirmation</a>