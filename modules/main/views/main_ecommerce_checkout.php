<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<h3>ORDER DETAIL</h3><hr>
<table class="table table-cart">
	<thead>
	  <th>PRODUCT</th>
	  <th>SIZE</th>
	  <th class="text-center" style="width:60px;">QTY</th>
	  <th style="text-align:right;">SUBTOTAL</th>
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
					<select class="form-control" name="<?php echo $i.'[size]'?>" id="size" disabled>
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
			  	<td class="text-center"><?php echo $items['qty']; ?></td>
			  	<td style="text-align:right">IDR <?php echo $this->cart->format_number($items['subtotal']); ?></td>
			</tr>

		<?php $i++; ?>

		<?php endforeach; ?>
		<?php if($this->cart->total_items() > 0): ?>
		<tr>
		  <td colspan="3" class="right text-right">Sub Total:</td>
		  <td class="right text-right">IDR <?php echo $this->cart->format_number($this->cart->total()); ?></td>
		</tr>
		<tr>
		  <td colspan="3" class="right text-right table-no-border">Shipping Fee(s): </td>
		  <td class="right text-right table-no-border">IDR <?php echo $shipping_fee ?></td>
		</tr>
		<tr>
		  <td colspan="3" class="right text-right top-border"><strong>Grand Total: </strong></td>
		  <td class="right text-right top-border"><strong>IDR <?php $total=$this->cart->total()+$shipping_fee; echo $this->cart->format_number($total); ?></strong></td>
		</tr>
		<?php endif; ?>
	</tbody>
</table>