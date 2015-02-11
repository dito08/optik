<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<h3>PAYMENT TERMS</h3><hr>
<p>Select Your order will be automatically cancelled from our system if you do not make the payment within 24 hours. If you encounter difficulties, please contact us.</p>
<?php
    echo form_open('main/payment_terms', 'class="form form-horizontal"');
    echo form_hidden('address', $address);
    echo form_hidden('city_code', $city_code);
    echo '<div class="form-group">';
    echo form_label('Choose Your Bank', ' for="" class="control-label col-sm-4');
    echo '<div class="col-sm-5">';
    $options = array(
        'bca'  		=> 'BCA',
        'mandiri'   => 'Mandiri',
    );
    echo form_dropdown('bank', $options, '', 'class="form-control"');
    echo '</div>';
    echo '</div>';

    echo '<div class="form-group">';
    echo form_label('Account Number', ' for="" class="control-label col-sm-4');
    echo '<div class="col-sm-8">';
    echo form_input('account_number', '', 
        'id="account_number" placeholder="" class="form-control"');
    echo '</div>';
    echo '</div>';

    echo '<div class="form-group">';
    echo form_label('Account Name', ' for="" class="control-label col-sm-4');
    echo '<div class="col-sm-8">';
    echo form_input('account_name', '', 
        'id="account_name" placeholder="" class="form-control"');
    echo '</div>';
    echo '</div>';

    echo '<p>Your confirmation order will be sent via email to <b>'. $email.'</b></p>';

    echo '<div class="form-group"><div class="col-sm-offset-2 col-sm-10">';
    echo form_submit('confirm_payment', 'Confirm', 'class="btn btn-primary pull-right"');
    echo '</div></div>';

    echo form_close();
?>
<div class="text-danger">
<?php echo validation_errors(); ?>
</div>