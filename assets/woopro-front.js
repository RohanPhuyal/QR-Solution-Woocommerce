jQuery( function( $ ) {
    'use strict';
    
    console.log('woopro-front.js loaded');
    console.log('jQuery version:', $.fn.jquery);

    ;( function ( document, window, index ) {
        // feature detection for drag&drop upload
        var isAdvancedUpload = function() {
            var div = document.createElement( 'div' );
            return ( ( 'draggable' in div ) || ( 'ondragstart' in div && 'ondrop' in div ) ) && 'FormData' in window && 'FileReader' in window;
        }();

        // applying the effect for every form
        var forms = document.querySelectorAll( '.box' );
        Array.prototype.forEach.call( forms, function( form ) {
            var input        = form.querySelector( 'input[type="file"]' ),
                label        = form.querySelector( 'label' ),
                errorMsg     = form.querySelector( '.box__error span' ),
                restart      = form.querySelectorAll( '.box__restart' ),
                droppedFiles = false,
                showFiles    = function( files ) {
                    // Show filename
                    var filenameDiv = form.querySelector('.box__filename');
                    if( filenameDiv ) {
                        var filename = files.length > 1 ? ( input.getAttribute( 'data-multiple-caption' ) || '' ).replace( '{count}', files.length ) : files[ 0 ].name;
                        filenameDiv.textContent = 'Selected: ' + filename;
                        filenameDiv.style.marginTop = '15px';
                        filenameDiv.style.fontSize = '14px';
                        filenameDiv.style.color = '#333';
                        filenameDiv.style.fontWeight = 'bold';
                    }
                    
                    // Show image preview
                    var previewDiv = form.querySelector('.box__image-preview');
                    if( files.length > 0 && files[0].type.match('image.*') && previewDiv ) {
                        var reader = new FileReader();
                        reader.onload = function(e) {
                            var existingPreview = previewDiv.querySelector('img');
                            if( existingPreview ) {
                                existingPreview.src = e.target.result;
                            } else {
                                var preview = document.createElement('img');
                                preview.src = e.target.result;
                                preview.style.maxWidth = '250px';
                                preview.style.marginTop = '10px';
                                preview.style.display = 'block';
                                preview.style.border = '2px solid #ddd';
                                preview.style.borderRadius = '5px';
                                preview.style.padding = '5px';
                                previewDiv.appendChild(preview);
                            }
                        };
                        reader.readAsDataURL(files[0]);
                    }
                },
                triggerFormSubmit = function() {
                    var event = document.createEvent( 'HTMLEvents' );
                    event.initEvent( 'submit', true, false );
                    form.dispatchEvent( event );
                };

            // letting the server side to know we are going to make an Ajax request
            var ajaxFlag = document.createElement( 'input' );
            ajaxFlag.setAttribute( 'type', 'hidden' );
            ajaxFlag.setAttribute( 'name', 'ajax' );
            ajaxFlag.setAttribute( 'value', 1 );
            form.appendChild( ajaxFlag );

            // automatically submit the form on file select
            input.addEventListener( 'change', function( e ) {
                console.log('File selected:', e.target.files);
                showFiles( e.target.files );
                
                // Also update the preview directly
                if( e.target.files.length > 0 ) {
                    var file = e.target.files[0];
                    
                    // Update filename
                    var filenameDiv = document.querySelector('.box__filename');
                    if( filenameDiv ) {
                        filenameDiv.textContent = 'Selected: ' + file.name;
                        filenameDiv.style.marginTop = '15px';
                        filenameDiv.style.fontSize = '14px';
                        filenameDiv.style.color = '#333';
                        filenameDiv.style.fontWeight = 'bold';
                    }
                    
                    // Update image preview
                    if( file.type.match('image.*') ) {
                        var reader = new FileReader();
                        reader.onload = function(event) {
                            var previewDiv = document.querySelector('.box__image-preview');
                            if( previewDiv ) {
                                previewDiv.innerHTML = '';
                                var img = document.createElement('img');
                                img.src = event.target.result;
                                img.style.maxWidth = '250px';
                                img.style.marginTop = '10px';
                                img.style.display = 'block';
                                img.style.border = '2px solid #ddd';
                                img.style.borderRadius = '5px';
                                img.style.padding = '5px';
                                previewDiv.appendChild(img);
                            }
                        };
                        reader.readAsDataURL(file);
                    }
                }
            });

            // drag&drop files if the feature is available
            if( isAdvancedUpload ) {
                form.classList.add( 'has-advanced-upload' ); // letting the CSS part to know drag&drop is supported by the browser

                [ 'drag', 'dragstart', 'dragend', 'dragover', 'dragenter', 'dragleave', 'drop' ].forEach( function( event ) {
                    form.addEventListener( event, function( e ) {
                        // preventing the unwanted behaviours
                        e.preventDefault();
                        e.stopPropagation();
                    });
                });
                [ 'dragover', 'dragenter' ].forEach( function( event ) {
                    form.addEventListener( event, function() {
                        form.classList.add( 'is-dragover' );
                    });
                });
                [ 'dragleave', 'dragend', 'drop' ].forEach( function( event ) {
                    form.addEventListener( event, function() {
                        form.classList.remove( 'is-dragover' );
                    });
                });
                form.addEventListener( 'drop', function( e ) {
                    e.dataTransfer.clearData();
                    droppedFiles = e.dataTransfer.files; // the files that were dropped
                    showFiles( droppedFiles );
                });
            }

            $( 'body' ).on( 'click', '.finalized_order', function( e ) {
                $('.popup-wrapper .loader').css('display','block');
                var ajaxData = new FormData( form );
                
                // Add selected QR option name
                var selectedOption = $( '.selected-qr-option-input' ).val();
                if( selectedOption ) {
                    ajaxData.append( 'selected_qr_option', selectedOption );
                }
                
                if( typeof $( 'input[name=files]' )[0].files[0] != 'undefined' ) {

                    ajaxData.append( 'image', $( 'input[name=files]' )[0].files[0] );
                    if( typeof $( 'input[name=files]' )[0].files[0] == 'undefined' ){
                        $( '.popup-wrapper .error' ).show().delay( 2000 ).slideUp();
                        return false;
                    }
                } else if( droppedFiles ) {

                    Array.prototype.forEach.call( droppedFiles, function( file ) {
                        ajaxData.append( 'image', file );
                    });

                    if( typeof file == 'undefined' ) {
                        $( '.popup-wrapper .error' ).show().delay( 2000 ).slideUp();
                        return false;
                    }
                }
                
                ajaxData.append( 'action', 'kwp_yape_peru_qr_code' );

                $.ajax({
                    url:kwajaxurl.ajaxurl,
                    type:'POST',
                    processData: false,
                    contentType: false,
                    data: ajaxData,
                    success: function( response ) {
                        if( response == 'yes' ){
                            $('.place-order button, #place_order').removeClass('yape_peru');
                            $('.popup-wrapper').hide();
                            $('.place-order button, #place_order').trigger('click');
                        } else {
                            $("form.box")[0].reset();
                            $('.popup-wrapper .error').show().html(kwp_translate.kwp_pqr_upload_images).delay(2000).slideUp();
                        }
                        $('.popup-wrapper .loader').css('display','none');
                    }
                });
            });

            // if the form was submitted
            form.addEventListener( 'submit', function( e ) {
                // preventing the duplicate submissions if the current one is in progress
                if( form.classList.contains( 'is-uploading' ) ) return false;

                form.classList.add( 'is-uploading' );
                form.classList.remove( 'is-error' );

                if( isAdvancedUpload ) {
                    e.preventDefault();

                    // gathering the form data
                    var ajaxData = new FormData( form );
                    if( droppedFiles ) {
                        Array.prototype.forEach.call( droppedFiles, function( file ) {
                            ajaxData.append( input.getAttribute( 'name' ), file );
                        });
                    }

                    // ajax request
                    var ajax = new XMLHttpRequest();
                    ajax.open( form.getAttribute( 'method' ), form.getAttribute( 'action' ), true );

                    ajax.onload = function() {
                        form.classList.remove( 'is-uploading' );
                        if( ajax.status >= 200 && ajax.status < 400 ) {
                            var data = JSON.parse( ajax.responseText );
                            form.classList.add( data.success == true ? 'is-success' : 'is-error' );
                            if( !data.success ) errorMsg.textContent = data.error;
                        } 
                        else alert( 'Error. Please, contact the webmaster!' );
                    };

                    ajax.onerror = function() {
                        form.classList.remove( 'is-uploading' );
                        alert( 'Error. Please, try again!' );
                    };

                    ajax.send( ajaxData );
                }
                else // fallback Ajax solution upload for older browsers
                {
                    var iframeName  = 'uploadiframe' + new Date().getTime(),
                        iframe      = document.createElement( 'iframe' );

                        $iframe     = $( '<iframe name="' + iframeName + '" style="display: none;"></iframe>' );

                    iframe.setAttribute( 'name', iframeName );
                    iframe.style.display = 'none';

                    document.body.appendChild( iframe );
                    form.setAttribute( 'target', iframeName );

                    iframe.addEventListener( 'load', function() {
                        var data = JSON.parse( iframe.contentDocument.body.innerHTML );
                        form.classList.remove( 'is-uploading' )
                        form.classList.add( data.success == true ? 'is-success' : 'is-error' )
                        form.removeAttribute( 'target' );
                        if( !data.success ) errorMsg.textContent = data.error;
                        iframe.parentNode.removeChild( iframe );
                    });
                }
            });

            // restart the form if has a state of error/success
            Array.prototype.forEach.call( restart, function( entry ) {
                entry.addEventListener( 'click', function( e ) {
                    e.preventDefault();
                    form.classList.remove( 'is-error', 'is-success' );
                    input.click();
                });
            });

            // Firefox focus bug fix for file input
            input.addEventListener( 'focus', function(){ input.classList.add( 'has-focus' ); });
            input.addEventListener( 'blur', function(){ input.classList.remove( 'has-focus' ); });

        });
    }( document, window, 0 ));

    $( document ).ajaxComplete( function( event, request, options ) {
        var selectedValue = $( 'form.checkout .wc_payment_methods input[name^="payment_method"]:checked' ).val();
        if ( selectedValue == 'wocommerce_yape_peru' ) {
            $( '.place-order button, #place_order' ).addClass( 'yape_peru' );
        }
    });

    $( 'form.checkout' ).on( 'change', 'input[name^="payment_method"]', function() {
        var choosenPaymentMethod = $( 'input[name^="payment_method"]:checked' ).val();
        if( choosenPaymentMethod == 'wocommerce_yape_peru' ){
            $( '.place-order button, #place_order' ).addClass( 'yape_peru' );
        } else { 
            $( '.place-order button, #place_order' ).removeClass( 'yape_peru' );
        }
    });

    $( 'body' ).on( 'click', '.yape_peru', function(e) {
        e.preventDefault();
        
        // Get selected payment option from checkout
        var selectedIndex = $( 'input[name="kwp_selected_qr_option"]:checked' ).val();
        if( typeof selectedIndex === 'undefined' ) {
            selectedIndex = 0; // Default to first option
        }
        
        console.log('Selected index from checkout:', selectedIndex);
        
        // Update popup selector active state to match checkout selection
        $( '.kwp-popup-option-item' ).removeClass( 'active' );
        $( '.kwp-popup-option-item[data-index="' + selectedIndex + '"]' ).addClass( 'active' );
        
        // Load data from the corresponding hidden div
        var $selectedOption = $( '.kwp-qr-data-item[data-index="' + selectedIndex + '"]' );
        
        if( $selectedOption.length ) {
            var qrImage = $selectedOption.data( 'qr-image' );
            var phone = $selectedOption.data( 'phone' );
            var limit = $selectedOption.data( 'limit' );
            var limitMessage = $selectedOption.data( 'limit-message' );
            var optionName = $selectedOption.data( 'option-name' );
            var popupDescription = $selectedOption.data( 'popup-description' );
            
            // Update QR image
            $( '.popup-qr' ).attr( 'src', qrImage );
            
            // Update popup description
            if( popupDescription ) {
                if( $( '.kwp-qr-display p.popup-description' ).length ) {
                    $( '.kwp-qr-display p.popup-description' ).text( popupDescription );
                } else {
                    $( '.kwp-qr-display .message-limit-amount' ).after( '<p class="popup-description">' + popupDescription + '</p>' );
                }
            } else {
                $( '.kwp-qr-display p.popup-description' ).remove();
            }
            
            // Update phone number
            if( phone ) {
                if( $( '.telephone-number' ).length ) {
                    $( '.telephone-number a' ).attr( 'href', 'tel:' + phone ).text( 'Add Contact: ' + phone );
                    $( '.telephone-number' ).show();
                } else {
                    $( '.popup-qr' ).after( '<span class="telephone-number"><a href="tel:' + phone + '">Add Contact: ' + phone + '</a></span>' );
                }
            } else {
                $( '.telephone-number' ).hide();
            }
            
            // Update limit
            $( '.popup-price-wrapper' ).data( 'price-limit', limit );
            
            // Update limit message
            if( limitMessage ) {
                if( $( '.message-limit-amount' ).length ) {
                    $( '.message-limit-amount' ).text( limitMessage );
                } else {
                    $( '.price' ).after( '<p class="message-limit-amount" style="display: none;">' + limitMessage + '</p>' );
                }
            }
            
            // Update selected option input
            $( '.selected-qr-option-input' ).val( optionName );
        }
        
        $( '.popup-wrapper' ).show();
        $( '.first-step .woocommerce-Price-amount' ).remove();

        // Get price limit from selected option
        var priceLimit = $( '.popup-price-wrapper' ).data( 'price-limit' );
        if( priceLimit ) {
            $( '.first-step .popup-price-wrapper' ).append( $( '.order-total .woocommerce-Price-amount' ).first().clone() );
            $( '.first-step .popup-price-wrapper .woocommerce-Price-currencySymbol' ).remove();
            var getPrice = $( '.first-step .popup-price-wrapper' ).text();
            if( getPrice ) {
                if( parseFloat( priceLimit ) < parseFloat( getPrice ) && parseFloat( priceLimit ) != parseFloat( getPrice ) ) {
                    $( '.first-step .message-limit-amount' ).show();
                    $( '.first-step .btn-continue' ).remove();
                } else {
                    $( '.first-step .message-limit-amount' ).hide();
                    $( '.first-step .btn-continue' ).remove();
                    $( '.first-step' ).append( '<button class="btn-continue btn_submit">'+kwp_translate.kwp_pqr_btn_continue+'</button>' );
                }
            }
        } else {
            $( '.first-step .btn-continue' ).remove();
            $( '.first-step' ).append( '<button class="btn-continue btn_submit">'+kwp_translate.kwp_pqr_btn_continue+'</button>' );
        }
        $( '.first-step .price' ).append( $( '.order-total .woocommerce-Price-amount' ).first().clone() );
    });

    // Handle checkout QR option selection
    $( 'body' ).on( 'click', '.kwp-checkout-qr-option', function() {
        var $this = $( this );
        
        // Update active state
        $( '.kwp-checkout-qr-option' ).removeClass( 'active' );
        $this.addClass( 'active' );
        
        // Check the radio button
        $this.find( 'input[type="radio"]' ).prop( 'checked', true ).trigger( 'change' );
    });
    
    // Handle popup QR option selection
    $( 'body' ).on( 'click', '.kwp-popup-option-item', function() {
        var $this = $( this );
        var selectedIndex = $this.data( 'index' );
        
        console.log('Popup option clicked, index:', selectedIndex);
        
        // Update active state
        $( '.kwp-popup-option-item' ).removeClass( 'active' );
        $this.addClass( 'active' );
        
        // Load data from the corresponding hidden div
        var $selectedOption = $( '.kwp-qr-data-item[data-index="' + selectedIndex + '"]' );
        
        if( $selectedOption.length ) {
            var qrImage = $selectedOption.data( 'qr-image' );
            var phone = $selectedOption.data( 'phone' );
            var limit = $selectedOption.data( 'limit' );
            var limitMessage = $selectedOption.data( 'limit-message' );
            var optionName = $selectedOption.data( 'option-name' );
            var popupDescription = $selectedOption.data( 'popup-description' );
            
            console.log('Updating popup with:', optionName);
            
            // Update QR image
            $( '.popup-qr' ).attr( 'src', qrImage );
            
            // Update popup description
            if( popupDescription ) {
                if( $( '.kwp-qr-display p.popup-description' ).length ) {
                    $( '.kwp-qr-display p.popup-description' ).text( popupDescription );
                } else {
                    $( '.kwp-qr-display .message-limit-amount' ).after( '<p class="popup-description">' + popupDescription + '</p>' );
                }
            } else {
                $( '.kwp-qr-display p.popup-description' ).remove();
            }
            
            // Update phone number
            if( phone ) {
                if( $( '.telephone-number' ).length ) {
                    $( '.telephone-number a' ).attr( 'href', 'tel:' + phone ).text( 'Add Contact: ' + phone );
                    $( '.telephone-number' ).show();
                } else {
                    $( '.popup-qr' ).after( '<span class="telephone-number"><a href="tel:' + phone + '">Add Contact: ' + phone + '</a></span>' );
                }
            } else {
                $( '.telephone-number' ).hide();
            }
            
            // Update limit
            $( '.popup-price-wrapper' ).data( 'price-limit', limit );
            
            // Update limit message
            if( limitMessage ) {
                if( $( '.message-limit-amount' ).length ) {
                    $( '.message-limit-amount' ).text( limitMessage );
                } else {
                    $( '.price' ).after( '<p class="message-limit-amount" style="display: none;">' + limitMessage + '</p>' );
                }
            }
            
            // Update selected option input
            $( '.selected-qr-option-input' ).val( optionName );
            
            // Re-check price limit
            var priceLimit = limit;
            if( priceLimit ) {
                $( '.first-step .popup-price-wrapper' ).empty().append( $( '.order-total .woocommerce-Price-amount' ).first().clone() );
                $( '.first-step .popup-price-wrapper .woocommerce-Price-currencySymbol' ).remove();
                var getPrice = $( '.first-step .popup-price-wrapper' ).text();
                if( getPrice ) {
                    if( parseFloat( priceLimit ) < parseFloat( getPrice ) && parseFloat( priceLimit ) != parseFloat( getPrice ) ) {
                        $( '.first-step .message-limit-amount' ).show();
                        $( '.first-step .btn-continue' ).remove();
                    } else {
                        $( '.first-step .message-limit-amount' ).hide();
                        if( !$( '.first-step .btn-continue' ).length ) {
                            $( '.first-step' ).append( '<button class="btn-continue btn_submit">' + kwp_translate.kwp_pqr_btn_continue + '</button>' );
                        }
                    }
                }
            } else {
                $( '.first-step .message-limit-amount' ).hide();
                if( !$( '.first-step .btn-continue' ).length ) {
                    $( '.first-step' ).append( '<button class="btn-continue btn_submit">' + kwp_translate.kwp_pqr_btn_continue + '</button>' );
                }
            }
        }
    });

    // Handle QR option selection
    $( 'body' ).on( 'click', '.kwp-qr-option-item', function() {
        var $this = $( this );
        
        // Update active state
        $( '.kwp-qr-option-item' ).removeClass( 'active' );
        $this.addClass( 'active' );
        
        // Get option data
        var qrImage = $this.data( 'qr-image' );
        var phone = $this.data( 'phone' );
        var limit = $this.data( 'limit' );
        var limitMessage = $this.data( 'limit-message' );
        var optionName = $this.data( 'option-name' );
        
        // Update QR image
        $( '.popup-qr' ).attr( 'src', qrImage );
        
        // Update phone number
        if( phone ) {
            if( $( '.telephone-number' ).length ) {
                $( '.telephone-number a' ).attr( 'href', 'tel:' + phone ).text( 'Add Contact: ' + phone );
                $( '.telephone-number' ).show();
            } else {
                $( '.popup-qr' ).after( '<span class=\"telephone-number\"><a href=\"tel:' + phone + '\">Add Contact: ' + phone + '</a></span>' );
            }
        } else {
            $( '.telephone-number' ).hide();
        }
        
        // Update limit
        $( '.popup-price-wrapper' ).data( 'price-limit', limit );
        
        // Update limit message
        if( limitMessage ) {
            if( $( '.message-limit-amount' ).length ) {
                $( '.message-limit-amount' ).text( limitMessage );
            } else {
                $( '.price' ).after( '<p class=\"message-limit-amount\" style=\"display: none;\">' + limitMessage + '</p>' );
            }
        }
        
        // Update selected option input
        $( '.selected-qr-option-input' ).val( optionName );
        
        // Re-check price limit
        var priceLimit = limit;
        if( priceLimit ) {
            $( '.first-step .popup-price-wrapper' ).empty().append( $( '.order-total .woocommerce-Price-amount' ).first().clone() );
            $( '.first-step .popup-price-wrapper .woocommerce-Price-currencySymbol' ).remove();
            var getPrice = $( '.first-step .popup-price-wrapper' ).text();
            if( getPrice ) {
                if( parseFloat( priceLimit ) < parseFloat( getPrice ) && parseFloat( priceLimit ) != parseFloat( getPrice ) ) {
                    $( '.first-step .message-limit-amount' ).show();
                    $( '.first-step .btn-continue' ).remove();
                } else {
                    $( '.first-step .message-limit-amount' ).hide();
                    if( !$( '.first-step .btn-continue' ).length ) {
                        $( '.first-step' ).append( '<button class=\"btn-continue btn_submit\">' + kwp_translate.kwp_pqr_btn_continue + '</button>' );
                    }
                }
            }
        } else {
            $( '.first-step .message-limit-amount' ).hide();
            if( !$( '.first-step .btn-continue' ).length ) {
                $( '.first-step' ).append( '<button class=\"btn-continue btn_submit\">' + kwp_translate.kwp_pqr_btn_continue + '</button>' );
            }
        }
    });

    $( '.popupCloseButton' ).click(function(){
        $( '.second-step' ).css( 'display','none' );
        $( '.popup-wrapper .error' ).css( 'display','none' );
        $( '.first-step' ).css( 'display','block' );
        $( '.popup-wrapper' ).hide();
    });

    $( '.first-step' ).on( 'click', '.btn-continue', function (){
        $( '.second-step' ).show();
        $( '.first-step' ).hide();
    });
    
    // Use body delegation for dynamically loaded content
    $( 'body' ).on('click', '.box__button', function(e){
        e.preventDefault();
        console.log('Box button clicked (body delegation)');
        $( '.box__file' ).trigger( 'click' );
    });
    
    // Handle file selection and show preview
    $( document ).on( 'change', '.box__file', function(e) {
        console.log('Change event triggered on .box__file');
        var file = this.files[0];
        console.log('File object:', file);
        console.log('File name:', file ? file.name : 'No file');
        console.log('.box__filename element:', $( '.box__filename' ).length);
        console.log('.box__image-preview element:', $( '.box__image-preview' ).length);
        
        if( file ) {
            // Update filename
            var filenameDiv = $( '.box__filename' );
            console.log('Setting filename to:', 'Selected: ' + file.name);
            filenameDiv.text( 'Selected: ' + file.name ).css({
                'margin-top': '15px',
                'font-size': '14px',
                'color': '#333',
                'font-weight': 'bold',
                'display': 'block'
            });
            console.log('Filename div text after set:', filenameDiv.text());
            
            // Update image preview
            if( file.type.match('image.*') ) {
                console.log('File is an image, creating preview');
                var reader = new FileReader();
                reader.onload = function(event) {
                    console.log('FileReader loaded, creating img element');
                    var img = $('<img>').attr('src', event.target.result).css({
                        'max-width': '200px',
                        'max-height': '200px',
                        'width': 'auto',
                        'height': 'auto',
                        'margin': '10px auto',
                        'display': 'block',
                        'border': '2px solid #ddd',
                        'border-radius': '5px',
                        'padding': '5px',
                        'object-fit': 'contain'
                    });
                    var previewDiv = $( '.box__image-preview' );
                    previewDiv.html(img);
                    console.log('Image added to preview div');
                };
                reader.readAsDataURL(file);
            } else {
                console.log('File is not an image, type:', file.type);
            }
        } else {
            console.log('No file selected');
        }
    });
    
    // Listen to WooCommerce checkout updates
    $( document.body ).on( 'updated_checkout', function() {
        console.log('WooCommerce checkout updated');
    });

});