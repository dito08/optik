<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<style type="text/css">
    #login_message:empty{
        display:none;
    }
</style>
<h4>Sign In Customers</h4>
<hr/>
<p>If you have an account with us please log in using your email address</p>
<?php
    echo form_open('main/login');
    echo form_label('Email Address *');
    echo form_input('identity', $identity, 'placeholder="" class="form-control"').br();
    echo form_label('{{ language:Password }} *');
    echo form_password('password','','placeholder="" class="form-control"').br();
    echo '<p>* Required Fields</p>';
    echo form_submit('login', $login_caption, 'class="btn btn-primary"');
    echo form_close();
?>
<div id="login_message" class="alert alert-danger"><?php echo isset($message)?$message:''; ?></div>
<br/>
<a href="{{ site_url }}main/forgot"><strong>Forgotton Your Password?</strong></a>
<hr>
