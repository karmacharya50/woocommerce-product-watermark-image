<?php
/**
 * Product Image watermark on save product post.
 *
 * @version 1.0.0
 * @author  WPFactory
 */

defined( 'ABSPATH' ) || exit;

if ( !class_exists('Product_Watermark_Image') ) {
	class Product_Watermark_Image {
	    
	    public function __construct() {
	        // Apply watermark to the product image on save or update
	        add_action( 'save_post_product', array($this, 'add_watermark_product_images'), 10, 3 );
	    }
	
	    // Add watermark to product images, gallery images, and variable product images
	    public function add_watermark_product_images( $post_id, $post, $update ) {
	        update_post_meta( $post_id, 'apply_watermark_image', 1 );
	        $this->process_product_images($post_id);
	        $product = wc_get_product($post_id);
	        if ($product->is_type('variable')) {
	            $this->process_variation_images($product);
	        }
	    }
	
	    // Process all product images for watermark
	    public function process_product_images( $post_id ) {
	        $featured_image_id = get_post_thumbnail_id( $post_id );
	        if ( $featured_image_id ) {
	            $this->apply_watermark_to_image_by_id( $featured_image_id );
	        }
	
	        $gallery_image_ids = get_post_meta( $post_id, '_product_image_gallery', true );
	        if ( $gallery_image_ids ) {
	            $gallery_image_ids = explode(',', $gallery_image_ids);
	            foreach ( $gallery_image_ids as $image_id ) {
	                $this->apply_watermark_to_image_by_id( $image_id );
	            }
	        }
	    }
	
	    // Process variable product images
	    public function process_variation_images( $product ) {
	        $variations = $product->get_children();
	        foreach ( $variations as $variation_id ) {
	            $variation_image_id = get_post_thumbnail_id( $variation_id );
	            if ( $variation_image_id ) {
	                $this->apply_watermark_to_image_by_id( $variation_image_id );
	            }
	        }
	    }
	
	    // Apply watermark to image by attachment ID
	    public function apply_watermark_to_image_by_id( $attachment_id ) {
	        $image_url = wp_get_attachment_url( $attachment_id );
	        $watermark_url = get_option( 'wf_watermark_image' );
	        $uploads = wp_upload_dir();
	        $watermark_path = str_replace( $uploads['baseurl'], $uploads['basedir'], $watermark_url );
	        
	        // Get position and opacity from settings
	        $position = get_option( 'wf_watermark_position', 'center' );
	        $opacity = get_option( 'wf_watermark_opacity', 30 );
	
	        if (file_exists( $watermark_path )) {
	            $this->add_watermark_to_image( $image_url, $watermark_path, $position, $opacity );
	        }
	    }
	
	    public function add_watermark_to_image( $image_url, $watermark_path, $position, $opacity ) {
	        $image_path = str_replace(home_url('/'), ABSPATH, $image_url);
	        if (!file_exists( $image_path )) {
	            return;
	        }
	        if($this->check_duplicate_image( $image_path )){
	            return;
	        }
	        
	        list( $img_width, $img_height, $img_type ) = getimagesize( $image_path );
	        $img = $this->create_image_from_type( $image_path, $img_type );
	        list( $watermark_width, $watermark_height, $watermark_img_type ) = getimagesize( $watermark_path );
	        if( $watermark_img_type != '3' ){
	            return; //watermark image is not png format
	        }
	        $watermark = imagecreatefrompng( $watermark_path );
	        // Backup product image
	        $this->backup_image( $image_path );
	        imagealphablending( $watermark, true );
	        imagesavealpha( $watermark, true );
	
	        // Calculate position for watermark
	        list( $x, $y ) = $this->get_watermark_position( $position, $img_width, $img_height, $watermark_width, $watermark_height );
	        imagecopymerge( $img, $watermark, $x, $y, 0, 0, $watermark_width, $watermark_height, $opacity );
	        $this->save_image_from_type( $img, $image_path, $img_type );
	        $this->generate_watermak_thumbnail_image( $image_url );
	        imagedestroy( $img );
	        imagedestroy( $watermark );
	    }
	
	    public function generate_watermak_thumbnail_image( $image_url ){
			$attachment_id = attachment_url_to_postid( $image_url );
			$fullsize_path = get_attached_file( $attachment_id );
			$meta_data = wp_get_attachment_metadata( $attachment_id );
			
			//Watermark image regenerate thumbnail size
			$sizes_list = array(
				'thumbnail',
				'medium',
				'medium_large',
				'large',
				'woocommerce_thumbnail',
				'woocommerce_single',
				'woocommerce_gallery_thumbnail',
				'shop_single'
			);
			if ( isset($meta_data['sizes']) ) {
				foreach ($meta_data['sizes'] as $size => $size_info) {
					if(in_array( $size,$sizes_list )){
						$resized_image = image_make_intermediate_size( $fullsize_path, $size_info['width'],	$size_info['height'], true );
					}
				}
			}
	    }
	
	    //Copy product image for backup and to check the watermark apply or not
	    public function backup_image( $image_path ) {
	        $file_name = basename( $image_path );
	        $path = str_replace( $file_name, '', $image_path );
	        $pattern = '/(\.\w+?$)/i';
	        $replacement = '_sk_backup$1';
	        $new_file_name = preg_replace( $pattern, $replacement, $file_name );
	        $new_path = $path.$new_file_name;
	        copy( $image_path, $new_path );
	    }
	
	    //Check for already watermark image check condition
	    public function check_duplicate_image( $image_path ){
	        $file_name = basename( $image_path );
	        $path = str_replace( $file_name, '', $image_path );
	        $pattern = '/(\.\w+?$)/i';
	        $replacement = '_sk_backup$1';
	        $new_file_name = preg_replace( $pattern, $replacement, $file_name );
	        if (file_exists( $path.$new_file_name) ) {
	            return 1;
	        }else{
	            return 0;
	        }
	    }
	
	    // Get watermark position
	    public function get_watermark_position( $position, $img_width, $img_height, $watermark_width, $watermark_height ) {
	        switch ( $position ) {
	            case 'top-left':
	                return [0, 0];
	            case 'top-right':
	                return [$img_width - $watermark_width, 0];
	            case 'bottom-left':
	                return [0, $img_height - $watermark_height];
	            case 'bottom-right':
	                return [$img_width - $watermark_width, $img_height - $watermark_height];
	            case 'center':
	            default:
	                return [( $img_width - $watermark_width )/2, ( $img_height - $watermark_height )/2];
	        }
	    }
	
	    // Create image from type
	    public function create_image_from_type( $path, $type ) {
	        switch ( $type ) {
	            case IMAGETYPE_JPEG:
	                return imagecreatefromjpeg( $path );
	            case IMAGETYPE_PNG:
	                return imagecreatefrompng( $path );
	            case IMAGETYPE_WEBP:
	                return imagecreatefromwebp( $path );    
	            default:
	                return false;
	        }
	    }
	
	    // Save image based on type
	    public function save_image_from_type( $image, $path, $type ) {
	        switch ( $type ) {
	            case IMAGETYPE_JPEG:
	                imagejpeg( $image, $path, 90 );
	                break;
	            case IMAGETYPE_PNG:
	                imagepng( $image, $path, 9 );
	                break;
	            case IMAGETYPE_WEBP:
	                imagewebp( $image, $path );
	                break;
	        }
	    }   
	}
	
	new Product_Watermark_Image();
}
