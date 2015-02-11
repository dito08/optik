<style>
	.name-search{
		text-transform: uppercase;
	}
</style>
<h3>SEARCH RESULT</h3><hr>
<?php if ( ! is_null($results)): ?>
    <?php if (count($results)): ?>
        <ul>
        <?php foreach ($results as $result): ?>
            <li>
            <a href="<?php echo base_url(); ?>main/main_product/<?php echo $result->id?>" class="name-search"><?php echo $result->product_name.' - '.$result->brand.' '.$result->type; ?></a>
			<p><?php echo $result->info; ?></p>	
            </li>
        <?php endforeach ?>
        </ul>
    <?php else: ?>
        <p><em>There are no results for your Search Term. Please using product category terms.</em></p>
    <?php endif ?>
<?php endif ?>