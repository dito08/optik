<table class="table">
	<thead>
		<td>PRODUCT</td>
		<td>SIZE</td>
		<td>QTY</td>
		<td>SUBTOTAL</td>
	</thead>
	<tbody>
	<?php
    $i = 0;
    foreach($order_detail as $row)
    {
        echo "<tr>
        <td>
            <table>
                <tr>
                    <td>
                        <img src='".base_url('modules/main/assets/uploads/'.$row['image_url'])."' alt='' width='70px' style='margin:10px'>
                    </td>
                    <td>
                        <font class='text-upper'><strong>".$row['product_name']."</strong></font><br/>
                        ".$row['brand']."<br/>
                        ".$row['type']."
                    </td>
                </tr>
            </table>
        </td>
        <td>".$row['size']."</td>
        <td>".$row['qty']."</td>
        <td>IDR ".$row['subtotal']."</td>
        </tr>
        ";

        $i++;
    }   ?>
	</tbody>
</table>