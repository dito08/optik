<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<div class="row-fluid">
	<div class="col-md-4">
		<?php
		echo '<a class="product_img" title="'.$product[0]['product_name'].'" rel="gal1" href="'.base_url('modules/{{ module_path }}/assets/uploads/'.$product[0]['photos'][0]['url']).'">';
        echo '<img src="'.base_url('modules/{{ module_path }}/assets/uploads/thumb_'.$product[0]['photos'][0]['url']).'" title="'.$product[0]['product_name'].'"/>';
        echo '</a>';
        echo '<div class="clearfix"></div>';
        echo '<p>Hover or touch to zoom</p><hr/>';
        echo '<div class="clearfix"></div>';
		foreach($product[0]['photos'] as $photo){
	        echo '<ul id="thumblist" class="" >';
			echo '<li><a class="zoomThumb" href=\'javascript:void(0);\' rel="{gallery: \'gal1\', smallimage: \''.base_url('modules/{{ module_path }}/assets/uploads/thumb_'.$photo['url']).'\',largeimage: \''.base_url('modules/{{ module_path }}/assets/uploads/'.$photo['url']).'\'}"><img src="'.base_url('modules/{{ module_path }}/assets/uploads/thumb_'.$photo['url']).'"></a></li></ul>';
    	}
		?>
	</div>
	<div class="col-md-8">
		<div style="border-bottom: 2px solid #7A7A7A; padding-bottom: 30px;">
			<input type="hidden" id="product_id" value="<?php echo $product[0]['id'] ?>">
			<h3 class="text-upper"><?php echo $product[0]['product_name'] ?></h3>
			<p><b><?php echo $product[0]['brand'] ?></b> - 
			<b><?php echo $product[0]['type'] ?></b></p>
			<?php
				if($product[0]['old_price'] != ''){
					echo '<h4 style="text-decoration: line-through;">IDR '.number_format($product[0]['old_price'], 0, ',', '.').'</h4>';
				}
			?>
			<h4><b>IDR <?php echo number_format($product[0]['price'], 0, ',', '.') ?> 
				<?php if($product[0]['show_m2'] == 1)
					echo 'per m&sup2;';
				?>
				</b>
				<?php if ($product[0]['tax'] != '') {
					echo '<i>(price Include tax '.$product[0]['tax'].' %)</i>';
				}?>
			</h4>
			<?php
				if($product[0]['weight'] != ''){
					echo '<p><b>Weight:'.$product[0]['weight'].'Kg</b></p>';
				}
			?>
			<p><b>Availibility: <?php if($product[0]['availibility'] != 0){ echo 'In Stock';}else{echo 'Empty Stock';}?></b></p>
			<p><b>Select Size:</b>
				<select class="form-control" id="size" style="display:inline-block; width:200px;border-radius: 20px;">
					<option value="0">-Choose-</option>
					<?php
					foreach ($product[0]['size'] as $size) {
						echo '<option value='.$size['size_id'].'>'.$size['size_txt'].'</option>';
					}
					?>
				</select>
			</p>
			<?php if($product[0]['availibility'] != 0 ): ?>
			<div><b>Qty: </b>
				<div class="input-group spinner" style="display:inline-flex; margin-left:10px;margin-bottom:30px;">
					<input type="text" id="qty" class="form-control" value="1" maxlength="2" style="height:30px">
					<div class="input-group-btn-vertical">
					  <button class="btn btn-default up-caret"><span class="caret"></span></button>
					  <button class="btn btn-default"><span class="caret"></span></button>
					</div>
				</div>
		  	</div>
		  	<?php endif; ?>
		  	<div>
		  		<button type="button" class="btn btn-default" onclick="add_wishlist(<?php echo $product[0]['id'] ?>)">ADD TO WISHLIST</button>
		  		<?php if($product[0]['availibility'] != 0 ): ?>
		  			<button type="button" class="btn btn-primary" onclick="add_cart()">ADD TO TROLLEY</button>
		  		<?php endif; ?>
		  	</div>
		  	<div id="message" class="alert alert-danger" style="margin-top:10px"></div>
		</div>
		<h4>ADDITIONAL PRODUCT INFORMATION</h4><hr/>
		<pre><?php echo $product[0]['info'] ?></pre>
	</div>
</div>
<script type="text/javascript">

$(document).ready(function() {
	$('.product_img').jqzoom({
        zoomType: 'standard',
        lens:true,
        preloadImages: false,
        alwaysOn:false
    });
	
});
</script>
<script>

(function ($) {
	$('#message').hide();
	$('.spinner .btn:first-of-type').on('click', function() {
		$('.spinner input').val( parseInt($('.spinner input').val(), 10) + 1);
	});
		$('.spinner .btn:last-of-type').on('click', function() {
		$('.spinner input').val( parseInt($('.spinner input').val(), 10) - 1);
	});
})(jQuery);
function add_wishlist(product_id)
{
	var qty = $('#qty').val();
	$.ajax({
        type:'POST',
        url: "<?php echo base_url()?>main/product_add_wishlist",
        data :{product_id:product_id,qty:qty},
        dataType: 'json',
        success:
        function(msg){
        	$('#message').show();
            $('#message').html(msg.message);
        }
    }); 
}

function add_cart()
{
	var product_id = $('#product_id').val();
	var qty = $('#qty').val();
	var size = $('#size').val();

	if(size == '0'){
		$('#message').html("Please Select Size");
		$('#message').show();
	}
	else if(qty <= '0'){
		$('#message').html("Minimum Qty Order is 1");
		$('#message').show();
	}else{
		$.ajax({
	        type:'POST',
	        url: "<?php echo base_url()?>main/product_add_cart",
	        data :{product_id:product_id,qty:qty,size:size},
	        success:
	        function(msg){
	        	if(msg == "ok"){
	        		window.location.replace("<?php echo base_url()?>main/cart");
	        	}
	        }
	    });
	}
}
</script>