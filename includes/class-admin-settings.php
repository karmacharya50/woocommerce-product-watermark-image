<?php
/**
 * Product Image watermark admin setting.
 *
 * @version 1.0.0
 * @author  WPFactory
 */

if ( ! class_exists('Admin_Settings') ) {
    class Admin_Settings {
    
        public function __construct() {
    
            // Admin settings for watermark configuration
            add_action( 'admin_menu', array($this, 'create_watermark_settings_page') );
            add_action( 'admin_init', array($this, 'register_settings') );
        }
       
        // Create settings page for watermark options
        public function create_watermark_settings_page() {
            add_options_page(
                'WooCommerce Watermark Settings',
                __( 'Watermark Settings', 'wf-watermark' ),
                'manage_options',
                'wf-watermark-settings',
                array($this, 'watermark_settings_page')
            );
        }
    
        public function watermark_settings_page() {
            ?>
            <div class="wf-watermark-wrapper">
                <form method="post" action="options.php">
                    <?php
                    settings_fields( 'wf-watermark-settings-fields' );
                    do_settings_sections( 'wf-watermark-settings' );
                    submit_button();
                    ?>
                </form>
                <div class="generate-watermark-image-old-product">
                    <h2><?php echo __( 'Generate watermark image for old products', 'wf-watermark' );?>
                    <p><?php echo __( 'Apply a watermark to product images for products created before this plugin was activated.', 'wf-watermark' );?>
                    <div id="generate-watermark-old-product-image" ><?php echo __( 'Generate','wf-watermark' );?></div>
                </div>
            </div>
            <?php
        }
    
        // Register settings for watermark options
        public function register_settings() {
            register_setting( 'wf-watermark-settings-fields', 'wf_watermark_image' );
            register_setting( 'wf-watermark-settings-fields', 'wf_watermark_position' );
            register_setting( 'wf-watermark-settings-fields', 'wf_watermark_opacity' );
    
            add_settings_section(
                'wf_watermark_section',
                __( 'Watermark Settings', 'wf-watermark' ),
                null,
                'wf-watermark-settings'
            );
            add_settings_field(
                'wf_watermark_image',
                __( 'Watermark Image URL', 'wf-watermark' ),
                array( $this, 'watermark_image_callback'),
                'wf-watermark-settings',
                'wf_watermark_section'
            );
            add_settings_field(
                'wf_watermark_position',
                __( 'Watermark Position', 'wf-watermark' ),
                array( $this, 'watermark_position_callback'),
                'wf-watermark-settings',
                'wf_watermark_section'
            );
            add_settings_field(
                'wf_watermark_opacity',
                __( 'Watermark Opacity (0-100)', 'wf-watermark' ),
                array( $this, 'watermark_opacity_callback'),
                'wf-watermark-settings',
                'wf_watermark_section'
            );
        }
    
        public function watermark_image_callback() {
            $watermark_image = esc_attr( get_option( 'wf_watermark_image', '' ) );?>   
            <input type="hidden" name="wf_watermark_image" id="wf_watermark_image" value="<?php echo esc_attr( $watermark_image ); ?>" />
            <?php if( $watermark_image ){?>
                <img id="watermark_image_preview" src="<?php echo esc_attr( $watermark_image ); ?>" style="max-width: 200px;" />
                <br />
                <?php
            } ?>
            <input type="button" class="button" id="upload_watermark_image_button" value="<?php echo __( 'Upload Image', 'wf-watermark' ); ?>" />
            <input type="button" class="button" id="remove_watermark_image_button" value="<?php echo __( 'Remove Image', 'wf-watermark' ); ?>" />
            <p><?php echo __( 'Please upload png image only.', 'wf-watermark' );?>
            <?php
        }
    
        public function watermark_position_callback() {
            $position = esc_attr( get_option( 'wf_watermark_position', 'center' ) );
            ?>
            <select name="wf_watermark_position">
                <option value="top-left" <?php selected($position, 'top-left'); ?>><?php echo __( 'Top Left', 'wf-watermark' ); ?></option>
                <option value="top-right" <?php selected($position, 'top-right'); ?>><?php echo __( 'Top Right', 'wf-watermark' ); ?></option>
                <option value="bottom-left" <?php selected($position, 'bottom-left'); ?>><?php echo __( 'Bottom Left', 'wf-watermark' ); ?></option>
                <option value="bottom-right" <?php selected($position, 'bottom-right'); ?>><?php echo __( 'Bottom Right', 'wf-watermark' ); ?></option>
                <option value="center" <?php selected($position, 'center'); ?>><?php echo __( 'Center', 'wf-watermark' ); ?></option>
            </select>
            <?php
        }
    
        public function watermark_opacity_callback() {
            $opacity = esc_attr( get_option( 'wf_watermark_opacity', 30 ) );?>
            <input type="number" name="wf_watermark_opacity" value="<?php echo $opacity ;?>" min="0" max="100" />
            <?php
        }
    }
    
    new Admin_settings();
}
