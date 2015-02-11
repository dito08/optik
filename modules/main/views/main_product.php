<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
$div_item_list = array();
for($i=0; $i<count($category_list); $i++){
    $category = $category_list[$i];

    $div_item_list[] = 
            '<div class="col-sm-3">'.
            '<div class="row-fluid front-category">'.
            '<div class="col-sm-12 fc-head">'.$category['title'].'</div>'.
            '<div class="col-sm-12 fc-pic">'.
            '<a href="#" class="thumbnail"><img class="category-image" src="'.base_url('modules/'.$module_path.'/assets/images/category/'.$category['image_url']).'" alt=""></a>'.
            '</div></div></div>';
}
?>
        
<div class="row-fluid">
    <?php foreach($div_item_list as $div_item){ echo $div_item;} ?>
</div>