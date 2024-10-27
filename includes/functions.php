<?php 

//Generate Image watermark for old product image.
add_action( 'wp_ajax_nopriv_generate_watermark_product_image', 'generate_watermark_product_image' );
add_action( 'wp_ajax_generate_watermark_product_image', 'generate_watermark_product_image' );

function generate_watermark_product_image() {
    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => -1,
        'meta_query'     => array(
            array(
                'key'     => 'apply_watermark_image',
                'compare' => 'NOT EXISTS',
            ),
        ),
    );
    
    $query = new WP_Query( $args );
    if ( $query->have_posts() ) {
        $watermark_class = new Product_Watermark_Image();
        while ( $query->have_posts() ) {
            $query->the_post();
            $product_id = get_the_id();

             //Set the value to indicate if a watermark has been applied to the product
            update_post_meta($product_id, 'apply_watermark_image', 1);
            $watermark_class->process_product_images( $product_id );
          
            // Get variations and process their images
            $product = wc_get_product($product_id);
            if ($product->is_type('variable')) {
                $watermark_class->process_variation_images( $product );
            }            
        }
        wp_reset_postdata();
        $product[ 'stat' ] = 1;
    }else{
        $product[ 'stat' ] = 0;
    }
    wp_send_json($product);
    wp_die();
}
