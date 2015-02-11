<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<h3>MY WISHLIST</h3><hr/>
<?php if(count($wishlist) > 0):?>
  <table class="table table-hover">
    <thead>
      <td>NO</td><td>PRODUCT</td><td>DESCRIPTION</td><td>QTY</td><td colspan="2">UNIT PRICE</td>
    </thead>
    <tbody>
    <?php $i=1;
      foreach($wishlist as $row)
      {
        echo "<tr>
          <input type='hidden' id='product_id_".$i."' value='".$row['product_id']."'>
          <input type='hidden' id='qty".$i."' value='".$row['qty']."'>
          <input type='hidden' id='wishlist_id_".$i."' value='".$row['wish_id']."'>
          <td>$i</td>
          <td>
            <table>
                <tr>
                    <td>
                        <a href='".base_url()."main/main_product/".$row['product_id']."'><img src='".base_url('modules/main/assets/uploads/'.$row['image_url'])."' alt='' width='70px' style='margin:10px'></a>
                    </td>
                    <td>
                        <a href='".base_url()."main/main_product/".$row['product_id']."'><font class='text-upper'><strong>".$row['product_name']."</strong></font></a><br/>
                        ".$row['brand']."<br/>
                        ".$row['type']."
                    </td>
                </tr>
            </table>
          </td>
          <td>".$row['info']."</td>
          <td>".$row['qty']."</td>
          <td>IDR ".$row['price']."<br/><button class='btn btn-default btn-small' onclick='add_cart(".$i.")'>Add to Troley</button></td>
          <td><button type='button' class='close' onclick='remove_wishlist(".$row['product_id'].")'>&times;</button></td>
        </tr>";
      $i++;
      } ?>
    </tbody>
  </table>

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

function add_cart(id)
{
  var product_id = $('#product_id_'+id).val();
  var wishlist_id = $('#wishlist_id_'+id).val();
  var qty = $('#qty'+id).val();
  var size = '';

  if(qty == '0'){
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
              //window.location.replace("<?php echo base_url()?>main/cart");
                  $.ajax({
                  type:'POST',
                  url: "<?php echo base_url()?>main/remove_wishlist",
                  data :{wishlist_id:wishlist_id},
                  success:
                  function(msg){
                    if(msg){
                      window.location.replace("<?php echo base_url()?>main/account_wishlist");
                    }
                  }
              });
            }
          }
    });
  }
}

function remove_wishlist(id)
{
  var wishlist_id = $('#wishlist_id_'+id).val();

  $.ajax({
    type:'POST',
    url: "<?php echo base_url()?>main/remove_wishlist",
    data :{wishlist_id:wishlist_id},
    success:
    function(msg){
      if(msg){
        window.location.replace("<?php echo base_url()?>main/account_wishlist");
      }
    }
  });
}
</script>

<?php else:?>
  <p>There is no wishlist</p>
<?php endif;?>