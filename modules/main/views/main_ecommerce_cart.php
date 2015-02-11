<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<h3>SHOPPING TROLLEY</h3><hr>
<div class="col-md-12">
	<?php if($this->cart->total_items() > 0):?>
		<?php echo form_open(base_url().'main/checkout'); ?>
		<table class="table table-cart">
			<thead>
			  <th>PRODUCT</th>
			  <th>SIZE</th>
			  <th>UNIT PRICE</th>
			  <th>QTY</th>
			  <th style="text-align:right; width:120px">SUBTOTAL</th>
			</thead>
			<tbody>
				<?php $i = 1; ?>

				<?php foreach ($this->cart->contents() as $items): ?>

					<?php echo form_hidden($i.'[rowid]', $items['rowid']); ?>

					<tr>
						<td>
							<table>
								<tr>
									<td>
										<img src="<?php echo base_url('modules/main/assets/uploads/'.$items['image_url'])?>" alt="" width="70px" style="margin:10px">
									</td>
									<td>
										<font class="text-upper"><strong><?php echo $items['name']; ?></strong></font><br/>
										<?php echo $items['brand']; ?><br/>
										<?php echo $items['type']; ?>
									</td>
								</tr>
							</table>
						</td>
						<td>
							<select class="form-control" name="<?php echo $i.'[size]'?>" id="size" style="width:200px;">
								<?php
								foreach ($items['size_list'] as $size) {
									$selected = "";
									if(isset($items['size'])){
										$selected = ($items['size']==$size['size_id']) ? " selected='selected' " : "";
									}
									echo "<option $selected value=".$size['size_id'].">".$size['size_txt']."</option>";
								}
								?>
							</select>
						</td>
						<td><?php echo $this->cart->format_number($items['price']); ?></td>
					  	<td><?php echo form_input(array('name' => $i.'[qty]', 'value' => $items['qty'], 'maxlength' => '2', 'size' => '2')); ?>
					 	</td>
					  	<td style="text-align:right">IDR <?php echo $this->cart->format_number($items['subtotal']); ?></td>
					  	<td><a href="<?php echo base_url()?>main/product_remove_cart/<?php echo $items['rowid']?>">x</a></td>
					</tr>

				<?php $i++; ?>

				<?php endforeach; ?>
				<?php if($this->cart->total_items() > 0): ?>
				<tr>
				  <td colspan="3"> </td>
				  <td class="right text-right">Sub Total:</td>
				  <td class="right text-right">IDR <?php echo $this->cart->format_number($this->cart->total()); ?></td>
				</tr>
				<tr>
				  <td class="table-no-border" colspan="3"> </td>
				  <td class="right text-right table-no-border">Shipping Fee(s): </td>
				  <td class="right text-right table-no-border">IDR <?php echo $shipping_fee ?></td>
				</tr>
				<tr>
				  <td class="table-no-border" colspan="3"> </td>
				  <td class="right text-right top-border"><strong>Grand Total: </strong></td>
				  <td class="right text-right top-border"><strong>IDR <?php $total=$this->cart->total()+$shipping_fee; echo $this->cart->format_number($total); ?></strong></td>
				</tr>
				<tr>
				  <td class="table-no-border" colspan="4"> </td>
				  <td class="table-no-border">
				  	<p><a href="<?php echo base_url()?>main/shop_product/all" class="btn btn-default">CONTINUE SHOPPING</a></p>
				  	<p class="text-right"><?php echo form_submit('', 'CHECKOUT','class="btn btn-primary"'); ?></p>
				  </td>
				</tr>
				<?php endif; ?>
			</tbody>
		</table>
	<?php else: ?>
		<p>Your shopping trolley is Empty</p>
	<?php endif; ?>
	<hr>
</div>
<div class="col-md-8">
	<h4>SHOPPING PAYMENT TERMS</h4><hr>
	<p style="width:47%">For complete information payment, delivery, shipment details and general FAQ, <a href="<?php echo base_url()?>main/shipping_payment_terms"><b>click here</b></a></p>
</div>
<div class="col-md-4">
	<h4>CONTACT US</h4><hr>
	<p>Should you wish to speak to someone about your order, please get in touch with us.</p>
	<p>Customer service: +62 21 5367 8290</p>
	<p>Email: goldendragon@flamboo.com</p>
	<p>Office Hours: 9.00am - 5pm Mon - Fri</p>
</div>