<!DOCTYPE html>
<html lang="{{ language:language_alias }}">
    <head>
        <meta charset="utf-8">
        <title><?php echo $template['title'];?></title>
        <?php echo $template['metadata'];?>
        <link rel="icon" href="{{ site_favicon }}">
        <!-- Le styles -->
        <?php
            $asset = new CMS_Asset();       
            $asset->add_cms_css('bootstrap/css/bootstrap.min.css');
            $asset->add_themes_css('bootstrap.min.css', '{{ used_theme }}', 'default');
            $asset->add_themes_css('style.css', '{{ used_theme }}', 'default');
            echo $asset->compile_css();
        ?>
        <!-- Le fav and touch icons -->
        <link rel="shortcut icon" href="{{ site_favicon }}">
        {{ widget_name:section_custom_script }}
    </head>
    <body>
        <?php
            $asset->add_cms_js("bootstrap/js/bootstrap.min.js");
            $asset->add_themes_js('script.js', '{{ used_theme }}', 'default');
            echo $asset->compile_js();
        ?>
        <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
        <!--[if lt IE 9]>
          <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
        <![endif]-->
        {{ widget_name:section_top_fix }}
        <div class="container">
            <div class="row-fluid">
                <div>     
                    <div id="__section-left-and-content" class="col-md-3" style="padding:0px;">
                        <h3>GALLERY</h3><hr/>
                        <ul class="nav nav-pills nav-stacked" style="max-width: 300px;">
                          <li><a href="<?php echo base_url();?>main/gallery_product/all"><?php echo ($current == 'all') ? '<span class="glyphicon glyphicon-play"></span>' : ''; ?> View All</a></li>
                          <li><a href="<?php echo base_url();?>main/gallery_product/residence"><?php echo ($current == 'residence') ? '<span class="glyphicon glyphicon-play"></span>' : ''; ?> Residence</a></li>
                          <li><a href="<?php echo base_url();?>main/gallery_product/function"><?php echo ($current == 'function') ? '<span class="glyphicon glyphicon-play"></span>' : ''; ?> Function Room</a></li>
                          <li><a href="<?php echo base_url();?>main/gallery_product/staircase"><?php echo ($current == 'staircase') ? '<span class="glyphicon glyphicon-play"></span>' : ''; ?> Staircase</a></li>
                          <li><a href="<?php echo base_url();?>main/gallery_product/office"><?php echo ($current == 'office') ? '<span class="glyphicon glyphicon-play"></span>' : ''; ?> Office</a></li>
                          <li><a href="<?php echo base_url();?>main/gallery_product/hotel"><?php echo ($current == 'hotel') ? '<span class="glyphicon glyphicon-play"></span>' : ''; ?> Hotel & Apartment</a></li>
                          <li><a href="<?php echo base_url();?>main/gallery_product/restaurant"><?php echo ($current == 'restaurant') ? '<span class="glyphicon glyphicon-play"></span>' : ''; ?> Restaurant</a></li>
                        </ul>
                    </div><!--/#layout-content-->
                    <div id="__section-right" class="col-md-9">
                        <p>We provide custom production that includes custom design rugs and wall-to-wall carpets for staircases, apartments, residences, restaurants, hotels and rooms. You can find our past projects in our gallery below. If you interested in custom ordering your carpet, please contact us.</p><hr/>
                        <h3><?php echo $title ?></h3><hr/>
                        <div id="__section-content" class="col-md-12"><?php echo $template['body'];?></div>
                    </div><!--/#layout-widget-->
                </div>
            </div><!--/row-->
        </div><!--/.fluid-container-->
        <footer>{{ widget_name:section_bottom }}</footer>
        <script type="text/javascript">
            $(document).ready(function(){
                // if section-banner is empty, remove it
                if($.trim($('__section-banner').html()) == ''){
                    $('__section-banner').remove();
                }            
            });
        </script>
    </body>
</html>