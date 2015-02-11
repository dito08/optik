<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<style type="text/css">
    #message:empty{
        display:none;
    }
    #btn-register, .register_input{
        display:none;
    }
</style>
<script type="text/javascript">
	var REQUEST_EXISTS = false;
	var REQUEST = "";
    function check_user_exists(){
        //var user_name =  $('input[name="<?=$secret_code?>user_name"]').val();
        var email = $('input[name="<?=$secret_code?>email"]').val();
        var first_name = $('input[name="<?=$secret_code?>first_name"]').val();
        var last_name = $('input[name="<?=$secret_code?>last_name"]').val();
        var password = $('input[name="<?=$secret_code?>password"]').val();        
        var confirm_password = $('input[name="<?=$secret_code?>confirm_password"]').val();
        $("#img_ajax_loader").show();
        if(REQUEST_EXISTS){
        	REQUEST.abort();
        }
        REQUEST_EXISTS = true;
        REQUEST = $.ajax({
            "url" : "check_registration",
            "type" : "POST",
            "data" : {"email":email},
            "dataType" : "json",
            "success" : function(data){
            	if(!data.error && !data.exists && first_name!='' && last_name!='' && password!='' && password==confirm_password){
                    $('input[name="register"]').show();
                    $('input[name="register"]').removeAttr('disabled');
                    console.log($('input[name="register"]'));
                }else{
                    $('input[name="register"]').hide();
                    $('input[name="register"]').attr('disabled', 'disabled');
                }

            	// get message from server + local check
                var message = '';
                if(first_name == ''){
                    message += 'First Name Cannot Empty<br />';
                }
                if(last_name == ''){
                    message += 'Last Name Cannot Empty<br />';
                }
                if(data.message!=''){
                    message += data.message+'<br />';
                }
                if(password == ''){
                    message += '{{ language:Password is empty }}<br />';
                }
                if(password != confirm_password){
                    message += '{{ language:Confirm password doesn\'t match }}';
                }

                if(message != $('#message').html()){
                    $('#message').html(message);
                }
                REQUEST_EXISTS = false;
                $("#img_ajax_loader").hide();
            },
            error: function(xhr, textStatus, errorThrown){
                if(textStatus != 'abort'){
                    setTimeout(check_user_exists, 10000);    
                }
            }
        });
    }

    $(document).ready(function(){
        check_user_exists();
        $('input').keyup(function(){
            check_user_exists();
        });
    })
</script>
<h4>Personal Information</h4>
<hr/>
<?php
    echo form_open('main/register', 'class="form form-horizontal form-register"');
    echo form_input(array('name'=>'first_name', 'value'=>'', 'class'=>'register_input'));
    echo form_input(array('name'=>'last_name', 'value'=>'', 'class'=>'register_input'));
    echo form_input(array('name'=>'user_name', 'value'=>'', 'class'=>'register_input'));
    echo form_input(array('name'=>'email', 'value'=>'', 'class'=>'register_input'));  
    echo form_input(array('name'=>'password', 'value'=>'', 'class'=>'register_input'));
    echo form_input(array('name'=>'confirm_password', 'value'=>'', 'class'=>'register_input'));

    echo form_label('First Name *', ' for="" class="');
    echo form_input($secret_code.'first_name', $first_name, 
        'id="'.$secret_code.'first_name" class="form-control"');

    echo form_label('Last Name *', ' for="" class="');
    echo form_input($secret_code.'last_name', $last_name, 
        'id="'.$secret_code.'last_name" class="form-control"');

    echo form_label('{{ language:Email }} *', ' for=""');
    echo form_input($secret_code.'email', $email, 
        'id="'.$secret_code.'email" class="form-control"');   
    echo '<br/><h4>Login Information</h4><hr/>';
    echo form_label('{{ language:Password }} * ', ' for="" class=""');
    echo form_password($secret_code.'password', '', 
        'id="'.$secret_code.'password" class="form-control"');

    echo form_label('{{ language:Confirm Password }}', ' for="" class="');
    echo form_password($secret_code.'confirm_password', '', 
        'id="'.$secret_code.'confirm_password" class="form-control"');

    echo '<img id="img_ajax_loader" style="display:none;" src="'.base_url('assets/nocms/images/ajax-loader.gif').'" /><br />';
    echo '<div id="message" class="alert alert-danger"></div>';
    echo form_submit('register', 'Submit', 'id="btn-register" class="btn btn-default" style="display:none;"');
    echo form_close();
?>
<p>* Required Fields</p>
<p><b>Please Note: </b> after you have registered we will send you an email containing an activation link. Please check your e-mail and check on this activation link to activate your account</p>
