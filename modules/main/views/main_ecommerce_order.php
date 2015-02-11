<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<h3>MY ORDERS</h3><hr/>
<?php if(count($order_list) > '0'):?>
  <table class="table table-hover">
  	<thead>
  		<td>No</td><td>ORDER NUMBER</td><td>DATE</td><td>TIME</td><td>STATUS</td><td>TOTAL</td>
  	</thead>
  	<tbody>
  	<?php $i=1;
  		foreach($order_list as $row)
  		{
  			if($row->status == 0){
              	echo "<tr>
  		            <td>$i</td>
  		            <td>$row->order_no</td>
  		            <td>".date("d-m-Y", strtotime($row->date))."</td>
  		            <td>$row->time</td>
  		            <td><font class='text-danger'><i>Pending</i></font></td>
  		            <td>IDR ".number_format($row->total, 0, ',', '.')."<br/><button class='btn btn-default btn-small' onclick='openModal(\"$row->order_no\")'>Details</button></td>
  		        </tr>";
              }else if($row->status == 1){
              	echo "<tr>
  		            <td>$i</td>
  		            <td>$row->order_no</td>
  		            <td>".date("d-m-Y", strtotime($row->date))."</td>
  		            <td>$row->time</td>
  		            <td><font class='text-primary'><i>Processing</i></font></td>
  		            <td>IDR ".number_format($row->total, 0, ',', '.')."<br/><button class='btn btn-default btn-small' onclick='openModal(\"$row->order_no\")'>Details</button></td>
  		        </tr>";
              }else{
              	echo "<tr>
  		            <td>$i</td>
  		            <td>$row->order_no</td>
  		            <td>".date("d-m-Y", strtotime($row->date))."</td>
  		            <td>$row->time</td>
  		            <td><font class='text-success'><i>Process/Paid</i></font></td>
  		            <td>IDR ".number_format($row->total, 0, ',', '.')."<br/>
                  <button class='btn btn-default btn-small' onclick='openModal(\"$row->order_no\")'>Details</button></td>
  		        </tr>";
              } 
          $i++;
      } ?>
  	</tbody>
  </table>
<?php else:?>
  <p>There is no order yet</p>
<?php endif;?>

<!-- Modal -->
<div class="modal fade" id="orderModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <div>
        	<b>Order Number: </b><span id="order_no"></span><br/>
        	<b>Date: </b><span id="date"></span><br/>
        	<b>Time: </b><span id="time"></span><br/>
        	<b>Status: </b> <i><span id="status" class="text-danger"></span></i> <br/>
        </div>
      </div>
      <div class="modal-body">
      	<h3>ORDER DETAIL</h3><hr/>
      	<div id="table_order"></div>
        <hr>
      	<div class="pull-right">
      		<table>
      			<tr>
      				<td width="150px">Sub Total</td>
      				<td width="200px">IDR <span id="subtotals"></span></td>
      			</tr>
      			<tr>
      				<td>Shipping Fee(s)</td>
      				<td>IDR <span id="shipping"></span></td>
      			</tr>
      			<tr>
      				<td><b>Grand Total: </b></td>
      				<td><b>IDR <span id="total_order"></span></b></td>
      			</tr>
      		</table>
      	</div>
      	<div class="clearfix"></div>
      </div>
    </div>
  </div>
</div>

<script>
function openModal(id){
	$.ajax({
        type:'POST',
        url: "get_detail_order",
        data :{id:id},
        dataType: 'html',
        success:
        function(msg){
            $('#table_order').html(msg);
        }
    });
    $.ajax({
        type:'POST',
        url: "get_info_order",
        data :{id:id},
        dataType: 'json',
        success:
        function(msg){
            document.getElementById("order_no").innerHTML= msg.order_no;
            document.getElementById("date").innerHTML= msg.date;
            document.getElementById("time").innerHTML= msg.time;
            document.getElementById("status").innerHTML= msg.status;
            document.getElementById("subtotals").innerHTML= msg.total;
            document.getElementById("shipping").innerHTML= msg.shipping;
            document.getElementById("total_order").innerHTML= msg.total_order;
        }
    }); 

    $('#orderModal').modal('show');
}
</script>