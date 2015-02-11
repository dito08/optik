<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
$div_item_list = array();
for($i=0; $i<count($gallery_list); $i++){
    $item = $gallery_list[$i];

    $div_item_list[] = 
            '<div class="view view-ninth">'.
            '<a href="#" onclick="openDetail('.$item['id'].')"><img class="category-image img-responsive" src="'.base_url('modules/main/assets/uploads/gallery/'.$item['photos']).'" alt="" style="width:100%;">'.
            '<div class="mask mask-1"></div>'.
            '<div class="mask mask-2"></div>'.
            '<div class="content">'.
            '<h2>'.$item['product_name'].'</h2>'.
            '<p>'.$item['place'].'<br/>'.
            $item['brand'].'</p>'.
            '</div></a></div>';
}
?>
        
<div class="row-fluid">
    <?php 
    	if(count($gallery_list)>0){
    		foreach($div_item_list as $div_item)
	    	{ 
	    		echo $div_item;
	    	} 
    	}
    	else{
    		echo "No Available Item in Gallery";
    	}
    ?>
</div>

<!-- Modal -->
<div class="modal fade" id="orderModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header" style="border-bottom:0;">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        </div>
        <div class="modal-body">
            <div id="pop-image"></div>
        </div>
        <div class="modal-footer">
            <div class="pull-left text-left">
                <div id="pop-place"></div>
                <div id="pop-name" style="text-transform:uppercase"></div>
                <div id="pop-brand"></div>
            </div>
            <div id="pop-direct"></div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
//'.base_url().'main/main_product/'.$item['id'].'
function openDetail(id){
    $.ajax({
        type:'POST',
        url: '<?php echo base_url()?>main/get_info_gallery',
        data :{id:id},
        dataType: 'json',
        success:
        function(msg){
            $('#pop-place').html(msg.place);
            $('#pop-brand').html(msg.brand);
            $('#pop-name').html(msg.name);
            $('#pop-direct').html('<a href="<?php echo base_url()?>main/main_product/'+msg.id+'" class="btn btn-default">MORE DETAILS</a>');
            $('#pop-image').html('<img class="img-responsive" src="<?php echo base_url() ?>modules/main/assets/uploads/gallery/'+msg.image_url+'" alt="" style="width:100%;">');
        }
    }); 

    $('#orderModal').modal('show');
}
</script>