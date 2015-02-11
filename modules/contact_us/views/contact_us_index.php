<style type="text/css">
    textarea[name="<?php echo $secret_code; ?>content"]{
        resize:none;
    }
</style>
<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<hr/>
<h3>INQUIRY</h3><hr>
<div class="col-md-9">
<p>If you have any inquiries, comments or suggestions, feel free to submit them via this form and our customer service team will contact you as soon as we can.</p>
<p>You can also use this form if you want a custom rug produced for you.<br/>Please leave your contact number so we may contact you.</p>
<br>
<?php 
    if(!$success){
        echo '<div class="alert alert-danger">'.$error_message.'</div>';
    }else if($show_success_message){
        echo '<div class="alert alert-success">Your message has been sent</div>';
    }
    
    echo form_open('', 'class="form form-horizontal form-horizontal-left"');

    echo '<div class="form-group">';
    echo form_label('Name', ' for="" class="control-label col-sm-2');
    echo '<div class="col-sm-9">';
    echo form_input($secret_code.'name', $name, 
        'id="'.$secret_code.'name" placeholder="Your Name" class="form-control"');
    echo '</div>';
    echo '</div>';

    echo '<div class="form-group">';
    echo form_label('Email', ' for="" class="control-label col-sm-2');
    echo '<div class="col-sm-9">';
    echo form_input($secret_code.'email', $email, 
        'id="'.$secret_code.'email" placeholder="Your Email" class="form-control"');
    echo '</div>';
    echo '</div>';

    echo '<div class="form-group">';
    echo form_label('Phone', ' for="" class="control-label col-sm-2');
    echo '<div class="col-sm-9">';
    echo form_input($secret_code.'phone', $phone, 
        'id="'.$secret_code.'phone" placeholder="Your Phone Number" class="form-control"');
    echo '</div>';
    echo '</div>';

    echo '<div class="form-group">';
    echo form_label('Subject', ' for="" class="control-label col-sm-2');
    echo '<div class="col-sm-9">';
    echo form_input($secret_code.'subject', $subject, 
        'id="'.$secret_code.'subject" placeholder="Message Subject" class="form-control"');
    echo '</div>';
    echo '</div>';

    echo '<div class="form-group">';
    echo form_label('Comment', ' for="" class="control-label col-sm-2');
    echo '<div class="col-sm-9">';
    echo form_textarea($secret_code.'content', $content, 
        'id="'.$secret_code.'content" placeholder="Your Message" class="form-control"');
    echo '</div>';
    echo '</div>';

    echo '<div class="form-group"><div class="col-sm-11 text-right">';
    echo form_submit('send', 'Submit', 'class="btn btn-default"');
    echo '</div></div>';
?>
</div>
<script type="text/javascript" src="{{ base_url }}assets/nocms/js/jquery.autosize.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
        $('textarea[name="<?php echo $secret_code; ?>content"]').autosize();
    });
</script>