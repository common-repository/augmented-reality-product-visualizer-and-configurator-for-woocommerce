<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Ogmo_Admin_Home
{

    public static function init()
    {
        add_action('admin_init', array(__CLASS__, 'redirect'));
        add_action('admin_menu', array(__CLASS__, 'add_get_started_submenu'));
        add_filter('admin_footer_text', array( __CLASS__, 'remove_footer_admin' ) );

        $css_path = plugin_dir_url( __DIR__ ) . '../assets/css';
        wp_enqueue_style('ogmo-admin-home', $css_path.'/ogmo-admin-home.css');
    }

    // Admin footer remove
    public static function remove_footer_admin () 
    {
        remove_filter( 'update_footer', 'core_update_footer' );
    }

    public static function activate()
    {
        global $wp_rewrite;
        add_option('ogmo_do_activation_redirect', true);
        $wp_rewrite->flush_rules(false);
    }


    public static function redirect()
    {
        if (get_option('ogmo_do_activation_redirect', false)) {
            delete_option('ogmo_do_activation_redirect');
            wp_redirect(admin_url('admin.php?page=ogmo-plugin'));
        }
    }


    public static function add_get_started_submenu()
    {
        add_menu_page(
            __('Get Started', 'ogmo'),
            __('OGMO', 'ogmo'),
            'manage_product_terms', 'ogmo-plugin',
            array(__CLASS__, 'get_about_page'),
            get_ogmo()->plugin_url() . '/assets/images/ogmo_icon.png'
        );
    }


    private static function permalink_check()
    {
        $permalinks = get_option('permalink_structure', false);
        return $permalinks && strlen($permalinks) > 0;
    }


    private static function site_url_check()
    {
        return !strpos(get_site_url(), 'localhost') && !strpos(get_site_url(), '127.0.0.1');
    }


    private static function protocol_check()
    {
        if (is_ssl()) {
            return strpos(get_option('siteurl'), 'https://') !== false && strpos(get_option('home'), 'https://') !== false;
        }
        return true;
    }

    private static function get_ogmo_start_url()
    {
        return OGMO_DASHBOARD_URL . '?storeUrl=' .get_site_url();
    }

    private static function issues_check()
    {
        $ogmo_plugin_issues = array();
        if (!self::permalink_check()) {
            $ogmo_plugin_issues[] = __('WooCommerce API will not work unless your permalinks in Settings > Permalinks are set up correctly.', 'ogmo');
        }
        if (!self::site_url_check()) {
            $ogmo_plugin_issues[] = '<span style="font-size: 18px;font-weight:700;">You can\'t connect OGMO from localhost!</span> <br/> Please make sure OGMO could reach your site to establish a connection.';
        }
        if (!self::protocol_check()) {
            $ogmo_plugin_issues[] = 'Your site is using https, but your site url is set to be in http. Please update your site url to be in https by going to: WordPress admin > Settings > General.';
        }

        return $ogmo_plugin_issues;
    }


    private static function error_page()
    {
        $ogmo_plugin_issues = self::issues_check();
        ?>
        <div class="wrap" style="max-width: 1200px;margin-top: 20px">
        <div style="width:100%; height:100%;">
            <div style="background-color:white; padding: 45px 20px 45px 30px;position: relative;overflow: hidden; border: 1px solid #A9A9A9;">
                    <img style="width:150px;"
                         src="<?php echo get_ogmo()->plugin_url() . '/assets/images/ogmo_logo.png' ?>">
                    <br/><br/>
                    <div style="margin-left:1%; color:#000000;">
                            <?php
                                foreach ($ogmo_plugin_issues as $issue) {
                                    echo '<span style="font-size: 16px;line-height: 32px;font-weight:500;position: relative;">' . wp_kses_post($issue) . '</span>';
                                }
                            ?>
                    </div>
            </div>
        </div>
        </div>
        <?php
    }


    private static function connect_with_ogmo()
    {
        ?>
        <div class="ogmo-wrap">

            <div class="ogmo-container">
                <img style="width:150px;"
                        src="<?php echo get_ogmo()->plugin_url() . '/assets/images/ogmo_logo.png' ?>">

                <h2 class="ogmo-page-title">
                You are just three steps away from making your product reveal it's story!
                </h2>
                <div class="ogmo-container-div">
                    <p class="ogmo-setup-steps">
                        <b style="color:#000000;">
                        1. Click “Get Start with OGMO”.<br><br>
                        2. Log in from your OGMO account.<br><br>
                        3. Approve Wordpress authentication.<br><br>
                        </b>
                    </p>
                    <button id="ogmo-go-to-btn" class="button button-primary">
                        <a
                                id="ogmo-go-to"
                                href="<?php echo self::get_ogmo_start_url(); ?>"
                                target="_blank">
                            <?php _e('Get started with OGMO', 'ogmo'); ?>
                        </a>
                    </button>
                    <p>
                    Step into our <a href="<?php echo OGMO_DOCS_URL; ?>" target="_blank" style="text-decoration:none;">Knowledge Hub</a> to get step by step guide on how to use OGMO
                    </p>
                </div>
            </div>
        </div>
        <?php
    }

    private static function go_to_ogmo($priceplan_info,$design_summery_info)
    {
        $priceplan = json_decode($priceplan_info,true);
        $design_summery = json_decode($design_summery_info,true);
        $design_count_percentage = $design_summery['design_count']/$priceplan['design_count']*100;
        if($design_count_percentage<=100){
            if($design_count_percentage>=80){
                $style_design_count = "width:".$design_count_percentage."%; class='ogmo-red-progress'";
            }
            else{
                $style_design_count = "width:".$design_count_percentage."%; class='ogmo-blue-progress'";
            }
        }
        else{
            $style_design_count = "width:100%; class='ogmo-red-progress'";
        }
        $design_view_count_percentage = $design_summery['design_view_count']/$priceplan['design_view_count']*100;
        if($design_view_count_percentage<=100){
            if($design_view_count_percentage>=80){
                $style_design_view_count = "width:".$design_view_count_percentage."%; class='ogmo-red-progress'";
            }
            else{
                $style_design_view_count = "width:".$design_view_count_percentage."%; class='ogmo-blue-progress'";
            }
        }
        else{
            $style_design_view_count = "width:100%; class='ogmo-red-progress'";
        }

        ?>
        <div class="ogmo-wrap">
        <div style="width:100%; height:100%;">
            <div class="ogmo-container">
                    <img style="width:150px;"
                         src="<?php echo get_ogmo()->plugin_url() . '/assets/images/ogmo_logo.png' ?>">

                    <div style="margin-left:1%">
                        <h2 class="ogmo-page-title">
                            Your Wordpress site is now connected <br> with OGMO
                        </h2>
                        <div class="ogmo-container-div">
                            <p class="ogmo-setup-steps">
                                <b style="color:#000000;">
                                1. Go to Products > Your Product > OGMO.<br><br>
                                2. Add your Products’s 3D model as a GLB or select it from your available designs from OGMO Web.<br><br>
                                3. Save your configuration and view your Product in AR.<br><br>
                                </b>
                            </p>
                                <p class="ogmo-design-description">You can also manage your Designs and Account settings from “OGMO Portal”</p>
                                    <button id="ogmo-go-to-btn" class="button button-primary">
                                        <a
                                        id="ogmo-go-to"
                                        href="<?php echo OGMO_DASHBOARD_URL; ?>" 
                                        target="_blank">
                                            <?php _e('Go to OGMO Portal', 'ogmo'); ?>
                                        </a>
                                    </button>
                                <br>
                                <p class="ogmo-docs-description">Step into our  <a style="text-decoration:none;" href="<?php echo OGMO_DOCS_URL; ?>" target="_blank">Knowledge base</a>  to get step by step guide on how to use OGMO</p>
                        </div>
    
                    </div>
            </div>
            
            <div class="ogmo-dashboard-container">
                    <h3 class="ogmo-dashboard-title">Dashboard</h3>
                    <div class="ogmo-card-container">
                        <div class="ogmo-card-border">
                            <div class="ogmo-cards" style="width:280px;">
                                <h4 class="ogmo-subscription">Your Monthly Subcription</h4>
                                <h3 class="ogmo-subscription-type"><?php echo $priceplan['priceplan_name']?></h3>
                                <h4 class="ogmo-card-out-of-count">&nbsp;</h4>
                                <div>&nbsp;</div>
                            </div>
                        </div>

                        <div class="ogmo-card-border">
                            <div class="ogmo-cards">
                                <h3 class="ogmo-card-title">Total designs used</h3>
                                <h3 class="ogmo-card-count"><?php echo (int)$design_summery['design_count']?></h3>
                                <h4 class="ogmo-card-out-of-count">
                                <?php
                                if($priceplan['design_count']!=="unlimited"){
                                    echo "Out of ".(int)$priceplan['design_count'];
                                }else{
                                    echo "&nbsp;";
                                }
                                ?>
                                </h4>
                                <?php
                                if($priceplan['design_count']=="unlimited"){
                                    echo "<span class='ogmo-design-count'>You have ".$priceplan['design_count']." designs </span>";
                                }else{
                                    echo "<div class='ogmo-progressbar'>
                                        <div style= $style_design_count > </div>
                                    </div>";
                                }

                                ?>
                            </div>
                        </div>
                        
                        <div class="ogmo-card-border">
                            <div class="ogmo-cards">
                                <h3 class="ogmo-card-title">Total views made</h3>
                                <h3 class="ogmo-card-count"><?php echo (int)$design_summery['design_view_count']?></h3>
                                <h4 class="ogmo-card-out-of-count">Out of <?php echo (int)$priceplan['design_view_count']?></h4>
                                <div class="ogmo-progressbar">
                                    <div style=<?php echo $style_design_view_count?>> </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </div>
        </div>
        <?php
    }


    public static function get_about_page()
    {
        $show_error_page = true;
        $ogmo_plugin_issues = self::issues_check();
        $integration = new Ogmo_Integration();
        if (!empty($ogmo_plugin_issues) && $show_error_page) {
            self::error_page();
        }
        else if ($integration->get_option('client_id') == '' && $integration->get_option('refresh_token') == ''){
            self::connect_with_ogmo();
        } else {
            $webservice = new Ogmo_Webservice();
            $priceplan_info = $webservice->get_priceplan_info();
            $design_summery_info = $webservice->get_design_summery();
            self::go_to_ogmo($priceplan_info,$design_summery_info);
        }
    }
}

Ogmo_Admin_Home::init();
