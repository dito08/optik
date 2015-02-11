<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<link rel="stylesheet" href="//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css">
<script src="//code.jquery.com/jquery-1.10.2.js"></script>
<script src="//code.jquery.com/ui/1.10.4/jquery-ui.js"></script>
<h3>ADD NEW ADDRESS</h3><hr/>
<h4>CONTACT INFORMATION</h4>
<script type="text/javascript">
$(this).ready( function() {
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
        },
    });
});
</script>
<?php
    echo form_open('main/account_address', 'class="form form-horizontal"');

    echo '<div class="form-group">';
    echo form_label('First Name *', ' for="" class="control-label col-sm-2');
    echo '<div class="col-sm-8">';
    echo form_input('first_name', $first_name, 
        'id="first_name" placeholder="" class="form-control"');
    echo '</div>';
    echo '</div>';

    echo '<div class="form-group">';
    echo form_label('Last Name *', ' for="" class="control-label col-sm-2');
    echo '<div class="col-sm-8">';
    echo form_input('last_name', $last_name, 
        'id="last_name" placeholder="" class="form-control"');
    echo '</div>';
    echo '</div>';

    echo '<div class="form-group">';
    echo form_label('Company *', ' for="" class="control-label col-sm-2');
    echo '<div class="col-sm-8">';
    echo form_input('company', $company, 
        'id="company" placeholder="" class="form-control"');
    echo '</div>';
    echo '</div>';

    echo '<div class="form-group">';
    echo form_label('Handphone *', ' for="" class="control-label col-sm-2');
    echo '<div class="col-sm-8">';
    echo form_input('handphone', $handphone, 
        'id="handphone" placeholder="" class="form-control"');
    echo '</div>';
    echo '</div>';

    echo '<div class="form-group">';
    echo form_label('Telephone *', ' for="" class="control-label col-sm-2');
    echo '<div class="col-sm-8">';
    echo form_input('telephone', $telephone, 
        'id="telephone" placeholder="" class="form-control"');
    echo '</div>';
    echo '</div>';

    echo '<br/><h4>ADDRESS - <small><i>this will be your default delivery address</i></small></h4>';

    echo '<div class="form-group">';
    echo form_label('Street Address *', ' for="" class="control-label col-sm-2');
    echo '<div class="col-sm-8">';
    echo form_input('address', $address, 
        'id="address" placeholder="" class="form-control"');
    echo '</div>';
    echo '</div>';

    echo '<div class="form-group">';
    echo '<input type="hidden" name="city_code" id="city_code" value="'.$city_code.'" />';
    echo form_label('District, City *', ' for="" class="control-label col-sm-2');
    echo '<div class="col-sm-8">';
    echo form_input('city', $city, 
        'id="city" placeholder="" class="form-control"');
    echo '</div>';
    echo '</div>';

    echo '<div class="form-group">';
    echo form_label('State/Province *', ' for="" class="control-label col-sm-2');
    echo '<div class="col-sm-8">';
    echo form_input('state', $state, 
        'id="state" placeholder="" class="form-control"');
    echo '</div>';
    echo '</div>';

    echo '<div class="form-group">';
    echo form_label('Post Code *', ' for="" class="control-label col-sm-2');
    echo '<div class="col-sm-8">';
    echo form_input('postal_code', $postal_code, 
        'id="postal_code" placeholder="" class="form-control"');
    echo '</div>';
    echo '</div>';

    echo '<div class="form-group">';
    echo form_label('Country *', ' for="" class="control-label col-sm-2');
    echo '<div class="col-sm-5">';
    $options = array(
        'indonesia'  => 'Indonesia'
    );
    echo form_dropdown('country', $options, $country, 'class="form-control"');
    echo '</div>';
    echo '</div>';

    echo '<div class="form-group"><div class="col-sm-offset-2 col-sm-8">';
    echo form_submit('change_profile', 'Save Address', 'class="btn btn-default"');
    echo '</div></div>';
    echo '<div class="col-sm-offset-2 col-sm-8">* Required Fields</div>';

    echo form_close();
?>
<div class="text-danger">
<?php echo validation_errors(); ?>
</div>