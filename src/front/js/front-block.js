/** Front js for modifying cart and checkout blocks Since MP Lite: 1.0.3 */

"use strict";
var wkmp = jQuery.noConflict();

wkmp(function () {
    const { registerCheckoutFilters } = window.wc.blocksCheckout;

    // Updating 'Proceed to Checkout' button text to 'Not Purchasable' if cart validation failed.
    const modifyProceedToCheckoutButtonLabel = (defaultValue, extensions, args) => {
        if (cartHasMPErrors(args.cart)) {
            return wkmpObj.not_purchasable
        }
        return defaultValue;
    }

    // Updating 'Proceed to Checkout' button link to 'Javascript:void(0)' if cart validation failed.
    const modifyProceedToCheckoutButtonLink = (defaultValue, extensions, args) => {
        if (cartHasMPErrors(args.cart)) {
            return '#';
        }
        return defaultValue
    }
    const modifyPlaceOrderButtonLabel = (defaultValue, extensions) => {
        let cart = wp.data.select(wc.wcBlocksData.CART_STORE_KEY).getCartData();

		if (cartHasMPErrors(cart)) {
			return wkmpObj.not_purchasable
		}

        return defaultValue
    }

    registerCheckoutFilters('wkmp-proceed-checkout-button', {
        proceedToCheckoutButtonLink: modifyProceedToCheckoutButtonLink,
        proceedToCheckoutButtonLabel: modifyProceedToCheckoutButtonLabel,
        placeOrderButtonLabel: modifyPlaceOrderButtonLabel,
    });

    wp.data.subscribe( () => {
        let cart = wp.data.select(wc.wcBlocksData.CART_STORE_KEY).getCartData();

		if (cartHasMPErrors(cart)) {
			wkmp('.wc-block-cart__submit-container').hide();
		}else{
			wkmp('.wc-block-cart__submit-container').show();
		}
    });

    const cartHasMPErrors = (cartData) => {
        let wkmp_error = cartData.errors.map( error => {
			return (0 === error.code.indexOf('wkmp_error_'));
        })
        return wkmp_error.includes(true)
    }
});
