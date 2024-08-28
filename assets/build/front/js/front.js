/**
 * Front End JS file.
 */

"use strict";
var wkmp = jQuery.noConflict();

document.addEventListener("DOMContentLoaded", function () {
	if (wkmp('.wkmp-select2').length) {
		wkmp('.wkmp-select2').select2();
	}
	if (wkmp("#mp_seller_product_categories").length) {
		wkmp("#mp_seller_product_categories").select2();
		wkmp('.wc-product-search').select2();
	}

	if (wkmp('#new_zone_locations').length) {
		wkmp('#new_zone_locations').select2()
    }
});

// Window.load started.
wkmp(window).on('load', function () {
	wkmp('.wkmp-add-product-form .select2-container').css('width', '100%');
	wkmp('.wkmp_nav_tabs a').css('text-decoration', 'none');
    wkmp('.woocommerce-pagination a').css('text-decoration', 'none');

    //Allowing media upload on by seller.
    if (wp.hasOwnProperty('media')) {
        wp.media.model.settings.post.id = 0;
    }

    //Remove link from separate dashboard link. And click via js
    if (wkmp('.woocommerce-MyAccount-navigation .woocommerce-MyAccount-navigation-link').length > 0) {
        if (undefined !== wkmpObj.mkt_tr.separate_dashboard) {
            if (wkmp('.woocommerce-MyAccount-navigation-link--'+wkmpObj.mkt_tr.separate_dashboard).length > 0) {
                wkmp('.woocommerce-MyAccount-navigation-link--' + wkmpObj.mkt_tr.separate_dashboard + ' a').attr('href', 'javascript:void(0);');

                wkmp('.woocommerce-MyAccount-navigation-link--'+wkmpObj.mkt_tr.separate_dashboard+' a').on('click', function (eve) {
                    eve.preventDefault();
                    wkmp('.woocommerce-MyAccount-navigation-link--separate-dashboard').addClass('loading');
            		wkmp.ajax({
            			type: 'POST',
            			url: wkmpObj.ajax.ajaxUrl,
            			data: {
            				"action": "wkmp_change_frontend_seller_dashboard",
            				"change_to": 'backend_dashboard',
            				"wkmp_nonce": wkmpObj.ajax.ajaxNonce
            			},
            			success: function (data) {
            				if (data) {
            					window.location.href = data.redirect;
            				}
            			}
            		})
            	});
            }
        }
    }

    //Handle stock management.
	wkmp(document).on('click', '#wk_stock_management', function () {
		if (wkmp(this).is(':checked')) {
			wkmp('.wkmp_profile_input #wk-mp-stock-qty').parent('.wkmp_profile_input').css('display', 'block');
			wkmp('.wkmp_profile_input #_backorders').parent('.wkmp_profile_input').css('display', 'block');
            wkmp('.wkmp_profile_input #wk-mp-stock-threshold').parent('.wkmp_profile_input').css('display', 'block');
            wkmp('.wkmp_profile_input #_stock_status').parent('.wkmp_profile_input').css('display', 'none')
		} else {
			wkmp('.wkmp_profile_input #wk-mp-stock-qty').parent('.wkmp_profile_input').css('display', 'none');
			wkmp('.wkmp_profile_input #_backorders').parent('.wkmp_profile_input').css('display', 'none');
            wkmp('.wkmp_profile_input #wk-mp-stock-threshold').parent('.wkmp_profile_input').css('display', 'none');
            wkmp('.wkmp_profile_input #_stock_status').parent('.wkmp_profile_input').css('display', 'block')
		}
    });

	// Seller review box.
    wkmp('.mp-avg-rating-box-link').on('click', function (event) {
        event.stopPropagation();
		if (wkmp(event.target).hasClass('mp-avg-rating-box-link')) {
			wkmp('.mp-avg-rating-box').toggle();
			wkmp(this).toggleClass('open')
		}
    });

    //Hide/open ratings.
    wkmp('body').on('click', function (event) {
        if (wkmp('.mp-avg-rating-box-link').hasClass('open')) {
            wkmp('.mp-avg-rating-box-link').removeClass('open');
            wkmp('.mp-avg-rating-box').toggle();
        }
    });

	wkmp('body').on('click', '.mp-seller-review-form p.mp-star-rating a', function () {
		var feedType = wkmp(this).data('type');
		var $star = wkmp(this),
			$rating = wkmp(this).closest('.mp-star-rating').siblings('#feed-' + feedType + '-rating'),
			$container = wkmp(this).closest('.mp-star-rating');

		$rating.val($star.data('rate'));
		$star.siblings('a').removeClass('active');
		$star.addClass('active');
		$container.addClass('selected');

		return false
	});
}); // Window.load end.

