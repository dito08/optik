<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<h3>PAYMENT CONFIRMATION</h3><hr>
<p>Please confirm your payment with fill this online form.<br/> Once confirmed, delvery will proceed within 24 hours.</p><br/>
<p>For more information, please contact our customer service</p>
<br><br>
<?php if($order_number == FALSE): ?>
	<p><strong>You have no pending order payment.</strong></p>
	
<?php else: ?>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url()?>modules/main/assets/styles/datepicker.css">
	<script type="text/javascript" src="<?php echo base_url()?>modules/main/assets/scripts/bootstrap-datepicker.js"></script>
	<?php
	    echo form_open('main/payment_confirmation', 'class="form form-horizontal form-horizontal-left"');
	    echo form_hidden('order_no', $order_number);
	    echo '<div class="form-group">';
	    echo form_label('Order Number', ' for="" class="control-label col-sm-3');
	    echo '<div class="col-sm-5"><b>'.$order_number.'</b>';
	    echo '</div>';
	    echo '</div>';

	    echo '<div class="form-group">';
	    echo form_label('Payment By', ' for="" class="control-label col-sm-3');
	    echo '<div class="col-sm-3">';
	    $options = array(
			'atm'   => 'ATM',
			'ebank' => 'E-Banking',
	    );
	    echo form_dropdown('payment_method', $options, '', 'class="form-control"');
	    echo '</div>';
	    echo '</div>';

	    echo '<div class="form-group">';
	    echo form_label('Transfer Date', ' for="" class="control-label col-sm-3');
	    echo '<div class="col-sm-2">';
	    echo form_input('transfer_date', '', 
	        'id="transfer_date" placeholder="" class="form-control datepicker"');
	    echo '</div>';
	    echo '</div>';

	    echo '<div class="form-group"><div class="col-sm-3">';
	    echo form_submit('confirm_payment', 'Send', 'class="btn btn-primary"');
	    echo '</div></div>';

	    echo form_close();
	?>
	<div class="text-danger">
	<?php echo validation_errors(); ?>
	</div>
	<script>
		$('.datepicker').datepicker({format:"dd/mm/yyyy"})
	</script>
<?php endif; ?>