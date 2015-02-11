<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<hr>
<div class="row-fluid">
	<div class="col-md-4 text-center">
		<b>Total Customer</b>
		<h2><?php echo $total_customer?></h2>
	</div>
	<div class="col-md-4 text-center">
		<b>Total Order</b>
		<h2><?= $total_order ?></h2>
	</div>
	<div class="col-md-4 text-center">
		<b>Today Order</b>
		<h2><?= $total_order_today?></h2>
	</div>
</div>
<div class="clearfix"></div>
<hr>
<div class="row-fluid">
	<div class="col-md-12">
		<div class="panel panel-danger">
		  <div class="panel-heading">
		    <h3 class="panel-title">Pending Order</h3>
		  </div>
		  <div class="panel-body">
		    <table class="table">
		    	<thead>
		    		<th>Order No</th>
		    		<th>Total Qty</th>
		    		<th>Date / Time</th>
		    		<th>Shipping</th>
		    		<th>Total</th>
		    		<th>Name</th>
		    	</thead>
		    	<tbody>
		    		<?php foreach ($order as $row) {
		    			if($row->status == 0){
		    				echo "<tr>
			    			<td>$row->order_no</td>
			    			<td>$row->total_qty</td>
			    			<td>$row->date/$row->time</td>
			    			<td>$row->shipping</td>
			    			<td>$row->total</td>
			    			<td>$row->first_name $row->last_name</td></tr>";
		    			}
		    		}?>
		    	</tbody>
		    </table>
		  </div>
		</div>
	</div>

	<div class="col-md-12">
		<div class="panel panel-primary">
		  <div class="panel-heading">
		    <h3 class="panel-title">Paid Order</h3>
		  </div>
		  <div class="panel-body">
		    <table class="table">
		    	<thead>
		    		<th>Order No</th>
		    		<th>Bank</th>
		    		<th>Transfer Date</th>
		    		<th>Account Name</th>
		    		<th>Payment By</th>
		    		<th>Name</th>
		    	</thead>
		    	<tbody>
		    		<?php foreach ($order as $row) {
		    			if($row->status == 1){
		    				echo "<tr>
			    			<td>$row->order_no</td>
			    			<td>$row->bank</td>
			    			<td>$row->transfer_date</td>
			    			<td>$row->bank_account_name</td>
			    			<td>$row->payment_by</td>
			    			<td>$row->first_name $row->last_name</td></tr>";
		    			}
		    		}?>
		    	</tbody>
		    </table>
		  </div>
		</div>
	</div>

	<div class="col-md-12">
		<div class="panel panel-info">
		  <div class="panel-heading">
		    <h3 class="panel-title">Confirmed Order</h3>
		  </div>
		  <div class="panel-body">
		    <table class="table">
		    	<thead>
		    		<th>Order No</th>
		    		<th>Date</th>
		    		<th>Shipping</th>
		    		<th>Total</th>
		    		<th>Name</th>
		    		<th>Shipping Address</th>
		    	</thead>
		    	<tbody>
		    		<?php foreach ($order as $row) {
		    			if($row->status == 2){
		    				echo "<tr>
			    			<td>$row->order_no</td>
			    			<td>$row->date</td>
			    			<td>$row->shipping</td>
			    			<td>$row->total</td>
			    			<td>$row->first_name $row->last_name</td>
			    			<td>$row->shipping_address</td></tr>";
		    			}
		    		}?>
		    	</tbody>
		    </table>
		  </div>
		</div>
	</div>
</div>