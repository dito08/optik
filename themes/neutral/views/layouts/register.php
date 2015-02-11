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
                    <h3>Create An Account</h3>
                    <hr/>
                    <div id="__section-left-and-content" class="col-md-6">
                        <div id="__section-content" class="col-md-12"><?php echo $template['body'];?></div>
                    </div><!--/#layout-content-->
                    <div id="__section-right" class="col-md-5 col-md-offset-1">
                        <div class="col-md-12">
                            <br/><h4>Log in with Facebook</h4>
                            <hr/>
                            <p>It's Fast and Easy</p>
                            {{ widget_name:social_auth }}
                            <p>We'll Never Post anything without your permission</p>
                        </div>
                    </div><!--/#layout-widget-->
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