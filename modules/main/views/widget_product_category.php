<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php 
$div_item_list = array();

for($i=0; $i<count($categories); $i++){
    $item = $categories[$i];

    if($current == $item['id']){
    	$div_item_list[] = 
	 		'<li>'.
	        '<a href="'.base_url().'main/shop_product/'.$item['id'].'"><span class="glyphicon glyphicon-play"></span> '.$item['name'].'</a>'.
	        '</li>';
    }else{
    	$div_item_list[] = 
	 		'<li>'.
	        '<a href="'.base_url().'main/shop_product/'.$item['id'].'">'.$item['name'].'</a>'.
	        '</li>';
    }

    
}
?>
<ul class="nav nav-pills nav-stacked" style="max-width: 300px;">
	<li><a href="<?php echo base_url();?>main/shop_product/all"><?php echo ($current == 'all') ? '<span class="glyphicon glyphicon-play"></span>' : ''; ?> ALL PRODUCTS</a></li>
    <?php 
    	if(count($categories)>0){
    		foreach($div_item_list as $div_item)
	    	{ 
	    		echo $div_item;
	    	} 
    	}
    	else{
    		echo "No Available Product";
    	}
    ?>
</ul>