wkmp(function () { // wkmp function started.
    if (wkmp('.wkmp-role-selector').length) {
        wkmp('.wkmp-role-selector li').on('click', function (e) {
            let thisElm = wkmp(this);
            thisElm.addClass('active').siblings().removeClass('active');
            thisElm.children('input[type=radio]').prop('checked', true);
            if (1 == thisElm.data('target')) {
                wkmp('.wkmp-show-fields-if-seller').slideDown();
                wkmp('.wkmp-show-fields-if-seller').find(':input').removeAttr('disabled');
            } else {
                wkmp('.wkmp-show-fields-if-seller').find(':input').attr('disabled', 'disabled');
                wkmp('.wkmp-show-fields-if-seller').slideUp();
            }
        });
    }

    if (wkmp('#wkmp-shopname').length) {
        wkmp('#wkmp-shopname').on('focusout', function (e) {
            wkmp(this).next('.wkmp-error').remove();
            let value = wkmp(this).val().toLowerCase().replace(/-+/g, '').replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, '');
            wkmp('#wkmp-shopurl').val(value);
        });

        wkmp('#wkmp-shopurl').on('focusout', function () {
            let self = wkmp(this);
            wkmp(self).next('.wkmp-error').remove();
            let shop_slug = self.val();
            if ('' === shop_slug) {
                return false;
            }

            wkmp.ajax({
                type: 'POST',
                url: wkmpObj.ajax.ajaxUrl,
                data: {
                    action: "wkmp_check_shop_url",
                    shop_slug: shop_slug,
                    wkmp_nonce: wkmpObj.ajax.ajaxNonce,
                },
                success: function (response) {
                    if (false === response.error) {
                        wkmp('#wkmp-shop-url-availability').removeClass('wkmp-text-danger').addClass('wkmp-text-success').text(response.message);
                    } else {
                        wkmp('#wkmp-shop-url-availability').removeClass('wkmp-text-success').addClass('wkmp-text-danger').text(response.message);
                    }
                }
            });
        });
    }

    if (wkmp('.woocommerce-form-register').length) {
        wkmp('form.woocommerce-form-register').on('submit', function (e) {
            wkmp('.wkmp-error').remove();
            let role = wkmp('input[name=role]:checked').val();

            if ('seller' === role) {
                let form = wkmp(this).find('input');
                let errorDiv = wp.template('wkmp_field_empty');

                wkmp.each(form, function (i, elm) {
                    let elm_name = elm.name;

                    if ('wkmp_seller_signup_term_accept' === elm_name) {
                        wkmp(elm).is(':checked') ? wkmp(elm).val('yes') : wkmp(elm).val('');
                    }
                    let is_optional = wkmp(elm).attr('data-is_optional');

                    if (!is_optional && '' === wkmp(elm).val() && (elm_name.indexOf('wkmp_') > -1 || 'email' === elm_name)) {
                        e.preventDefault();
                        if ('wkmp_seller_signup_term_accept' === elm_name) {
                            wkmp(wkmp(elm)[0]).parent().after(errorDiv());
                        } else {
                            wkmp(wkmp(elm)[0]).after(errorDiv());
                        }
                    }
                });
            }
        });
    }

    /**
     * Js for tabs.
     */
    wkmp('.wkmp_nav_tabs li a:not(:first)').addClass('inactive');
    wkmp('.wkmp_tab_content .wkmp_tab_pane:not(:first)').addClass('wkmp_hide');

    wkmp('.wkmp_nav_tabs li a').on('click', function () {
        wkmp('.wkmp_nav_tabs li a').removeClass('active').addClass('inactive');
        wkmp(this).removeClass('inactive').addClass('active');

        let id = wkmp(this).data('id');

        wkmp('.wkmp_tab_content .wkmp_tab_pane').removeClass('wkmp_show').addClass('wkmp_hide');
        wkmp(`.wkmp_tab_content ${id}`).removeClass('wkmp_hide').addClass('wkmp_show');
    });

    wkmp('.wkmp_nav_tabs li a').each(function () {
        if ('yes' === wkmp(this).attr('data-current_tab')) {
            wkmp(this).trigger('click');
        }
    });

    // Variation attribute.
    wkmp(document).on('click', '#mp_var_attribute_call', function (event) {
        event.preventDefault();
        var pid = wkmp('#sell_pr_id').val();
        wkmp.ajax({
            type: 'POST',
            url: wkmpObj.ajax.ajaxUrl,
            data: {
                action: "wkmp_marketplace_attributes_variation",
                product: pid,
                wkmp_nonce: wkmpObj.ajax.ajaxNonce,
            },
            beforeSend: function () {
                wkmp('#mp-loader').css('display', 'block');
            },
            success: function (data) {
                wkmp('#mp-loader').css('display', 'none');
                wkmp('#mp_attribute_variations').append(data);
            }
        });
    });
    // Add product related code end here

    /**
     * Open the modal for seller ask query
     */
    wkmp('body').on('click', '#wkmp-ask-query', function () {
        let id = wkmp(this).data('modal_src');
        wkmp(id).css('display', 'block');
    });

    /**
     * Close modal.
     */
    wkmp('body').on('click', '.wkmp-popup-modal .modal-footer .close-modal', function () {
        wkmp(this).parents('.wkmp-popup-modal').css('display', 'none');
    });

    // Seller profile Page related code start here
    wkmp('body').on('click', '#wkmp-upload-profile-image', function () {
        wkmp('#seller_avatar_file').trigger('click');
    });

    wkmp('body').on('change', '#seller_avatar_file', function () {
        var reader = new FileReader();
        reader.onload = function (e) {
            wkmp('.wkmp_profile_img #wkmp-thumb-image img').attr('src', e.target.result);
        };
        reader.readAsDataURL(this.files[0]);
    });

    wkmp('body').on('click', '.wkmp_profile_img .wkmp-remove-profile-image', function () {
        let img = wkmp(this).parents('.wkmp_profile_img').find('#wkmp-thumb-image img').data('placeholder-url');
        wkmp(this).parents('.wkmp_profile_img').find('#wkmp-thumb-image img').attr('src', img);
        wkmp(this).parents('.wkmp_profile_img').find('#thumbnail_id_avatar').val('');
    });

    wkmp('body').on('click', '#wkmp-upload-shop-logo', function () {
        wkmp('#seller_shop_logo_file').trigger('click');
    });

    wkmp('body').on('change', '#seller_shop_logo_file', function () {
        var reader = new FileReader();
        reader.onload = function (e) {
            wkmp('.wkmp_profile_logo #wkmp-thumb-image img').attr('src', e.target.result);
        };
        reader.readAsDataURL(this.files[0]);
    });

    wkmp('body').on('click', '.wkmp_profile_logo .wkmp-remove-shop-logo', function () {
        let img = wkmp(this).parents('.wkmp_profile_logo').find('#wkmp-thumb-image img').data('placeholder-url');
        wkmp(this).parents('.wkmp_profile_logo').find('#wkmp-thumb-image img').attr('src', img);
        wkmp(this).parents('.wkmp_profile_logo').find('#thumbnail_id_company_logo').val('');
    });

    wkmp('body').on('click', '#wkmp-upload-seller-banner', function () {
        wkmp('#wk_mp_shop_banner').trigger('click');
    });

    wkmp('body').on('change', '#wk_mp_shop_banner', function () {
        var reader = new FileReader();
        reader.onload = function (e) {
            wkmp('.wkmp_shop_banner img').attr('src', e.target.result);
        };
        reader.readAsDataURL(this.files[0]);
    });

    wkmp('body').on('click', '.wkmp_shop_banner #wkmp-remove-seller-banner', function () {
        let img = wkmp(this).parents('.wkmp_shop_banner').find('#wk_seller_banner img').data('placeholder-url');
        wkmp(this).parents('.wkmp_shop_banner').find('#wk_seller_banner img').attr('src', img);
        wkmp(this).parents('.wkmp_shop_banner').find('#thumbnail_id_shop_banner').val('');
    });
    // Seller profile Page related code end here.

    /**
     * Checked all list on click all checked.
     */
    wkmp('body').on('click', '#wkmp-checked-all', function () {
        if (true == wkmp(this).prop("checked")) {
            wkmp('input[name*=\'selected\']').prop('checked', true);
        } else {
            wkmp('input[name*=\'selected\']').prop('checked', false);
        }
    });

    // Select all checkbox in head on selecting all entries checkboxes in body in front seller tables.
    wkmp('.wkmp-table-responsive table tbody td input[type=checkbox]').on('click', function () {
        let checkedInput = wkmp('.wkmp-table-responsive table tbody td input[type=checkbox]:checked').length;
        let total = wkmp(".wkmp-table-responsive table tbody td input[type=checkbox]").length;

        if (total === checkedInput) {
            wkmp("#wkmp-checked-all").prop("checked", true);
        } else {
            wkmp("#wkmp-checked-all").prop("checked", false);
        }
    });

    //Bulk deleting favorite seller from customer my-account page.
    wkmp('body').on('click', '.wkmp-bulk-delete', function () {
        let form_id = wkmp(this).data('form_id');
        let flag = false;
        wkmp(`${form_id} input[type=\'checkbox\']`).each(function () {

            if (wkmp(this).is(':checked') && wkmp(this).val() > 0) {
                flag = true;
            }
        });
        if (flag) {
            confirm(wkmpObj.delete_product_alert) ? wkmp(form_id).submit() : false;
        } else {
            alert(wkmpObj.none_selected);
        }
    });

    // Deleting a single favorite seller from customer my-account page.
    wkmp('body').on('click', '#wkmp_delete_single_fav_seller', function () {
        if (confirm(wkmpObj.delete_fav_seller_alert)) {
            wkmp(this).closest('tr').children('td:first').find('input[type=checkbox]').prop('checked', true);
            wkmp(this).closest('form').submit();
        }
    });

    wkmp('body').on('click', '#wkmp-send-notification', function () {
        let customer_ids = [];

        wkmp(`#wkmp-followers-list input[type=\'checkbox\']`).each(function () {
            if (wkmp(this).is(':checked')) {
                customer_ids.push(wkmp(this).val());
            }
        });

        if ('on' === customer_ids[0]) {
            customer_ids.shift();
        }

        if (customer_ids.length >= 1) {
            for (var i = 0; i < customer_ids.length; i++) {
                wkmp('#wkmp-seller-send-notification #wkmp-seller-sendmail-form').append(`<input type="hidden" name="customer_ids[]" value="${customer_ids[i]}"/>`);
            }
            wkmp('#wkmp-seller-send-notification').css('display', 'block');

        } else {
            alert(wkmpObj.none_selected);
        }
    });

    //Add favorite sellers.
    wkmp('body').on('click', '#wkmp-add-seller-as-favourite', function () {
        let seller_id = wkmp(this).find('input[name="wkmp_seller_id"]').val();
        let customer_id = wkmp(this).find('input[name="wkmp_customer_id"]').val();

        wkmp('.wkmp-spin-loader').removeClass('wkmp_hide');
        wkmp.ajax({
            type: 'POST',
            url: wkmpObj.ajax.ajaxUrl,
            data: {
                action: "wkmp_add_favourite_seller",
                seller_id: seller_id,
                customer_id: customer_id,
                wkmp_nonce: wkmpObj.ajax.ajaxNonce,
            },
            success: function (json) {
                wkmp('.wkmp-spin-loader').addClass('wkmp_hide');
                if ('added' === json['success']) {
                    wkmp('#wkmp-add-seller-as-favourite .dashicons-heart').addClass('wkmp_active_heart')
                }
                if ('removed' === json['success']) {
                    wkmp('#wkmp-add-seller-as-favourite .dashicons-heart').removeClass('wkmp_active_heart')
                }
                wkmp('.woocommerce .wkmp-confirmation-msg').html(json.message).css('display', 'block');
                setTimeout(function () {
                    wkmp('.woocommerce .wkmp-confirmation-msg').css('display', 'none');
                }, 3000);
            }
        });
    });

    wkmp('body').on('click', '.mp-rating-input .stars a', function () {
        let curr_obj = wkmp(this).parents('.mp-rating-input');
        let rate = wkmp(this).text();
        curr_obj.find('.stars').find('a').removeClass('active');
        curr_obj.find('.stars').addClass('selected');
        curr_obj.find('.stars').find(`.star-${rate}`).addClass('active');
        let id = curr_obj.data('id');
        wkmp(`${id} option:selected`).removeAttr("selected");
        wkmp(`${id} option:eq(${rate})`).attr("selected", "selected");
    });

    wkmp('#mp-update-sale-order').on('change', function (evt) {
        evt.preventDefault();
        wkmp(window).scrollTop(0);
        wkmp('body').append('<div class=wk-mp-loader><div class=wk-mp-spinner wk-mp-skeleton></div></div>');
        wkmp('.wk-mp-loader').css('display', 'inline-block');
        wkmp('body').css('overflow', 'hidden');
        setTimeout(function () {
            wkmp('body').css('overflow', 'auto');
            wkmp('.wk-mp-loader').remove()
        }, 1500)
    });

    wkmp('#wkmp-seller-profile #billing-country').on('change', function (evt) {
        let code = wkmp(this).val();
        wkmp.ajax({
            type: 'POST',
            url: wkmpObj.ajax.ajaxUrl,
            data: {
                action: "wkmp_get_state_by_country_code",
                country_code: code,
                wkmp_nonce: wkmpObj.ajax.ajaxNonce,
            },
            success: function (json) {
                if (json['success']) {
                    wkmp('#wkmp-seller-profile #wkmp_shop_state').replaceWith(json['html']);
                }
            }
        });
    });

    /* Product status downloadable file */
    var file_path_field;

    wkmp('.wk-mp-side-body').on("click", '.upload_downloadable_file', function (event) {
        var file_frame;
        var $el = wkmp(this);
        file_path_field = $el.closest('tr').find('td.file_url input');
        event.preventDefault();

        // If the media frame already exists, reopen it.
        if (file_frame) {
            file_frame.open();
            return;
        }

        // Create the media frame.
        file_frame = wp.media.frames.file_frame = wp.media({
            title: $el.data('choose'),
            button: {
                text: $el.data('update')
            },
            multiple: false  // Set to true to allow multiple files to be selected.
        });

        // When frame is open, select existing image attachments from custom field.
        file_frame.on('open', function () {
            var selection = file_frame.state().get('selection');
        });

        var query = wp.media.query();

        query.filterWithIds = function (ids) {
            return _(this.models.filter(function (c) {
                return _.contains(ids, c.id);
            }));
        };

        var res = query.filterWithIds([3]); // change these to your IDs.
        // When images are selected, place IDs in hidden custom field and show thumbnails.

        file_frame.on('select', function () {
            var file_path = '';
            var selection = file_frame.state().get('selection');

            // Place IDs in custom field.

            var attachment_ids = selection.map(function (attachment) {
                attachment = attachment.toJSON();
                if (attachment.url) {
                    file_path = attachment.url;
                }
                file_path_field.val(file_path).change();
                return attachment.id;
            });
        });

        // Finally, open the modal

        file_frame.open();
    });

    wkmp(".select-group .dropdown-togle").on("click", function () {
        wkmp(this).parent().toggleClass("open");
    });

    wkmp('.wkmp-order-refund-button').on('click', (e) => {
        wkmp('.wkmp-order-refund').toggle();
        if ('table-cell' === wkmp('.wkmp-order-refund').css('display')) {
            wkmp(e.target).text('Cancel');
        } else {
            wkmp(e.target).text('Refund');
        }
    });

    if (wkmp('.refund_line_total')) {
        wkmp('.refund_line_total').on('change', (e) => {
            let refundTotal = 0;
            document.querySelectorAll('.refund_line_total').forEach((input) => {
                let qty = 0;
                if (input.type === 'checkbox' && input.checked) {
                    qty = input.value;
                } else if (input.type !== 'checkbox') {
                    qty = input.value;
                }
                refundTotal += qty * input.previousElementSibling.value;
            });
            document.querySelector('#refund-amount').value = Math.round(refundTotal * 100) / 100;
        });
    }

    // Change product status.
    wkmp('.wkmp-toggle-select').on('change', function () {
        let status = wkmp(this).val();
        if ('publish' === status) {
            wkmp('.mp-toggle-selected-display').html(wkmpObj.mkt_tr.mkt28).addClass('green');
        } else {
            wkmp('.mp-toggle-selected-display').html(status).removeClass('green');
        }
    });


    // Select status on clicking label on product edit.
    wkmp('#wkmp_product_status_checkbox_wrap label').on('click', function () {
        wkmp(this).children(".wkmp-toggle-select").trigger('change').prop('checked', true);
    });

    // Product type sidebar.
    var product_type = wkmp('#product_type').val();

    var var_type = wkmp('#var_variation_display').val();
    if ('variable' === product_type && 'yes' === var_type) {
        wkmp("#edit_product_tab li").eq(6).show();
    }

    if ('external' === product_type) {
        wkmp("#edit_product_tab li").eq(5).show();
        wkmp("#edit_product_tab li").eq(2).hide();
    }

    wkmp(document).on('change', 'body #product_type', function () {
        var product_type = wkmp('#product_type').val();
        var var_type = wkmp('#var_variation_display').val();

        if ('variable' === product_type && 'yes' === var_type) {
            wkmp("#edit_product_tab li").eq(6).show();
        } else {
            wkmp("#edit_product_tab li").eq(6).hide();
        }

        if ('simple' === product_type) {
            wkmp('#regularPrice').show();
            wkmp('#salePrice').show();
        } else {
            wkmp('#regularPrice').hide();
            wkmp('#salePrice').hide();
        }

        if ('external' === product_type) {
            wkmp("#edit_product_tab li").eq(5).show();
            wkmp("#edit_product_tab li").eq(2).hide();
        } else {
            wkmp("#edit_product_tab li").eq(5).hide();
            wkmp("#edit_product_tab li").eq(2).show();
        }
    });

    wkmp('a.mp-toggle-type-cancel').on('click', function () {
        wkmp('.mp-toggle-select-type-container').css('display', 'none');
    });

    wkmp('.mp_value_asc').change(function () {
        var str = wkmp(this).val();
        var newUrl = window.location.href + '&' + str;
        window.location = newUrl;
    });

    //downloadable check.
    wkmp('#_ckdownloadable').change(function () {
        wkmp('.wk-mp-side-body').slideToggle("slow");
    });

    wkmp('#_ckvirtual').change(function () {
        if ('none' !== wkmp("#edit_product_tab li").eq(2).css('display')) {
            wkmp("#edit_product_tab li").eq(2).hide();
        } else {
            wkmp("#edit_product_tab li").eq(2).show();
        }
    });

    /***********Seller multiple downloadable files starts***********/

    wkmp('.wk-mp-side-body').on('click', '.downloadable_files a.insert', function () {
        wkmp(this).closest('.downloadable_files').find('tbody').append(wkmp(this).data('row'));
        return false;
    });

    wkmp('.wk-mp-side-body').on('click', '.downloadable_files a.delete', function () {
        wkmp(this).closest('tr').remove();
        return false;
    });

    /***********Seller multiple downloadable files ends***********/
    wkmp(document).on('change', '.checkbox_is_virtual', function () {
        wkmp(this).parents('tbody').children('tr').eq(0).find('.virtual').slideToggle('fast');
    });

    wkmp(document).on('change', '.checkbox_is_downloadable', function () {
        wkmp(this).parents('tbody').children('tr').eq(0).find('.downloadable').slideToggle('fast');
    });
    wkmp(document).on('change', '.checkbox_manage_stock', function () {
        wkmp(this).parents('tbody').children('tr').eq(0).find('.wkmp_stock_qty').slideToggle('fast');
        wkmp(this).parents('tbody').children('tr').eq(0).find('.wkmp_stock_status').slideToggle('fast');
    });
    // upload file name handler

    //upload button for product image file
    wkmp('.add-mp-product-images').on('click', function (event) {
        var file_frame;
        var image_id = wkmp(this).attr('id');
        var image_id_field = wkmp('#product_image_Galary_ids').val();
        var galary_ids = '';
        var typeError = 0;

        wkmp('#wk-mp-product-images').find('.wkmp-error-class').remove();

        if ('' === image_id_field) {
            galary_ids = '';
        } else {
            galary_ids = image_id_field + ',';
        }

        event.preventDefault();
        // If the media frame already exists, reopen it.
        if (file_frame) {
            file_frame.open();
            return;
        }

        // Create the media frame.
        file_frame = wp.media.frames.file_frame = wp.media({
            title: wkmp(this).data('uploader_title'),
            button: { text: wkmp(this).data('uploader_button_text') },
            multiple: true  // Set to true to allow multiple files to be selected
        });

        // When frame is open, select existing image attachments from custom field
        file_frame.on('open', function () {
            var selection = file_frame.state().get('selection');
        });

        var query = wp.media.query();

        query.filterWithIds = function (ids) {
            return _(this.models.filter(function (c) {
                return _.contains(ids, c.id);
            }));
        };

        // When images are selected, place IDs in hidden custom field and show thumbnails.
        file_frame.on('select', function () {
            var selection = file_frame.state().get('selection');
            // Place IDs in custom field
            var attachment_ids = selection.map(function (attachment) {
                attachment = attachment.toJSON();

                if (undefined !== attachment.sizes) {
                    galary_ids = galary_ids + attachment.id + ',';
                    wkmp('#handleFileSelectgalaray').append("<img src='" + attachment.sizes.thumbnail.url + "' width='50' height='50'/>");
                    return attachment.id;
                } else {
                    typeError = 1;
                }
            });

            if (typeError) {
                wkmp('#wk-mp-product-images').append("<p class=wkmp-error-class" + wkmp(".mp_product_thumb_image.button").data('type-error') + "</p>");
            }

            galary_ids = galary_ids.replace(/,\s*$/, "");
            wkmp('#product_image_Galary_ids').val(galary_ids);

        });

        // Finally, open the modal
        file_frame.open();
    });

    /* mp thumb image */
    wkmp('.mp_product_thumb_image').on('click', function (event) {
        var file_frame;

        event.preventDefault();

        if (file_frame) {
            file_frame.open();
            return;
        }

        // Create the media frame.
        file_frame = wp.media.frames.file_frame = wp.media({
            title: wkmp(this).data('uploader_title'),
            button: { text: wkmp(this).data('uploader_button_text') },
            multiple: false  // Set to true to allow multiple files to be selected
        });

        // When frame is open, select existing image attachments from custom field
        file_frame.on('open', function () {
            var selection = file_frame.state().get('selection');
        });

        var query = wp.media.query();

        query.filterWithIds = function (ids) {
            return _(this.models.filter(function (c) {
                return _.contains(ids, c.id);
            }));
        };

        // When images are selected, place IDs in hidden custom field and show thumbnails.
        file_frame.on('select', function () {
            var selection = file_frame.state().get('selection');
            // Place IDs in custom field
            var attachment_ids = selection.map(function (attachment) {
                attachment = attachment.toJSON();
                wkmp(".mp_product_thumb_image.button").siblings('.wkmp-error-class').remove();

                if (undefined !== attachment.sizes) {
                    wkmp('#product_thumb_image_mp').val(attachment.id);
                    wkmp('#mp-product-thumb-img-div').find("img").attr('src', attachment.sizes.thumbnail.url);

                    if (wkmp('#mp-product-thumb-img-div').find("span").length <= 0) {
                        wkmp('#mp-product-thumb-img-div').append('<span title="' + wkmpObj.mkt_tr.mkt32 + '" class="mp-image-remove-icon">x</span>');
                    }

                    return attachment.id;
                } else {
                    wkmp(".mp_product_thumb_image.button").parent().append("<p class=wkmp-error-class>" + wkmp(".mp_product_thumb_image.button").data('type-error') + "</p>");
                }
            });

        });

        // Finally, open the modal
        file_frame.open();
    });

    /* mp thumb image end */

    /* remove thumb image product */
    wkmp('#mp-product-thumb-img-div').on('click', '.mp-image-remove-icon', function () {
        wkmp('#product_thumb_image_mp').val('');
        wkmp(this).siblings('img').attr('src', wkmp(this).siblings('img').data('placeholder-url'));
        wkmp(this).remove();
    });

    // tabs on edit product page
    wkmp('#edit_product_tab li a:not(:first)').addClass('inactive');

    if (!wkmp('#edit_notification_tab li a').length) {
        wkmp('.wkmp_container').hide();
        wkmp('.wkmp_container:first').show();
    }

    var activeproducttab = wkmp('#active_product_tab');
    if (activeproducttab.val()) {
        var activeproducttabvalue = activeproducttab.val();
        if (wkmp('#' + activeproducttabvalue).hasClass('inactive')) {
            wkmp('#edit_product_tab li a').addClass('inactive');
            wkmp('#' + activeproducttabvalue).removeClass('inactive');

            wkmp('.wkmp_container').hide();
            wkmp('#' + wkmp('#' + activeproducttabvalue).attr('id') + 'wk').fadeIn('slow');
        }
    }

    wkmp('#edit_product_tab li a').click(function () {
        var t = wkmp(this).attr('id');
        activeproducttab.val(t);
        if (wkmp(this).hasClass('inactive')) { //this is the start of our condition
            wkmp('#edit_product_tab li a').addClass('inactive');
            wkmp(this).removeClass('inactive');

            wkmp('.wkmp_container').hide();
            wkmp('#' + t + 'wk').fadeIn('slow');
        }
    });

    wkmp('#edit_notification_tab li a').click(function () {
        var t = wkmp(this).attr('id');
        if (wkmp(this).hasClass('inactive')) { //this is the start of our condition
            wkmp('#edit_notification_tab li a').addClass('inactive');
            wkmp(this).removeClass('inactive');

            wkmp('.wkmp_container').hide();
            wkmp('#' + t + 'wk').fadeIn('slow');
        }
    });

    //attribute dynamic fields
    wkmp(document).on('click', '.wkmp-add-variant-attribute', function (e) {
        e.preventDefault();

        wkmp_create_attribute_html();
    });

    wkmp(".wk_marketplace_attributes").on("click", ".mp_attribute_remove", function (e) { //user click on remove text
        e.preventDefault();
        wkmp(this).parent().parent().parent().remove();
    });

    wkmp('#mp_attribute_variations').on("click", ".mp_attribute_remove", function (e) { //user click on remove text
        e.preventDefault();
        wkmp(this).parent().parent().remove();
        var var_att_id = wkmp(this).data('var_id');
        wkmp.ajax({
            type: 'POST',
            url: wkmpObj.ajax.ajaxUrl,
            data: { "action": "wkmp_attributes_variation_remove", "var_id": var_att_id, "wkmp_nonce": wkmpObj.ajax.ajaxNonce },
            success: function (data) {
                wkmp('#wkmp_remove_notice_wrap').removeClass('wkmp_hide').html(data.msg);
                if (!data.success) {
                    wkmp('#wkmp_remove_notice_wrap').addClass('woocommerce-error');
                }
            }
        });
    });

    wkmp('.wkmp_variation_downloadable_file').on("click", '.mp_var_del', function () {
        var del_id = wkmp(this).attr('id');
        wkmp('#' + del_id).parent().parent().remove();
    });

    wkmp('#mp_attribute_variations').on("click", ".upload_image_button", function () {
        var file_type_id = wkmp(this).attr('id') + 'upload';
        wkmp('#' + file_type_id).trigger('click');
    });

    wkmp(document).on("click", '#mp_attribute_variations div.wkmp_variation_downloadable_file .wkmp_downloadable_upload_file', function (event) {
        event.preventDefault();
        var trigger_id = wkmp(this).attr('id');
        // var up_id=trigger_id.split('_');
        var text_box_file_url = 'downloadable_upload_file_url_' + trigger_id;
        var file_frame;
        // If the media frame already exists, reopen it.
        if (file_frame) {
            file_frame.open();
            return;
        }

        // Create the media frame.
        file_frame = wp.media.frames.file_frame = wp.media({
            title: wkmp(this).data('uploader_title'),
            button: { text: wkmp(this).data('uploader_button_text') },
            multiple: false  // Set to true to allow multiple files to be selected
        });

        // When frame is open, select existing image attachments from custom field
        file_frame.on('open', function () {
            var selection = file_frame.state().get('selection');
        });
        var query = wp.media.query();

        query.filterWithIds = function (ids) {
            return _(this.models.filter(function (c) {
                return _.contains(ids, c.id);
            }));
        };

        var res = query.filterWithIds([3]); // change these to your IDs

        // When images are selected, place IDs in hidden custom field and show thumbnails.
        file_frame.on('select', function () {

            var selection = file_frame.state().get('selection');

            // Place IDs in custom field
            var attachment_ids = selection.map(function (attachment) {
                attachment = attachment.toJSON();
                wkmp('#' + text_box_file_url).val(attachment.url);
                return attachment.id;
            });
        });

        // Finally, open the modal
        file_frame.open();
    });

    // variation attribute

    // multiple thumb image upload and view
    function handleFileSelect(evt) {
        wkmp('#product_image').empty();
        var files = evt.target.files; // FileList object
        // Loop through the FileList and render image files as thumbnails.
        for (var i = 0, f; f = files[i]; i++) {
            // Only process image files.
            if (!f.type.match('image.*')) {
                continue;
            }
            var reader = new FileReader();
            // Closure to capture the file information.
            reader.onload = (function (theFile) {
                return function (e) {
                    // Render thumbnail.
                    var div = document.createElement('div');
                    //wkmp(div).attr({class:'ingdiv'});
                    div.innerHTML = ['<img class="thumb" src="', e.target.result, '" title="', escape(theFile.name), '"/><span class="wkmp_image_over" ></span><input type="hidden" name ="mpthumbimg[]" value="', escape(theFile.name), '">'].join('');
                    document.getElementById('product_image').insertBefore(div, null);
                    wkmp('#product_image div').attr({ class: 'imgdiv' });
                    wk_imgview();
                };
            })(f);
            // Read in the image file as a data URL.
            reader.readAsDataURL(f);
        }

        function wk_imgview() {
            wkmp('div.imgdiv').mouseover(function (event) {
                //alert('Hello div');
                wkmp(this).find(".wkmp_image_over").css({ display: "block" });
                wkmp(this).find("img").css("opacity", "0.4");
                wkmp(this).find(".wkmp_image_over").on('click', function () {
                    wkmp(this).parent("div").remove();
                });
            });

            wkmp("div.imgdiv").mouseout(function (event) {
                wkmp(this).find(".wkmp_image_over").css({ display: "none" });
                wkmp(this).find("img").css("opacity", "1");
            });
        }
    }

    // multiple galary image upload and view
    function handleFilegalaray(evt) {
        wkmp('#handleFileSelectgalaray').empty();
        var files = evt.target.files; // FileList object
        // Loop through the FileList and render image files as thumbnails.
        for (var i = 0, f; f = files[i]; i++) {
            // Only process image files.
            if (!f.type.match('image.*')) {
                continue;
            }
            var reader = new FileReader();
            // Closure to capture the file information.
            reader.onload = (function (theFile) {
                return function (e) {
                    // Render thumbnail.
                    var div = document.createElement('div');
                    div.innerHTML = ['<img class="thumb" src="', e.target.result, '" title="', escape(theFile.name), '"/><span class="wkmp_image_over" ></span><input type="hidden" name ="mpproductgall[]" value="', escape(theFile.name), '">'].join('');
                    document.getElementById('handleFileSelectgalaray').insertBefore(div, null);
                    wkmp('#handleFileSelectgalaray div').attr({ class: 'imgdiv' });
                    wk_imgview();
                };
            })(f);
            // Read in the image file as a data URL.
            reader.readAsDataURL(f);
        }

        function wk_imgview() {
            wkmp('div.imgdiv').mouseover(function (event) {
                //alert('Hello div');
                wkmp(this).find(".wkmp_image_over").css({ display: "block" });
                wkmp(this).find("img").css({ "opacity": "0.4" });
                // For Delete the image  Div at Click on Cross Icon
                wkmp(this).find(".wkmp_image_over").on('click', function () {
                    wkmp(this).parent("div").remove();
                });
            });

            wkmp("div.imgdiv").mouseout(function (event) {
                wkmp(this).find(".wkmp_image_over").css({ display: "none" });
                wkmp(this).find("img").css({ "opacity": "1" });
            });
        }
    }

    // deleting image
    wkmp('a.mp-img-delete_gal').click(function () {
        wkmp('#' + this.id).parent().remove();
        wkmp.ajax({
            type: 'POST',
            url: wkmpObj.ajax.ajaxUrl,
            data: {
                "action": "wkmp_productgallary_image_delete",
                "img_id": this.id,
                "wkmp_nonce": wkmpObj.ajax.ajaxNonce
            },
            success: function (data) {
                wkmp('#product_image_Galary_ids').val(data);
            }
        });
    });

    wkmp('#mp_attribute_variations').on("click", ".mp_varnew_file", function () {
        var var_did = wkmp(this).attr('id');
        var variation_count = wkmp("div#variation_downloadable_file_" + var_did + " > div").length;
        var wrapper = '#variation_downloadable_file_' + var_did;
        wkmp.ajax({
            type: 'POST',
            url: wkmpObj.ajax.ajaxUrl,
            data: {
                "action": "wkmp_downloadable_file_add",
                "var_id": var_did,
                "eleme_no": variation_count,
                "wkmp_nonce": wkmpObj.ajax.ajaxNonce
            },
            success: function (data) {
                wkmp(data).appendTo(wrapper);
            }
        });
    });

    //Product validation.
    wkmp('#add_product_sub').click(function (e) {
        if ('submit' === wkmp(this).attr('type')) {
            var product_name = wkmp('#product_name').val();
            product_name = trim_wkmp_value(product_name);
            var product_sku = wkmp('#product_sku').val();
            var regu_price = wkmp('#regu_price').val();

            var error = 0;
            if (0 === product_name.length) {
                wkmp('#pro_name_error').html(wkmpObj.mkt_tr.mkt2);
                error++;
            }

            if ('undefined' !== typeof (product_sku) && '' !== product_sku && product_sku.length < 3) {
                wkmp('#pro_sku_error').css('color', 'red');
                wkmp('#pro_sku_error').html(wkmpObj.mkt_tr.mkt3);
                error++;
            }

            var pro_type = wkmp('input[name="product_type"]').val();

            if ('' === pro_type || 'undefined' === typeof pro_type) {
                pro_type = wkmp('#product_type').val();
            }

            if ('variable' !== pro_type && 'grouped' !== pro_type) {
                if (!wkmp_validate_decimal_input(regu_price)) {
                    wkmp('#regl_pr_error').html(wkmpObj.mkt_tr.i18n_decimal_error);
                    error++;
                } else {
                    wkmp('#regl_pr_error').html('');
                }
            }

            var sale_price = wkmp('#sale_price').val();
            var regular = parseFloat(wkmp('#regu_price').val());
            var sale = parseFloat(wkmp('#sale_price').val());
            if (wkmp('#sale_price').val()) {
                if (!wkmp_validate_decimal_input(sale_price)) {
                    wkmp('#sale_pr_error').html(wkmpObj.mkt_tr.i18n_decimal_error);
                    error++;
                } else if (sale > regular) {
                    wkmp('#sale_pr_error').html(wkmpObj.mkt_tr.mkt5);
                    error++;
                } else {
                    wkmp('#sale_pr_error').html('');
                }
            }

            // variation weight price validation
            wkmp(document).on('blur', '.wc_input_decimal, #wk-mp-stock-qty', function () {
                var no = wkmp(this).val();
                wkmp(this).parent('.wrap').children('.wkmp-error-class').remove()
                if (no && !wkmp_validate_decimal_input(no)) {
                    wkmp(this).parent('.wrap').append('<span class="wkmp-error-class">' + wkmpObj.mkt_tr.i18n_decimal_error + '</span>')
                }
            });

            wkmp('.wkmp_marketplace_variation .wc_input_decimal, .wkmp-add-product-form .wc_input_decimal').each(function () {
                var no = wkmp(this).val();
                wkmp(this).parent('.wrap').children('.wkmp-error-class').remove()
                if (no && !wkmp_validate_decimal_input(no)) {
                    wkmp(this).parent('.wrap').append('<span class="wkmp-error-class">' + wkmpObj.mkt_tr.i18n_decimal_error + '</span>')
                    error++;
                }
            });

            if (error) {
                return false;
            }
        }
    });

    // Variation regular price validation.
    wkmp(document).on('blur', '.wc_input_price', function () {
        var no = wkmp(this).val();
        wkmp(this).next('.wkmp-error-class').remove()
        if (no && !wkmp_validate_decimal_input(no)) {
            wkmp(this).after('<span class="wkmp-error-class">' + wkmpObj.mkt_tr.i18n_decimal_error + '</span>');
        }
    });

    // variation weight price validation
    wkmp(document).on('blur', '.wc_input_decimal, #wk-mp-stock-qty', function () {
        var no = wkmp(this).val();
        wkmp(this).parent('.wrap').children('.wkmp-error-class').remove()
        if (no && !wkmp_validate_decimal_input(no)) {
            wkmp(this).parent('.wrap').append('<span class="wkmp-error-class">' + wkmpObj.mkt_tr.i18n_decimal_error + '</span>')
        }
    });

    // stock
    wkmp(document).on('blur', '._weight_field .wc_input_decimal, #wk-mp-stock-qty', function () {
        var no = wkmp(this).val();
        wkmp(this).next('.wkmp-error-class').remove()
        if (no && !wkmp_validate_decimal_input(no)) {
            wkmp(this).after('<span class="wkmp-error-class">' + wkmpObj.mkt_tr.i18n_decimal_error + '</span>')
        }
    });

    function trim_wkmp_value(item) {
        item = wkmp.trim(item);
        return item;
    }

    //SKU validation.
    let ps = wkmp('#product_sku').val();

    wkmp('#product_sku').blur(function () {
        let product_sku = wkmp('#product_sku').val();
        wkmp('#pro_sku_error').html('');

        if (product_sku !== ps) {
            product_sku_validation(product_sku);
        }
    });

    function product_sku_validation(argument) {
        var product_sku = argument;
        var reg_sku = /^[a-z0-9A-Z_-]{1,20}$/;
        wkmp('#pro_sku_error').css('color', 'red');

        if ('' !== product_sku) {

            if (!reg_sku.test(product_sku)) {
                wkmp('#pro_sku_error').html('Special character and spaces are not allowed');
                return false;
            } else if (product_sku.length < 3) {
                wkmp('#pro_sku_error').css('color', 'red');
                wkmp('#pro_sku_error').html(wkmpObj.mkt_tr.mkt3);
                return false;
            } else {
                wkmp('#pro_sku_error').html('');
            }

            wkmp('#add_product_sub').attr('disabled', 'disabled');
            wkmp.ajax({
                type: 'POST',
                url: wkmpObj.ajax.ajaxUrl,
                dataType: "json",
                data: {
                    "action": "wkmp_product_sku_validation",
                    "psku": product_sku,
                    "wkmp_nonce": wkmpObj.ajax.ajaxNonce
                },
                success: function (data) {
                    if (data && data.success === true) {
                        wkmp('#pro_sku_error').css('color', 'green');
                        wkmp('#pro_sku_error').html(data.message);
                        wkmp('#add_product_sub').removeAttr('disabled');
                    } else {
                        wkmp('#pro_sku_error').css('color', 'red');
                        wkmp('#pro_sku_error').html(data.message);
                    }
                }
            });
        }
    }

    // Variation sku validation.
    wkmp(document).on('blur', '.wkmp_variable_sku', function () {
        var wkmp_variable_sku = wkmp(this).val();
        var this_sel = this;
        wkmp(this).siblings('.wk_variable_sku_err').html('');
        if (wkmp(this).val() !== wkmp(this).attr('placeholder')) {
            variation_sku_validation(wkmp_variable_sku, this_sel);
        }
    });

    function variation_sku_validation(argument1, argument2) {
        var wkmp_variable_sku = argument1;
        var reg_sku = /^[a-z0-9A-Z]{1,20}$/;
        var this_sel = argument2;
        wkmp(this_sel).siblings('.wk_variable_sku_err').css('color', 'red');
        if ('' === wkmp_variable_sku) {
            wkmp(this_sel).siblings('.wk_variable_sku_err').html(wkmpObj.mkt_tr.mkt4);
            return false;
        } else if (!reg_sku.test(wkmp_variable_sku)) {
            // wkmp(this_sel).siblings('.wk_variable_sku_err').html('special character and space are not allowed');
            // return false;
        } else {
            wkmp(this_sel).siblings('.wk_variable_sku_err').html('');
        }
        wkmp.ajax({
            type: 'POST',
            url: wkmpObj.ajax.ajaxUrl,
            dataType: "json",
            data: { "action": "wkmp_product_sku_validation", "psku": wkmp_variable_sku, "wkmp_nonce": wkmpObj.ajax.ajaxNonce },
            success: function (data) {
                if (data && data.success === true) {
                    wkmp(this_sel).siblings('.wk_variable_sku_err').css('color', 'green');
                    wkmp(this_sel).siblings('.wk_variable_sku_err').html(data.message);
                } else {
                    wkmp(this_sel).siblings('.wk_variable_sku_err').css('color', 'red');
                    wkmp(this_sel).siblings('.wk_variable_sku_err').html(data.message);
                    return false;
                }
            }
        });
    }

    // variation weight price validation
    wkmp(document).on('keyup', '.wkmp_variable_stock', function () {
        var no = wkmp(this).val();
        var no_int = no;
        var stock = /^\d+(\.\d{1,2})?$/;
        var a = no_int;
        if (no == no_int) {
            a = no_int;
        }
        if ('' !== wkmp(this).val() && stock.test(a)) {
            wkmp(this).val(a);
        } else {
            wkmp(this).val('');
            a = 0;
        }
    });
    //product name validation.
    wkmp('#product_name').blur(function () {
        var product_name = wkmp('#product_name').val();
        if (_.isEmpty(product_name)) {
            wkmp('#pro_name_error').html(wkmpObj.mkt_tr.mkt8);
            return false;
        } else {
            wkmp('#pro_name_error').html('');
        }
    });

    //product regular price validation
    wkmp('#regu_price').blur(function () {
        var regu_price = wkmp('#regu_price').val();
        var pro_type = wkmp('#product_type');

        if (!product_type) {
            pro_type = wkmp('#product-form').find('input[name="product_type"]').val()
        }

        if ('variable' !== pro_type && 'grouped' !== pro_type && !wkmp_validate_decimal_input(regu_price)) {
            wkmp('#regl_pr_error').html(wkmpObj.mkt_tr.i18n_decimal_error);
            return false;
        } else {
            wkmp('#regl_pr_error').html('');
        }
    });

    //product sale price validation
    wkmp('#sale_price').blur(function () {
        var sale_price = wkmp('#sale_price').val();
        var regular = wkmp('#regu_price').val();
        regular = _.isEmpty(regular) ? regular : parseFloat(regular);
        var sale = parseFloat(wkmp('#sale_price').val());
        var pro_type = wkmp('#product_type');

        if (!product_type) {
            pro_type = wkmp('#product-form').find('input[name="product_type"]').val()
        }

        if (!_.isEmpty(wkmp('#sale_price').val()) && 'variable' !== pro_type && 'grouped' !== pro_type) {
            if (!wkmp_validate_decimal_input(sale_price)) {
                wkmp('#sale_pr_error').html(wkmpObj.mkt_tr.i18n_decimal_error);
                return false;
            } else if (sale >= regular) {
                wkmp('#sale_pr_error').html(wkmpObj.mkt_tr.mkt5);
                return false;
            } else {
                wkmp('#sale_pr_error').html('');
            }
        }
    });

    wkmp(document).on('blur', '.wkmp_variable_sale_price', function () {
        var sale_price = wkmp(this).val();
        var regular = parseFloat(wkmp(this).parent().siblings().children('.wkmp_variable_regular_price').val());
        var sale = parseFloat(wkmp(this).val());
        if ('' !== wkmp(this).val()) {
            if (!wkmp_validate_decimal_input(sale_price)) {
                wkmp(this).siblings('.sale_pr_error').html(wkmpObj.mkt_tr.i18n_decimal_error);
                return false;
            } else if (sale >= regular) {
                wkmp(this).siblings('.sale_pr_error').html(wkmpObj.mkt_tr.mkt5);
                return false;
            } else {
                wkmp('#sale_pr_error').html('');
            }
        }
    });
    // product validation end

    // Show list of countries and states on focus input box
    wkmp(document).on("focusin", "#unused_elm", function () {
        wkmp(this).siblings(".live-search-list").slideDown();
    });

    // On click to country or state show it on input box and save it on input type hidden
    wkmp(document).on("click", ".live-search-list li", function () {
        wkmp(this).parent(".live-search-list").slideUp();
        var currentVal = wkmp(this).text().trim();
        var searched_term = wkmp(this).data("search-term");
        tag = wkmp('<div class="mp_ship_tags" data-value=' + searched_term + '>' + currentVal + '<a class="mp_del_tag">x</a></div>');
        if ('' === wkmp(this).parent().prev("#mp_set_zone_location").val()) {
            wkmp(this).parent().prev("#mp_set_zone_location").val(wkmp(this).parent().prev("#mp_set_zone_location").val() + searched_term);
        } else {
            wkmp(this).parent().prev("#mp_set_zone_location").val(wkmp(this).parent().prev("#mp_set_zone_location").val() + ',' + searched_term);
        }

        tag.insertBefore(wkmp(this).parent().siblings("#unused_elm"), wkmp(this).parent().siblings("#unused_elm"));
        wkmp(this).parent().siblings("#unused_elm").val('');
    });

    wkmp(document).on('click', '.mp_del_tag', function () {
        var searched_term = wkmp(this).parent().data("value");
        if (searched_term) {
            var nowReq = wkmp(this).parent().siblings("#mp_set_zone_location").val();
            var new_term_1 = searched_term + ',';
            var new_term_2 = ',' + searched_term;
            if (nowReq.indexOf(new_term_1) !== -1) {
                var splitReq = nowReq.replace(searched_term + ',', "");
            } else if (nowReq.indexOf(new_term_2) !== -1) {
                var splitReq = nowReq.replace(',' + searched_term, "");
            } else {
                var splitReq = nowReq.replace(searched_term, "");
            }
            wkmp(this).parent().siblings("#mp_set_zone_location").val(splitReq);
            wkmp(this).parent().remove();
        }
    });

    // Limit search country or state result on every charater input
    wkmp(document).on('keyup', ".live-search-box", function () {
        var searchTerm = wkmp(this).val();
        var str = searchTerm.toLowerCase().replace(/\b[a-z]/g, function (letter) {
            return letter.toUpperCase();
        });
        wkmp(this).siblings('.live-search-list').find("li").each(function () {
            if (wkmp(this).is(":contains(" + str + ")") || str.length < 1) {
                wkmp(this).show();
            } else {
                wkmp(this).hide();
            }
        });
    });

    wkmp("#wkmp-submit-ask-form").on('click', function (event) {
        event.preventDefault();

        wkmp('#wkmp-subject-error').text('');
        wkmp('#wkmp-message-error').text('');

        let subject = '';
        let message = '';
        let status = true;

        subject = wkmp('#wkmp-subject').val();
        message = wkmp('#wkmp-message').val();

        if (subject.length < 3 || subject.length > 50 || !subject.match(/^[-_ a-zA-Z0-9]+$/)) {
            status = false;
            wkmp('#wkmp-subject-error').text(wkmpObj.mkt_tr.mkt40);
        }

        if (message.length < 5 || message.length > 255) {
            status = false;
            wkmp('#wkmp-message-error').text(wkmpObj.mkt_tr.mkt41);
        }

        if (status) {
            wkmp('#wkmp-submit-ask-form').prop('disabled', true); // To avoid multiple clicks.

            if (wkmp('#wkmp-seller-sendmail-form').length > 0) {
                wkmp('#wkmp-seller-sendmail-form').submit();
            }

            if (wkmp('#wkmp-seller-query-form').length > 0) {
                wkmp('#wkmp-seller-query-form').submit();
            }
        }
    });

    wkmp(document).on('click', 'a.upload_var_image_button', function (event) {
        var file_frame;
        var image_id = wkmp(this).attr('id');
        var image_val_id = 'upload_' + image_id;
        var image_url_set_id = 'wkmp_variation_product_' + image_id;
        event.preventDefault(); // If the media frame already exists, reopen it.

        if (file_frame) {
            file_frame.open();
            return;
        } // Create the media frame.

        let selection = '';


        file_frame = wp.media.frames.file_frame = wp.media({
            title: wkmp(this).data('uploader_title'),
            button: {
                text: wkmp(this).data('uploader_button_text')
            },
            multiple: false // Set to true to allow multiple files to be selected

        }); // When frame is open, select existing image attachments from custom field

        file_frame.on('open', function () {
            selection = file_frame.state().get('selection');
        });
        var query = wp.media.query();

        query.filterWithIds = function (ids) {
            return _(this.models.filter(function (c) {
                return _.contains(ids, c.id);
            }));
        }; // When images are selected, place IDs in hidden custom field and show thumbnails.


        file_frame.on('select', function () {
            selection = file_frame.state().get('selection'); // Place IDs in custom field

            var attachment_ids = selection.map(function (attachment) {
                attachment = attachment.toJSON();
                wkmp('#' + image_val_id).val(attachment.id);
                wkmp('#' + image_url_set_id).attr("src", attachment.sizes.thumbnail.url);
                return attachment.id;
            });
        }); // Finally, open the modal

        file_frame.open();
    });

    //Woodmart theme compatibility to remove anti-spam field from woocommerce my-account page registration field.
    wkmp(document).ready(function (event) {
        if (wkmp('.wd-login-title').length > 0 && wkmp('.nav.wkmp-role-selector').length > 0) {
            wkmp('input[name=email_2][id=trap]').parent().empty();
        }
    });

    /* Show sale schedule */
    wkmp(document).on("click", '.mp_sale_schedule', function () {
        wkmp(this).css('display', 'none');
        wkmp(this).siblings('.mp_cancel_sale_schedule').css('display', 'inline-block');
        wkmp(this).parents('tr').siblings('.mp_sale_price_dates_fields').css('display', 'table-row');
    });
    wkmp(document).on("click", '.mp_cancel_sale_schedule', function () {
        wkmp(this).css('display', 'none');
        wkmp(this).siblings('.mp_sale_schedule').css('display', 'inline-block');
        wkmp(this).parents('tr').siblings('.mp_sale_price_dates_fields').css('display', 'none');
    });

    //Minimum  order setting popup from seller front end.
    wkmp('body').on('click', '#wkmp_product_misc_settings', function () {
        wkmp('#wkmp_minimum_order_model').css('display', 'block');
    });

    /** Submitting minimum order form. **/
    wkmp("#wkmp-submit-min-order-amount-update").on('click', function (event) {
        event.preventDefault();

        let status = true;
        let amount_input = wkmp('input[name=_wkmp_minimum_order_amount]');

        if (amount_input.length > 0) {
            let amount = wkmp(amount_input).val().trim();
            let empty_amount_allow = parseInt(wkmp(amount_input).attr('data-empty_allow'));

            if (empty_amount_allow < 1 && (isNaN(amount) || (!isNaN(amount) && !(amount > 0)))) {
                status = false;
                wkmp('#wkmp-amount-error').text(wkmpObj.mkt_tr.mkt42);
            }
        }

        let qty_input = wkmp('input[name=_wkmp_max_product_qty_limit]');

        if (qty_input.length > 0) {
            let qty = wkmp(qty_input).val();
            let empty_qty_allow = parseInt(wkmp(qty_input).attr('data-empty_allow'));

            if (empty_qty_allow < 1 && (isNaN(qty) || (!isNaN(qty) && !(qty > 0) || (!isNaN(qty) && !(isNormalInteger(qty)))))) {
                status = false;
                wkmp('#wkmp-max-qty-limit-error').text(wkmpObj.mkt_tr.mkt47);
            }
        }

        if (status) {
            wkmp('#wkmp-amount-error').text('');
            wkmp('#wkmp-max-qty-limit-error').text('');
            wkmp('form#wkmp-seller-min-order-amount-form').submit();
        }
    });

    function isNormalInteger(str) {
        str = str.trim();
        if (!str) {
            return false;
        }
        str = str.replace(/^0+/, "") || "0";
        var n = Math.floor(Number(str));
        return n !== Infinity && String(n) === str && n >= 0;
    }

    function wkmp_validate_decimal_input(price) {
        let valid = true;

        if ('' !== price) {
            let separator = wkmpObj.mkt_tr.decimal_separator;
            let regex = new RegExp('[^\-0-9\%\\' + separator + ']+', 'gi');
            let decimalRegex = new RegExp('[^\\' + separator + ']', 'gi');

            var new_price = price.replace(regex, '');

            // Check if new value have more than one decimal point.
            if (1 < new_price.replace(decimalRegex, '').length) {
                new_price = new_price.replace(decimalRegex, '');
            }

            if (price !== new_price) {
                valid = false;
            }
        }
        return valid;
    }

    // Clearing min order amount from seller miscellaneous settings click.
    wkmp('#wkmp_clear_min_order_amount').on('click', function () {
        let amount_input = wkmp('input[name=_wkmp_minimum_order_amount]');
        let data_empty_allow = parseInt(wkmp(amount_input).attr('data-empty_allow'));
        if (data_empty_allow > 0) {
            wkmp(amount_input).attr('data-empty_allow', 0).attr('readOnly', false).attr('placeholder', wkmpObj.mkt_tr.mkt43);
            wkmp(this).text(wkmpObj.mkt_tr.mkt44);
        } else {
            wkmp(amount_input).attr('data-empty_allow', 1).val('').attr('readOnly', true).attr('placeholder', wkmpObj.mkt_tr.mkt45);
            wkmp(this).text(wkmpObj.mkt_tr.mkt46);
            wkmp('#wkmp-amount-error').text('');
        }
    });

    // Clearing maximum quantity from seller miscellaneous settings click.
    wkmp('#wkmp_clear_max_qty_limit').on('click', function () {
        let qty_input = wkmp('input[name=_wkmp_max_product_qty_limit]');
        let empty_qty_allow = parseInt(wkmp(qty_input).attr('data-empty_allow'));
        if (empty_qty_allow > 0) {
            wkmp(qty_input).attr('data-empty_allow', 0).attr('readOnly', false).attr('placeholder', wkmpObj.mkt_tr.mkt48);
            wkmp(this).text(wkmpObj.mkt_tr.mkt44);
        } else {
            wkmp(qty_input).attr('data-empty_allow', 1).val('').attr('readOnly', true).attr('placeholder', wkmpObj.mkt_tr.mkt45);
            wkmp(this).text(wkmpObj.mkt_tr.mkt46);
            wkmp('#wkmp-max-qty-limit-error').text('');
        }
    });

    /** Hiding max purchasable quantity if sold individually is enabled. **/
    wkmp('#wk_sold_individual').on('click', function () {
        if (wkmp(this).is(':checked')) {
            wkmp('.wkmp-max-product-qty-limit').hide();
        } else {
            wkmp('.wkmp-max-product-qty-limit').show();
        }
    });

    //Delete seller product - 5.2.0(21-12-28)
    wkmp('.wkmp_delete_seller_product').on('click', function () {
        let del_confirm_val = confirm(wkmpObj.mkt_tr.fajax0);
        if (del_confirm_val) {
            let del_link = wkmp(this);
            let product_id = del_link.data('product_id');
            wkmp('.wkmp-ajax-loader').removeClass('wkmp_hide');
            wkmp.ajax({
                type: 'POST',
                url: wkmpObj.ajax.ajaxUrl,
                data: {
                    action: "wkmp_delete_seller_product",
                    product_id: product_id,
                    wkmp_nonce: wkmpObj.ajax.ajaxNonce,
                },
                success: function (response) {
                    if (true === response.success) {
                        wkmp(del_link).closest('tr').remove();
                        wkmp('.wkmp-ajax-loader').html('<p class="notice success-notice">' + response.message + '</p>');
                        window.location.reload();
                    } else {
                        wkmp('.wkmp-ajax-loader').html('<p class="notice error-notice">' + response.message + '</p>');
                    }
                    setTimeout(function () {
                        wkmp('.wkmp-ajax-loader').addClass('wkmp_hide');
                    }, 2000);
                }
            });
        }
    });
    //Delete seller product ends - 5.2.0(21-12-28)

    //submitting delete shop follower form on clicking row action.
    wkmp('.wkmp-trash-shop-follower').on('click', function () {
        wkmp(this).closest('tr').find('input[type=checkbox]').attr('checked', true);
        wkmp(this).closest('form').submit();
    });

    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }

    //Creating a new attribute HTML on selecting existing attribute.
    const wkmp_create_attribute_html = (attribute_name = '') => {
        var wrapper = wkmp(".wk_marketplace_attributes");
        var x = wkmp("div.wk_marketplace_attributes > div.wkmp_attributes").length;
        var type = wkmp('#sell_pr_type').val();
        let is_global = 0;

        var html = '';
        html += '<div class="wkmp_attributes">';
        html += '<div class="box-header attribute-remove">';

        if ('' !== attribute_name) {
            html += '<div class="wkmp-attr-name-label"><label>'+wkmpProObj.name+': </label><strong>' +attribute_name+'</strong><input type="hidden" name="pro_att[' + x + '][name]" value="' + attribute_name + '"/></div>';
            html += '<select multiple="multiple" class="wkmp_product_attribute_term_values" id="wkmp_product_attribute_' + attribute_name.toLowerCase().replace(' ', '_') + '" name="pro_att[' + x + '][value][]"></select>';
            is_global = 1;
        } else {
            html += '<input type="text" class="mp-attributes-name wkmp_product_input" placeholder="' + wkmpObj.mkt_tr.mkt29 + '" name="pro_att[' + x + '][name]" value="' + attribute_name + '"/>';
            html += '<input type="text" class="option wkmp_product_input" title="' + wkmpObj.mkt_tr.mkt30 + '" placeholder="' + wkmpObj.mkt_tr.mkt30 + '" name="pro_att[' + x + '][value]" />';
        }

        html += '<input type="hidden" name="pro_att[' + x + '][position]" class="attribute_position" value="1"/>';
        html += '<span class="mp_actions">';
        html += '<button class="mp_attribute_remove btn btn-danger" type="button">' + wkmpObj.mkt_tr.mkt32 + '</button>';
        html += '</span>';
        html += '</div>';
        html += '<div class="box-inside clearfix">';
        html += '<div class="wk-mp-attribute-config">';
        html += '<div class="wkmp-checkbox-inline">';
        html += '<input type="checkbox" class="checkbox" name="pro_att[' + x + '][is_visible]" id="is_visible_page' + x + '" value="1"/>';
        html += '<label class="wkmp-visible-on-product-page-label" for="is_visible_page' + x + '">' + wkmpObj.mkt_tr.mkt33 + '</label>';
        html += '</div>';

        if ('variable' === type) {
            html += '<div class="wkmp-checkbox-inline">';
            html += '<input type="checkbox" class="checkbox" name="pro_att[' + x + '][is_variation]" id="product_att_variation_' + x + '" value="1"/>';
            html += '<label for="product_att_variation_' + x + '">' + wkmpObj.mkt_tr.mkt34 + '</label>';
            html += '</div>';
        }

        html += '<input type="hidden" name="pro_att[' + x + '][is_global]" value="'+is_global+'"/>';
        html += '</div>';
        html += '<div class="attribute-options"></div>';
        html += '</div>';
        html += '</div>';
        wkmp(wrapper).append(html);
        x++;
    }

    //Per Page Product setting popup from seller front end.
    wkmp('body').on('click', '#wkmp_products_per_page_settings', function () {
        wkmp('#wkmp_products_per_page_settings_model').css('display', 'block');
    });

     /** Submitting products per page form. **/
    wkmp("#wkmp-submit-product-per-page-update").on('click', function (event) {
        event.preventDefault();

        let status = true;
        let per_page_input = wkmp('input[name=_wkmp_products_per_page]');

        if (per_page_input.length > 0) {
            let amount = wkmp(per_page_input).val().trim();

            if ((isNaN(amount) || (!isNaN(amount) && !(amount > 0)))) {
                status = false;
                wkmp('#wkmp_product_per_page_error').text(wkmpObj.mkt_tr.mkt42);
            }
        }

        if (status) {
            wkmp('#wkmp_product_per_page_error').text('');
            wkmp('form#wkmp_seller_min_order_amount_form').submit();
        }
    });
}); // wkmp function end.



