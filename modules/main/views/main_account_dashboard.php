<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<h3>MY DASHBOARD</h3><hr/>
<h3>Hello, <?php echo $first_name .' '.$last_name ?></h3>
<p>From your My Account Dashboard you have the ability to view a snapshot of your recent account activity and update your account information. Select a link below to view or edit information.</p>
<br/><hr/>
<h3>ACCOUNT INFORMATION</h3><hr/>
<div class="row-fluid dashboard-detail">
	<div class="col-md-6">
		<div class="well">
			<h4>Contact Information | <a href="<?php echo base_url()?>main/change_profile">Edit</a></h4>
			<?php echo $first_name .' '.$last_name ?><br/>
			<?php echo $email ?><br/>
			<a href="<?php echo base_url()?>main/change_profile">Change Password</a>
		</div>
	</div>
	<div class="col-md-6">
		<div class="well">
			<h4>Address Book | Manage Address</h4>
			<strong>Default Shipping Address |</strong> <a href="<?php echo base_url()?>main/account_address">Edit Address</a><br/>
			<?php if(!$address_street == ''): ?>
				<?php echo $address_first_name?> <?php echo $address_last_name?> <br/>
				<?php echo $address_street?> , <?php echo $address_city?><br/>
				<?php echo $address_state?> <?php echo $address_postal?><br/>
				<?php echo $address_country?><br/>

			<?php else: ?>
				<p>You have not set a default shipping address</p>
			<?php endif;?>
		</div>
	</div>
</div>