<link rel="stylesheet" type="text/css" href="<?php echo base_url('assets/grocery_crud/css/ui/simple/'.grocery_CRUD::JQUERY_UI_CSS); ?>" />
<style type="text/css">
</style>

<table id="md_table_photos" class="table table-striped table-bordered">
    <thead>
        <tr>
            <th style="">Product ID</th>
            <th style="">Product Name</th>
            <th style="">Brand</th>
            <th style="">Type</th>
            <th style="">Qty</th>
            <th style="">Size</th>
        </tr>
    </thead>
    <tbody>
        <!-- the data presentation be here -->
        <?foreach ($result as $row): ?>
            <tr>
                <td><?php echo $row->product_id ?></td>
                <td><?php echo $row->product_name ?></td>
                <td><?php echo $row->brand ?></td>
                <td><?php echo $row->type ?></td>
                <td><?php echo $row->qty ?></td>
                <td><?php echo $row->size ?></td>
            </tr>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- This is the real input. If you want to catch the data, please json_decode this input's value -->
<input id="md_real_field_photos_col" name="md_real_field_photos_col" type="hidden" />

<script type="text/javascript" src="<?php echo base_url('assets/grocery_crud/js/jquery_plugins/ui/'.grocery_CRUD::JQUERY_UI_JS); ?>"></script>
<script type="text/javascript" src="<?php echo base_url('assets/grocery_crud/js/jquery_plugins/jquery.chosen.min.js'); ?>"></script>
<script type="text/javascript" src="<?php echo base_url('assets/grocery_crud/js/jquery_plugins/jquery.ui.datetime.js'); ?>"></script>
<script type="text/javascript" src="<?php echo base_url('assets/grocery_crud/js/jquery_plugins/jquery.numeric.min.js'); ?>"></script>