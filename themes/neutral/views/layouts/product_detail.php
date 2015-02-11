<!DOCTYPE html>
<html lang="{{ language:language_alias }}">
    <head>
        <meta charset="utf-8">
        <title><?php echo $product[0]['product_name'].' by '.$product[0]['brand'];?></title>
        <meta property="fb:app_id" content="1378288949127495">
        <meta property="og:url" content="<?php echo base_url().'main/main_product/'.$product[0]['id']?>" /> 
        <meta property="og:title" content="<?php echo $product[0]['product_name']?>" /> 
        <meta property="og:image" content="http://golden-dragon.arthalab.com/modules/main/assets/uploads/98020729938bb4cbaf992315cce77f69f814d7.jpg"/> 
        <meta property="og:description" content="<?php echo $product[0]['info']?>">
        <?php echo $template['metadata'];?>
        <link rel="icon" href="{{ site_favicon }}">
        <!-- Le styles -->
        <?php
            $asset = new CMS_Asset();       
            $asset->add_cms_css('bootstrap/css/bootstrap.min.css');
            $asset->add_cms_css('css/jquery.jqzoom.css');
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
            $asset->add_cms_js("js/jquery.jqzoom-core.js");
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
                <!-- <div id="__section-banner">
                    {{ widget_name:section_banner }}
                </div> -->
                <div>     
                    <div id="__section-left-and-content" class="col-md-12">
                        <div id="__section-content" class="col-md-12"><?php echo $template['body'];?></div>
                        <div class="col-md-4">
                            {{ widget_name:recent_view }}
                        </div>
                        <div class="col-md-8">
                            {{ widget_name:product_love }}
                        </div>
                    </div><!--/#layout-content-->
                </div>
            </div><!--/row-->
          <hr>
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