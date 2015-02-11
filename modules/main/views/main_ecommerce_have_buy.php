<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<div class="col-sm-12 text-center">
	<hr>YOU ALSO LOVE<hr>
	<?php
    $i = 0;
    echo '<div class="col-md-12">';
    if(count($product_list) != 0){
        foreach($product_list as $row)
        {
            
            echo '<div class="row-fluid">';
            echo '<div class="col-md-3">';
            echo '<a href="'.base_url().'main/main_product/'.$row['product_id'].'" class="thumbnail"><img class="category-image" src="'.base_url('modules/main/assets/uploads/'.$row['image_url']).'" alt=""></a>';
            echo '</div>';
            echo '</div>';
            

            $i++;
        }   
    }
    else{
        echo '<p><i>You haven\'t buying anything yet.</i></p>';
    }
    echo '</div>';?>
</div>