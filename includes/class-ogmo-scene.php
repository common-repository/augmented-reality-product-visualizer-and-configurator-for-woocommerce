<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Ogmo_Scene
{
    public static function init()
    {
        add_action( 'woocommerce_init', array( __CLASS__, 'replacing_product_image' ) );
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'rp_load_react_app' ) );
    }

    /**
     * Load react app files in WordPress admin.
     *
     * @return bool|void
     */

    public static function replacing_product_image(){
        remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20 );
        
        function wc_product_image_replace(){         
            $select_option_id = get_post_meta( get_the_ID(), '_select_field', true );
            
            $webservice = new Ogmo_Webservice();
            $design = $webservice->get_design($select_option_id);

            if (empty($select_option_id) || $design=="Error") {
                wc_get_template( 'single-product/product-image.php' );
            } else {
                $ogmo_viewer_url = OGMO_VIEWER_URL;
                function getOS() { 

                    $user_agent = $_SERVER['HTTP_USER_AGENT'];

                    $os_platform  = "device";

                    $os_array     = array(
                                        '/iphone/i'             =>  'iPhone',
                                        '/ipad/i'               =>  'iPad',
                                        '/android/i'            =>  'Android',
                                    );

                    foreach ($os_array as $regex => $value)
                        if (preg_match($regex, $user_agent))
                            $os_platform = $value;
                            return $os_platform;
                }
                $user_os = getOS();
                $ar_object = json_decode($design);
                ?>
                    <link rel="stylesheet" href="<?php echo get_ogmo()->plugin_url() . '/assets/css/ogmo-product-viewer.css'?>">
                    <div class="woocommerce-product-gallery woocommerce-product-gallery woocommerce-product-gallery--with-images woocommerce-product-gallery--columns-4 images ogmo-topcontainer">
                        <div id="ogmo_product_react_app" class="ogmo-canvasstyle"></div>
                        <?php
                        if($user_os == 'Android') {
                            ?>
                            <div class="ogmo-arbtnstyle">
                                <a href="intent://arvr.google.com/scene-viewer/1.0?file=<?= $ar_object->sceneInfo->modelPath?>&mode=ar_only#Intent;scheme=https;package=com.google.android.googlequicksearchbox;action=android.intent.action.VIEW;end;">
                                    <img style='border:none;width: 30px;height: 30px;' src="<?php echo get_ogmo()->plugin_url() . '/assets/images/ARIcon.png' ?>" alt="ar logo"/>
                                </a>    
                            </div>
                        <?php
                        }
                        elseif($user_os == 'iPhone' || $user_os == 'iPad' ) {
                            ?>
                            <div class="ogmo-arbtnstyle">
                                <a href="<?= $ar_object->sceneInfo->modelPathUsdz ?>" rel="ar">
                                    <img style='border:none;width: 30px;height: 30px;' src="<?php echo get_ogmo()->plugin_url() . '/assets/images/ARIcon.png' ?>" alt="ar logo"/>
                                </a>  
                            </div>
                        <?php
                        }
                        else {
                            ?>
                            <div id="arbutton" class="ogmo-arbtnstyle">
                                <img style='border:none;width: 30px;height: 30px;' src="<?php echo get_ogmo()->plugin_url() . '/assets/images/ARIcon.png' ?>" alt="ar logo"/>
                            </div>
                        <?php
                        }
                        if($user_os != 'iPhone') {
                        ?>
                        <div id="fullscreenbutton">
                            <img class="ogmo-iconstyle" style='border:none;' src="<?php echo get_ogmo()->plugin_url() . '/assets/images/fullscreen.svg' ?>" alt="fullscreen logo"/>
                        </div>
                        <?php
                        }
                        ?>
                        <div id="artooltip" class="ogmo-tooltipstyle">View in AR</div>
                    </div>
                    <div id="qrpopup" class="ogmo-screen">
                        <div class="ogmo-popupdiv">
                            <div class="ogmo-container">
                                <div class="ogmo-box1">
                                    <img class="ogmo-logo" src="<?php echo get_ogmo()->plugin_url() . '/assets/images/ogmoLogo.png' ?>" alt="ogmo logo" />
                                    <div class="ogmo-qrimgdiv">
                                        <canvas id="canvas"></canvas>
                                    </div>
                                    <p class="ogmo-artext"> Works in ARKit supported iOS devices and ARCore supported Android devices</p>
                                </div>
                                <div class="ogmo-box2">
                                    <div id="closeqr" class="ogmo-closebtn">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="ogmo-closeIcon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </div>
                                    <div class="ogmo-textDiv">
                                        <div>
                                            <div class="ogmo-title">You are just one step away from the magic!</div>
                                        </div>
                                        <div>
                                            <div class="ogmo-description">Scan the QR code from your smartphone or tablet for Augmented Reality experience.</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <script src="<?php echo get_ogmo()->plugin_url() . '/assets/js/qrcode.min.js'?>"></script>
                    <script>
                        var arTooltip = document.getElementById('artooltip');
                        window.addEventListener('load', () => {
                            setTimeout(() => { 
                                arTooltip.classList.add('ogmo-fade-out'); 
                                }, 5000)
                        });

                        var fullscreenbtn = document.getElementById('fullscreenbutton');
                        if(fullscreenbtn) {
                            fullscreenbtn.addEventListener('click',() => {
                                var ogmoCanvas = document.getElementById('ogmo_product_react_app');
                                if (ogmoCanvas.requestFullscreen) {
                                    ogmoCanvas.requestFullscreen();
                                }
                                else if (ogmoCanvas.mozRequestFullScreen) {
                                    ogmoCanvas.mozRequestFullScreen();
                                }
                                else if (ogmoCanvas.webkitRequestFullScreen) {
                                    ogmoCanvas.webkitRequestFullScreen();
                                }
                                else if (ogmoCanvas.msRequestFullscreen) {
                                    ogmoCanvas.msRequestFullscreen();
                                }
                            })
                        }

                        var arviewbtn = document.getElementById('arbutton');
                        if(arviewbtn) {
                            arviewbtn.addEventListener('click',() => {

                                document.getElementById('qrpopup').classList.add('ogmo-active');

                                QRCode.toCanvas(document.getElementById('canvas'), '<?= $ogmo_viewer_url?><?= $ar_object->shortId?>', { width: 175 } , function (error) {
                                    if (error){
                                        console.error(error)
                                    }
                                })

                            })
                        }
                        
                        var qrclosebtn = document.getElementById('closeqr');
                        if(qrclosebtn) {
                            qrclosebtn.addEventListener('click',() => {
                                document.getElementById('qrpopup').classList.remove('ogmo-active');
                            })
                        }

                        var design_info = '<?php
                                $design;
                                echo $design;
                        ?>';
                    </script>
                <?php
            }
        }
        add_action( 'woocommerce_before_single_product_summary', 'wc_product_image_replace', 20 );
    }

    public static function rp_load_react_app($hook){
        // Setting path variables.
        $plugin_app_dir_url = plugin_dir_url( __DIR__ ) . 'widget/';
        $react_app_build = $plugin_app_dir_url .'build/';
        $manifest_url = $react_app_build. 'asset-manifest.json';

        // Request manifest file.
        $request = file_get_contents( $manifest_url );

        // If the remote request fails, wp_remote_get() will return a WP_Error, so let’s check if the $request variable is an error:
        if( !$request )
            return false;

        // Convert json to php array.
        $files_data = json_decode($request);
        if($files_data === null)
            return ;


        if(!property_exists($files_data,'entrypoints'))
            return false;

        // Get assets links.
        $assets_files = $files_data->entrypoints;

        $js_files = array_filter($assets_files,array( __CLASS__, 'rp_filter_js_files' ) );
        $css_files = array_filter($assets_files,array( __CLASS__, 'rp_filter_css_files' ) );
        // Load css files.
        foreach ($css_files as $index => $css_file){
            wp_enqueue_style('react-plugin-'.$index, $react_app_build . $css_file);
        }

        // Load js files.
        foreach ($js_files as $index => $js_file){
            wp_enqueue_script('react-plugin-'.$index, $react_app_build . $js_file, array(), 1, true);
        }

        // Variables for app use.
        wp_localize_script('react-plugin-0', 'rpReactPlugin',
            array('appSelector' => '#ogmo_product_react_app')
        );
    }

    /**
     * Get js files from assets array.
     *
     * @param array $file_string
     *
     * @return bool
     */
    private static function rp_filter_js_files ($file_string){
        return pathinfo($file_string, PATHINFO_EXTENSION) === 'js';
    }

    /**
     * Get css files from assets array.
     *
     * @param array $file_string
     *
     * @return bool
     */
    private static function rp_filter_css_files ($file_string) {
        return pathinfo( $file_string, PATHINFO_EXTENSION ) === 'css';
    }

}

Ogmo_Scene::init();