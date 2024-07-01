/**
 * Admin facing js script.
 */
"use strict";
var wkJQ = jQuery.noConflict();

document.addEventListener(
	"DOMContentLoaded",
	function () {
		if (wkJQ( '.wkmp-select2' ).length) {
			wkJQ( '.wkmp-select2' ).select2();
		}
	}
);

wkJQ( document ).ready(
	function () {
		// Paying seller amount from backend by clicking 'Pay' button.
		wkJQ( '.wp-list-table.sellerorders' ).on(
			'click',
			'.admin-order-pay',
			function () {
				let confirm = window.confirm( wkmpObj.pay_confirm );
				if (confirm) {
					let order_seller_id = wkJQ( this ).data( 'id' );
					let anchor_el       = wkJQ( this );
					let parent_el_td    = wkJQ( anchor_el ).parent( 'td' );
					wkmp_update_order_status( order_seller_id, parent_el_td );
				}
			}
		);

		wkJQ( '.seller-query-revert' ).on(
			'click',
			function () {
				wkJQ( '.wkmp-text-danger' ).remove();
				let query_id      = wkJQ( this ).data( 'qid' );
				let reply_message = wkJQ( this ).prev( 'div' ).find( '.wkmp-admin_msg_to_seller' ).val();
				reply_message     = reply_message.replace( /\r\n|\r|\n/g, "<br/>" );
				if (reply_message.length < 5) {
					wkJQ( this ).prev( 'div' ).find( '.wkmp-admin_msg_to_seller' ).before( '<div class="wkmp-text-danger">Message should be more than five character</div>' );
					return false;
				}

				wkJQ.ajax(
					{
						type: 'POST',
						url: wkmpObj.ajax.ajaxUrl,
						data: {
							"action": "wkmp_admin_replied_to_seller",
							"qid": query_id,
							"reply_message": reply_message,
							"wkmp_nonce": wkmpObj.ajax.ajaxNonce
						},
						success: function (json) {
							if (json['success']) {
								alert( json['message'] );
								location.reload()
							} else {
								alert( json['message'] );
							}
						}
					}
				)
			}
		);

		if (wkJQ( ".wkmp-product-assigned-seller select" ).length) {
			wkJQ( ".wkmp-product-assigned-seller select" ).select2();
		}

		wkJQ( 'select#role' ).on(
			'change',
			function () {
				if (wkJQ( this ).val() === 'wk_marketplace_seller') {
					wkJQ( '.mp-seller-details' ).show();
				} else {
					wkJQ( '.mp-seller-details' ).hide();
				}
			}
		);

		wkJQ( '#org-name' ).on(
			'focusout',
			function () {
				var value = wkJQ( this ).val().toLowerCase().replace( /-+/g, '' ).replace( /\s+/g, '-' ).replace( /[^a-z0-9-]/g, '' );
				if ('' === value) {
					wkJQ( '#seller-shop-alert-msg' ).removeClass( 'wkmp-text-success' ).addClass( 'wkmp-text-danger' ).text( wkmpObj.shop_name );
				} else {
					wkJQ( '#seller-shop-alert-msg' ).text( "" );
				}
				wkJQ( '#seller-shop' ).val( value );
			}
		);

		wkJQ( '#seller-shop' ).on(
			'focusout',
			function () {
				var self = wkJQ( this );
				wkJQ.ajax(
					{
						type: 'POST',
						url: wkmpObj.ajax.ajaxUrl,
						data: {"action": "wkmp_check_myshop", "shop_slug": self.val(), "wkmp_nonce": wkmpObj.ajax.ajaxNonce},
						success: function (response) {
							if (0 === response) {
								wkJQ( '#seller-shop-alert' ).removeClass( 'wkmp-text-success' ).addClass( 'wkmp-text-danger' );
								wkJQ( '#seller-shop-alert-msg' ).removeClass( 'wkmp-text-success' ).addClass( 'wkmp-text-danger' ).text( 'Not Available' );
							} else if (2 === response) {
								wkJQ( '#seller-shop-alert' ).removeClass( 'wkmp-text-success' ).addClass( 'wkmp-text-danger' );
								wkJQ( '#seller-shop-alert-msg' ).removeClass( 'wkmp-text-success' ).addClass( 'wkmp-text-danger' ).text( 'Already Exists' );
							} else {
								wkJQ( '#seller-shop-alert' ).removeClass( 'wkmp-text-danger' ).addClass( 'wkmp-text-success' );
								wkJQ( '#seller-shop-alert-msg' ).removeClass( 'wkmp-text-danger' ).addClass( 'wkmp-text-success' ).text( 'Available' );
							}
						}
					}
				);
			}
		);

		// Changing dashboard from frontend to backend and vice versa.
		wkJQ( '#wp-admin-bar-wkmp-front-dashboard a' ).on(
			'click',
			function (ev) {
				ev.preventDefault();
				wkJQ( this ).append( '<span class="dashicons dashicons-update loading"></span>' );
				wkJQ.ajax(
					{
						type: 'POST',
						url: wkmpObj.ajax.ajaxUrl,
						data: {
							"action": "wkmp_change_seller_dashboard",
							"change_to": 'front_dashboard',
							"wkmp_nonce": wkmpObj.ajax.ajaxNonce
						},
						success: function (data) {
							if (data) {
								window.location.href = data.redirect;
							}
						}
					}
				)
			}
		);

		// Showing/hiding maximum qty field depending on Sold individually checkbox status.
		wkJQ( 'input#_sold_individually' ).on(
			'change',
			function () {
				if (wkJQ( this ).is( ':checked' )) {
					wkJQ( '._wkmp_max_product_qty_limit_field' ).hide();
				} else {
					wkJQ( '._wkmp_max_product_qty_limit_field' ).show();
				}
			}
		).trigger( 'change' );

		// Performing order action on seller action.
		wkJQ( 'select.wkmp_seller_order_action' ).on(
			'change',
			function () {
				let select_el   = wkJQ( this );
				let action_data = wkJQ( select_el ).val();
				if (action_data) {
					let confirm = window.confirm( wkmpObj.order_status_confirm );
					if (confirm) {
						let parent_el_td = wkJQ( select_el ).parent( 'td' );
						wkmp_update_order_status( action_data, parent_el_td );
					} else {
						wkJQ( select_el ).prop( 'selectedIndex', 0 );
					}
				}
			}
		);

		// Show pro upgrade pop-up on clicking lock icon.
		wkJQ( '.wkmp_pro_lock' ).on(
			'click',
			function () {
				wkJQ( '.wkmp_show_pro_upgrade_poupup, .wkmp-popup-overlay' ).show();
			}
		);

		wkJQ( '.wkmp_pro_upgrade_popup_close, .wkmp-popup-overlay, .wkmp_show_pro_upgrade_poupup .upgrade-btns a' ).on(
			'click',
			function () {
				wkJQ( '.wkmp_show_pro_upgrade_poupup, .wkmp-popup-overlay' ).hide();
			}
		);
		// Show pro upgrade pop-up on clicking lock icon end.

		/**
		 * Common function for paying and updating order status.
		 */
		function wkmp_update_order_status(action_data, parent_td_el) {
			wkJQ.ajax(
				{
					type: 'POST',
					url: wkmpObj.ajax.ajaxUrl,
					data: {
						"action": "wkmp_update_seller_order_status",
						"action_data": action_data,
						"wkmp_nonce": wkmpObj.ajax.ajaxNonce
					},
					beforeSend: function () {
						parent_td_el.html( '<span class="wkmp-order-status spinner"></span>' );
					},
					success: function (response) {
						if (true === response.success) {
							parent_td_el.find( '.wkmp-order-status.spinner' ).replaceWith( response.new_action_html );
							wkJQ( '.wkmp-admin-notice.is-dismissible' ).html( '<p>' + response.message + '</p>' ).removeClass( 'wkmp-hide' );
						} else {
							parent_td_el.find( '.wkmp-order-status.spinner' ).replaceWith( '<button class="button button-primary" disabled>' + wkmpObj.failed_btn + '</button>' );
							wkJQ( '.wkmp-admin-notice.is-dismissible' ).html( '<p>' + response.message + '</p>' ).removeClass( 'wkmp-hide' );
						}
					},
				}
			);
		}

		// Displaying Marketplace addons.
		if (wkJQ( '.__wk_ext-extension-body' ).length) {
			setTimeout(
				() => {
                wkmp_trigger_mp_addon_click( 1000 );
				},
				1000
			);
		}

		function wkmp_trigger_mp_addon_click(time) {
			let interval = 1000;

			if (wkJQ( ".__wk_ext-active-tab" ).length) {
				let c_url  = window.location.href; // Current URL.
				let params = new URLSearchParams( new URL( c_url ).search );
				if (params.has( 'ext_tab' )) {
					wkJQ( '.__wk_ext-border-color ul li:nth-child(' + params.get( 'ext_tab' ) + ')' ).trigger( 'click' );
				}
			} else {
				setTimeout(
					() => {
                    if (time < 9000) {
                        time = time + interval;
                        wkmp_trigger_mp_addon_click( time );
                    }
					},
					interval
				);
			}
		}

		// Pro Notice management via cookie.
		let notice_id = wkJQ( '.wkmp-upgrade-pro-banner-notice' ).data( 'admin_id' ) || '',
		cookieName    = 'wkmp_pro_banner_notice' + notice_id;

		// Check the value of that cookie and show/hide the notice accordingly
		if ( 'hidden' === wpCookies.get( cookieName ) ) {
			wkJQ( '.wkmp-upgrade-pro-banner-notice' ).hide();

			cookieName = 'wkmp_pro_toast_notice' + notice_id;

			if ('hidden' === wpCookies.get( cookieName )) {
				wkJQ( '.wkmp-toast-notice.upgrade-to-pro' ).hide();
			} else {
				wkJQ( '.wkmp-toast-notice.upgrade-to-pro' ).show();
			}

			// Set a cookie and hide the upgrade to pro toast notice when the dismiss button is clicked
			wkJQ( '.wkmp-toast-notice.upgrade-to-pro .notice-dismiss' ).on(
				'click',
				function ( event ) {
					wpCookies.set( cookieName, 'hidden', { path: '/' } );
					wkJQ( '.wkmp-toast-notice.upgrade-to-pro' ).hide();
					event.preventDefault();
				}
			);

		} else {
			wkJQ( '.wkmp-upgrade-pro-banner-notice' ).show();
		}

		// Set a cookie and hide the pro banner notice when the dismiss button is clicked
		wkJQ( '.wkmp-upgrade-pro-banner-notice .notice-dismiss' ).on(
			'click',
			function ( event ) {
				wpCookies.set( cookieName, 'hidden', { path: '/' } );
				wkJQ( '.wkmp-upgrade-pro-banner-notice' ).hide();
				event.preventDefault();
			}
		);
	}
); // document.ready ends here.
