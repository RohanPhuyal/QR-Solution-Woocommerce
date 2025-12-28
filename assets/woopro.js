jQuery( function( $ ) {
 
    $( document ).on( 'click', '.kwp_upload_icon_button', function( e ) {
        e.preventDefault();
  
        var button = $( this ),
        aw_uploader = wp.media({
            title: 'Custom icon',
            library : {
                uploadedTo : wp.media.view.settings.post.id, 
                type : 'image'
            },
            button: {
                text: 'Use this icon'
            },
            multiple: false
        }).on( 'select', function() {
            var attachment = aw_uploader.state().get( 'selection' ).first().toJSON();
            $( '.preview_icon_area' ).html('<img src="'+attachment.url+'" class="upload_icon" style="display: block; padding: 10px; width: 200px;border: 1px solid;margin-bottom: 10px;"><button class="remove_icon button-secondary" type="button">Remove</button>');
            $( '.kwp_preview_icon' ).val( attachment.url );
        })
        .open();
    });

    $( document ).on( 'click', '.kwp_upload_image_button', function( e ) {
        e.preventDefault();
  
        var button = $( this ),
        aw_uploader = wp.media({
            title: 'Custom image',
            library : {
                uploadedTo : wp.media.view.settings.post.id, 
                type : 'image'
            },
            button: {
                text: 'Use this image'
            },
            multiple: false
        }).on( 'select', function() {
            var attachment = aw_uploader.state().get( 'selection' ).first().toJSON();
            $( '.preview_area' ).html('<img src="'+attachment.url+'" class="upload_qr" style="display: block; padding: 10px; width: 200px;border: 1px solid;margin-bottom: 10px;"><button class="remove_qr button-secondary" type="button">Remove</button>');
            $( '.kwp_preview_qr' ).val( attachment.url );
        })
        .open();
    });

    $( document ).on( 'click', '.remove_icon', function( e ) {
        e.preventDefault();
        $( '.upload_icon' ).remove();
        $( '.kwp_preview_icon' ).val('');
        $( this ).remove();
    });

    $( document ).on( 'click', '.remove_qr', function( e ) {
        e.preventDefault();
        $( '.upload_qr' ).remove();
        $( '.kwp_preview_qr' ).val('');
        $( this ).remove();
    });

    // Add new QR option row
    $( document ).on( 'click', '.kwp-add-qr-option', function( e ) {
        e.preventDefault();
        var container = $( '.kwp-qr-options-container' );
        var newIndex = container.find( '.kwp-qr-option-row' ).length;
        
        var newRow = `
            <div class="kwp-qr-option-row" data-index="${newIndex}">
                <div class="kwp-qr-option-header">
                    <h4>Payment Option #${newIndex + 1}</h4>
                    <button type="button" class="button button-link-delete kwp-remove-qr-option">Remove</button>
                </div>
                <div class="kwp-qr-option-fields">
                    <p>
                        <label>Option Name (e.g., Yape, Plin, Bank)</label>
                        <input type="text" name="woocommerce_wocommerce_yape_peru_qr_options[${newIndex}][name]" value="" placeholder="E.g., Yape" class="kwp-option-name" />
                    </p>
                    <p>
                        <label>Icon Image</label>
                        <input type="hidden" name="woocommerce_wocommerce_yape_peru_qr_options[${newIndex}][icon_image]" value="" class="kwp-option-icon-url" />
                        <button type="button" class="button button-secondary kwp-upload-option-icon">Select Icon</button>
                        <div class="kwp-option-icon-preview" style="display: none;"></div>
                    </p>
                    <p>
                        <label>QR Code Image</label>
                        <input type="hidden" name="woocommerce_wocommerce_yape_peru_qr_options[${newIndex}][qr_image]" value="" class="kwp-option-qr-url" />
                        <button type="button" class="button button-secondary kwp-upload-option-qr">Select QR Code</button>
                        <div class="kwp-option-qr-preview" style="display: none;"></div>
                    </p>
                    <p>
                        <label>Phone Number (Optional)</label>
                        <input type="text" name="woocommerce_wocommerce_yape_peru_qr_options[${newIndex}][phone_number]" value="" placeholder="E.g., +51 999 999 999" />
                    </p>
                    <p>
                        <label>Limit Amount (Optional)</label>
                        <input type="text" name="woocommerce_wocommerce_yape_peru_qr_options[${newIndex}][limit_amount]" value="" placeholder="E.g., 500" />
                    </p>
                    <p>
                        <label>Limit Amount Message (Optional)</label>
                        <input type="text" name="woocommerce_wocommerce_yape_peru_qr_options[${newIndex}][limit_message]" value="" placeholder="E.g., Maximum 500 per day" />
                    </p>
                </div>
            </div>
        `;
        
        container.append( newRow );
        updateOptionNumbers();
    });

    // Remove QR option row
    $( document ).on( 'click', '.kwp-remove-qr-option', function( e ) {
        e.preventDefault();
        if( $( '.kwp-qr-option-row' ).length > 1 ) {
            $( this ).closest( '.kwp-qr-option-row' ).remove();
            reindexOptions();
            updateOptionNumbers();
        } else {
            alert( 'You must have at least one payment option.' );
        }
    });

    // Upload icon for specific QR option
    $( document ).on( 'click', '.kwp-upload-option-icon', function( e ) {
        e.preventDefault();
        var button = $( this );
        var row = button.closest( '.kwp-qr-option-row' );
        
        var aw_uploader = wp.media({
            title: 'Select Icon',
            library: {
                type: 'image'
            },
            button: {
                text: 'Use this icon'
            },
            multiple: false
        }).on( 'select', function() {
            var attachment = aw_uploader.state().get( 'selection' ).first().toJSON();
            row.find( '.kwp-option-icon-url' ).val( attachment.url );
            row.find( '.kwp-option-icon-preview' ).html(
                '<img src="' + attachment.url + '" style="max-width: 80px; display: block; margin-top: 5px;" />' +
                '<button type="button" class="button button-link-delete kwp-remove-option-icon">Remove Icon</button>'
            ).show();
        }).open();
    });

    // Upload QR for specific QR option
    $( document ).on( 'click', '.kwp-upload-option-qr', function( e ) {
        e.preventDefault();
        var button = $( this );
        var row = button.closest( '.kwp-qr-option-row' );
        
        var aw_uploader = wp.media({
            title: 'Select QR Code',
            library: {
                type: 'image'
            },
            button: {
                text: 'Use this QR code'
            },
            multiple: false
        }).on( 'select', function() {
            var attachment = aw_uploader.state().get( 'selection' ).first().toJSON();
            row.find( '.kwp-option-qr-url' ).val( attachment.url );
            row.find( '.kwp-option-qr-preview' ).html(
                '<img src="' + attachment.url + '" style="max-width: 150px; display: block; margin-top: 5px;" />' +
                '<button type="button" class="button button-link-delete kwp-remove-option-qr">Remove QR</button>'
            ).show();
        }).open();
    });

    // Remove icon from specific QR option
    $( document ).on( 'click', '.kwp-remove-option-icon', function( e ) {
        e.preventDefault();
        var row = $( this ).closest( '.kwp-qr-option-row' );
        row.find( '.kwp-option-icon-url' ).val( '' );
        row.find( '.kwp-option-icon-preview' ).html( '' ).hide();
    });

    // Remove QR from specific QR option
    $( document ).on( 'click', '.kwp-remove-option-qr', function( e ) {
        e.preventDefault();
        var row = $( this ).closest( '.kwp-qr-option-row' );
        row.find( '.kwp-option-qr-url' ).val( '' );
        row.find( '.kwp-option-qr-preview' ).html( '' ).hide();
    });

    // Reindex options after removal
    function reindexOptions() {
        $( '.kwp-qr-option-row' ).each( function( index ) {
            var row = $( this );
            row.attr( 'data-index', index );
            
            // Update all field names
            row.find( 'input, select, textarea' ).each( function() {
                var field = $( this );
                var name = field.attr( 'name' );
                if( name ) {
                    var newName = name.replace( /\[\d+\]/, '[' + index + ']' );
                    field.attr( 'name', newName );
                }
            });
        });
    }

    // Update option numbers in headers
    function updateOptionNumbers() {
        $( '.kwp-qr-option-row' ).each( function( index ) {
            $( this ).find( '.kwp-qr-option-header h4' ).text( 'Payment Option #' + ( index + 1 ) );
        });
    }
});
