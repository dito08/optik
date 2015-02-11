<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<div id="shipping_section">
<link rel="stylesheet" href="//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css">
<script src="//code.jquery.com/jquery-1.10.2.js"></script>
<script src="//code.jquery.com/ui/1.10.4/jquery-ui.js"></script>

<script type="text/javascript">
	var REQUEST_EXISTS = false;
	var REQUEST = "";

    function toggle_new_address(){
        if($('input[name="optionsRadios"]').prop('checked')){
            $('#different_address').hide();
            $('#exist_address').attr('disabled',false);
            $('#next_address').show();
        }else{
            $('#different_address').show();
            $('#exist_address').attr('disabled',true);
            $('#next_address').hide();
        }
    }

    $(document).ready(function(){
    	$('#message').hide();
        toggle_new_address();
        $('input[name="optionsRadios"]').change(function(){toggle_new_address();});
        $("#city").autocomplete({
	        minLength: 1,
	        source:
	        function(req, add){
	            $.ajax({
	                url: "<?php echo base_url(); ?>index.php/main/lookup",
	                dataType: 'json',
	                type: 'POST',
	                data: req,
	                success:
	                function(data){
	                    if(data.response =="true"){
	                        add(data.message);
	                    }
	                },
	            });
	        },
	    	select:
		        function(event, ui) {
		           $("#city_code").val(ui.item.id);
		           // $("#city_code").val(ui.item.id);
		           // document.getElementById('shipping_fee').innerHTML = ui.item.harga;
		           // var total_price = parseInt(<?php echo $this->cart->total() ?>);
		           // var total = parseInt(ui.item.harga)+parseInt(total_price);
		           // document.getElementById('grand_total').innerHTML = total;
		        },
	    });
    })
</script>
<h3>SHIPPING INFORMATION</h3><hr>
<p>Select a shipping address from your address boook or enter a new address</p>
<div class="radio">
  <label>
    <input type="radio" name="optionsRadios" id="optionsRadios1" value="option1" checked>
    Ship to this Address
  </label>
</div>
<input type="hidden" id="city_code_default" value="<?php echo $city_code ?>">
<input type="text" id="exist_address" class="form-control" value="<?php echo $exist_address ?>">
<div class="radio">
  <label>
    <input type="radio" name="optionsRadios" id="optionsRadios1" value="option1">
    Ship to different Address
  </label>
</div>
<div id="different_address">
	<?php
	    echo form_open('main/payment_terms', 'class="form form-horizontal"');

	    echo '<div class="form-group">';
	    echo form_label('Name *', ' for="" class="control-label col-sm-3');
	    echo '<div class="col-sm-8">';
	    echo form_input('full_name', '', 
	        'id="full_name" placeholder="" class="form-control"');
	    echo '</div>';
	    echo '</div>';

	    echo '<div class="form-group">';
	    echo form_label('Phone *', ' for="" class="control-label col-sm-3');
	    echo '<div class="col-sm-8">';
	    echo form_input('handphone', '', 
	        'id="handphone" placeholder="" class="form-control"');
	    echo '</div>';
	    echo '</div>';

	    echo '<div class="form-group">';
	    echo form_label('Shipping Address *', ' for="" class="control-label col-sm-3');
	    echo '<div class="col-sm-8">';
	    echo form_input('address', '', 
	        'id="address" placeholder="" class="form-control"');
	    echo '</div>';
	    echo '</div>';

	    echo '<div class="form-group">';
	    echo form_label('Postal Code *', ' for="" class="control-label col-sm-3');
	    echo '<div class="col-sm-8">';
	    echo form_input('postal_code', '', 
	        'id="postal_code" placeholder="" class="form-control"');
	    echo '</div>';
	    echo '</div>';

	    echo '<div class="form-group">';
	    echo '<input type="hidden" name="city_code" id="city_code" value="'.$city_code.'" />';
	    echo form_label('City *', ' for="" class="control-label col-sm-3');
	    echo '<div class="col-sm-8">';
	    echo form_input('city', '', 
	        'id="city" placeholder="" class="form-control"');
	    echo '</div>';
	    echo '</div>';

	    echo '<div class="form-group">';
	    echo form_label('Country *', ' for="" class="control-label col-sm-3');
	    echo '<div class="col-sm-5">';
	    $options = array(
	        'indonesia'  => 'Indonesia',
	        'malaysia'    => 'Malaysia',
	        'singapore'   => 'Singapore',
	        'thailand'    => 'Thailand',
	    );
	    echo form_dropdown('country', $options, '', 'class="form-control" id="country"');
	    echo '</div>';
	    echo '</div>';

		echo '<div class="form-group"><div class="col-sm-offset-2 col-sm-8">';
    	echo '<button type="button" class="btn btn-primary" onclick="new_step_payment()">Next</button>';
    	echo '</div></div>';
    	echo '<div class="col-sm-offset-2 col-sm-8">* Required Fields</div>';

	    echo form_close();
	?>
</div>

<div class="pull-right" id="next_address">
	<button type="button" class="btn btn-primary" onclick="step_payment()">Next</button>
</div>
</div>
<div id="hasil"></div>
<div class="clearfix"></div>
<div id="message" class="alert alert-danger" style="margin-top:10px"></div>

<script>
function step_payment(){
	var address = $('#exist_address').val();
	var city_code = $('#city_code_default').val();

	if(!address){
		$('#message').show();
        $('#message').html("Please fill your full shipping Address");
	}else{
		$.ajax({
	        type:'POST',
	        url: "<?php echo base_url()?>main/payment_terms",
	        data :{address:address,city_code:city_code},
	        success:
	            function(msg)
	            {
	            	$('#shipping_section').hide();
	            	$('#hasil').html(msg);
	            }
	    }); 
	}
}
function new_step_payment(){
	var name = $('#full_name').val();
	var phone = $('#handphone').val();
	var new_address = $('#address').val();
	var postal = $('#postal_code').val();
	var city = $('#city').val();
	var city_code = $('#city_code').val();
	var country = $('#country').val();

	if(!name){
		$('#message').show();
        $('#message').html("Please fill your full name");
	}else if(!phone){
		$('#message').show();
        $('#message').html("Please fill your phone number");
	}else if(!new_address){
		$('#message').show();
        $('#message').html("Please fill your new shipping address");
	}else if(!postal){
		$('#message').show();
        $('#message').html("Please fill your postal code");
	}else if(!city){
		$('#message').show();
        $('#message').html("Please fill your city name");
	}else if(!country){
		$('#message').show();
        $('#message').html("Please fill your country name");
	}else{
		var address = name+', '+new_address+' '+country+', '+city+' '+postal;
		if(!address){
			$('#message').show();
	        $('#message').html("Please fill your full shipping Address");
		}else{
			$.ajax({
		        type:'POST',
		        url: "<?php echo base_url()?>main/payment_terms",
		        data :{address:address,city_code:city_code},
		        success:
	            function(msg)
	            {
	            	$('#shipping_section').hide();
	            	$('#hasil').html(msg);
	            }
		    }); 
		}
	}
}
</script>