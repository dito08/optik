<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
$div_item_list = array();


for($i=0; $i<count($product_list); $i++){
    $item = $product_list[$i];

    $div_item_list[] = 
            '<div class="col-sm-4 product">'.
            '<div class="row-fluid">'.
            '<div class="col-sm-12 fc-pic product-pic">'.
            '<a href="'.base_url().'main/main_product/'.$item['id'].'" class="thumbnail"><img class="category-image" src="'.base_url('modules/main/assets/uploads/'.$item['photos']).'" alt=""></a>'.
            '</div>'.
            '<div class="col-sm-12 text-center text-upper">'.$item['product_name'].'</div>'.
            '<div class="col-sm-12 text-center">'.$item['brand'].'</div>'.
            '<div class="col-sm-12 text-center">'.$item['type'].'</div>'.
            '</div></div>';
}
?>
        
<div class="row-fluid">
    <?php 
    	if(count($product_list)>0){
    		foreach($div_item_list as $div_item)
	    	{ 
	    		echo $div_item;
	    	} 
    	}
    	else{
    		echo "No Available Product";
    	}
    ?>
</div>