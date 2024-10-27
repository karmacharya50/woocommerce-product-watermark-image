jQuery(document).ready(function ($) {
    $( '#upload_watermark_image_button' ).on( 'click', function (e) {
        e.preventDefault();
        var file_frame;
        // If the media frame already exists, reopen it.
        if ( file_frame ) {
            file_frame.open();
            return;
        }

        // Create a new media frame
        file_frame = wp.media({
            title: 'Select Watermark Image',
            button: {
                text: 'Watermark Image',
            },
            multiple: false,
        });

        // When an image is selected, run a callback.
        file_frame.on('select', function () {
            var attachment = file_frame.state().get('selection').first().toJSON();
            $('input[name="wf_watermark_image"]').val(attachment.url);
        });
        file_frame.open();
    });

    $( '#remove_watermark_image_button' ).on( 'click', function(e) {
        e.preventDefault();
        $('#wf_watermark_image').val('');
        $('#watermark_image_preview').hide();
        $(this).hide();
    });

    //Ajax call to generate watermak product image
    $( '#generate-watermark-old-product-image' ).on( 'click', function(){
        $( '.generator_status' ).remove();
        $.ajax({
            type: 'POST',            
            url: ajaxurl,
            data: {
                action: 'generate_watermark_product_image'
            },
            beforeSend: function (response) {
                $('#generate-watermark-old-product-image').after('<div class="loading_class_generator">loading...</div>');
            },
            success: function ( response ) {
                $( '.loading_class_generator' ).remove();
                if( response.stat == 1 ){
                    $( '#generate-watermark-old-product-image' ).after('<div class="generator_status">Product image watermark successfully</div>');
                }else{
                    $( '#generate-watermark-old-product-image' ).after('<div class="generator_status">No old Product found.</div>');
                }
            },
        });        
    });
});
