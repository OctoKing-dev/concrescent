import Vue from 'vue';
import shop from '../../api/shop';

// initial state
const state = {
    currentlyEditingItem: {},
    cartId: null,
    items: [],
    dirty: false,
    checkoutStatus: null,
    canPay: true,
    requiresApproval: false,
    canCheckout: false,
    canEdit: true
};

function calcPromoPrice(basePrice, promoData) {
    if (promoData == null || typeof promoData.payment_promo_type === 'undefined') {
        return basePrice;
    }
    switch (promoData.payment_promo_type) {
        case 0:
            return Math.max(0, basePrice - promoData.payment_promo_amount);
        case 1:
            return Math.max(0, basePrice * (100 - promoData.payment_promo_amount) / 100);
    }
}

// getters
const getters = {
    isDirty: (state, getters, rootState) => {
        return rootState.mydata.token.length > 0 && state.dirty;
    },
    canPay: (state, getters, rootState) => {
        return rootState.mydata.token.length > 0 && state.canPay;
    },
    requiresApproval: (state, getters, rootState) => {
        return rootState.mydata.token.length > 0 && state.requiresApproval;
    },
    canCheckout: (state, getters, rootState) => {
        return rootState.mydata.token.length > 0 && state.canCheckout;
    },
    canEdit: (state, getters, rootState) => {
        return rootState.mydata.token.length > 0 && state.canEdit;
    },
    cartProducts: (state, getters, rootState) => state.items.map((badge) => {
        const badgeContext = rootState.products.badges[badge.context_code];
        const product = (badgeContext ?? []).find((product) => product.id == badge.badge_type_id);
        // if product not found, and we don't have any, assume loading
        if (product == undefined && rootState.products.gotBadges[badge.context_code] == undefined) {
            return {
                title: 'Loading...',
                price: 'Loading...',
                ...badge,
            };
        }
        const basePrice = (typeof product !== 'undefined' ? product.price : Infinity);

        const result = {
            name: (typeof product !== 'undefined' ? product.name : 'Error!'),
            price: calcPromoPrice(basePrice, badge),
            basePrice,
            ...badge,
        };

        // If we're editing, adjust some things
        if (badge.id > -1) {
            const oldproduct = badgeContext.find((product) => product.id == badge.editBadgePriorBadgeId);
            if (oldproduct != undefined)
                result.price = Math.max(0, result.price - oldproduct.price);
        }
        // if (badge.editBadgePriorAddons != undefined) {
        //  result.addons = badge.addons.filter(addon => !badge.editBadgePriorAddons.includes(addon));
        // }

        return result;
    }),
    cartCount: (state) => state.items.length || 0,

    cartTotalPrice: (state, getters, rootState) => {
        const {
            addons
        } = rootState.products;
        return getters.cartProducts.reduce((total, product) => {
            let addonTotal = 0;
            if (product.addons !== undefined && typeof product.addons.reduce === 'function') {
                let addonsSelected = [];
                if (product.editBadgePriorAddons == undefined) {
                    addonsSelected = product.addons;
                } else {
                    addonsSelected = product.addons.filter((addon) => !product.editBadgePriorAddons.includes(addon['addon_id']));
                }
                addonTotal = addonsSelected.reduce((addonTotle, saddon) => {
                    if (addons[product.badge_type_id] == undefined) return addonTotle;
                    const addon = addons[product.badge_type_id].find((addon) => addon.id == saddon['addon_id']);
                    return addonTotle + (addon == undefined ? 0 : parseFloat(addon.price));
                }, 0);
            }
            let prodPrice = parseFloat(product.price);
            if (isNaN(prodPrice)) {
                prodPrice = 0;
            }
            return total + parseFloat(product.price) + addonTotal;
        }, 0);
    },
    getProductInCart: (state) => (cartIx) => state.items[cartIx],
    getCurrentlyEditingItem: (state) => state.currentlyEditingItem,
    getContactInfo: (state) => state.latestContactInfo,
};

// actions
const actions = {
    loadCart({
        commit,
        dispatch,
        state,
        rootState
    }, cartId) {
        return new Promise((resolve, reject) => {
            if (rootState.mydata.token.length > 0) {

                if (cartId == null) {
                    commit('resetCart');
                    console.log('loading empty cart')
                    resolve();
                } else {
                    //Is it in our cache?
                    if (rootState.mydata.activeCarts) {
                        var foundCart = rootState.mydata.activeCarts.find(cart => cart.id == cartId);
                        if (foundCart != undefined) {
                            console.log('loading cart from cache', cartId)
                            commit('setcartId', cartId);
                            commit('setCheckoutStatus', {
                                errors: foundCart.errors,
                                state: foundCart.state
                            });
                            commit('setCartItems', foundCart);
                            commit('clearDirty');
                            commit('setCanPay', foundCart.canPay);
                            commit('setRequiresApproval', foundCart.RequiresApproval);
                            commit('setCanCheckout', foundCart.canCheckout);
                            commit('setCanEdit', foundCart.canEdit);
                            //Now make sure our contexts for any added badges are loaded
                            var contexts = foundCart.items.map(({
                                context_code
                            }) => context_code)
                            contexts = contexts.filter(function(value, index, self) {
                                return self.indexOf(value) === index;
                            })
                            contexts.forEach(async (context_code, ) => {

                                await dispatch('products/getContextBadges', context_code, {
                                    root: true
                                });
                                await dispatch('products/getContextQuestions', context_code, {
                                    root: true
                                });
                                await dispatch('products/getContextAddons', context_code, {
                                    root: true
                                });

                            });

                            return resolve();
                        }
                    }
                    //Just attempt a load
                    shop.loadCart(rootState.mydata.token, cartId, async (result) => {
                        console.log('loaded cart from net', cartId)
                        commit('setcartId', cartId);
                        commit('setCheckoutStatus', {
                            errors: result.errors,
                            state: result.state
                        });
                        commit('setCartItems', result);
                        commit('clearDirty');
                        commit('setCanPay', result.canPay);
                        commit('setRequiresApproval', result.RequiresApproval);
                        commit('setCanCheckout', result.canCheckout);
                        commit('setCanEdit', result.canEdit);
                        //Now make sure our contexts for any added badges are loaded
                        var contexts = result.items.map(({
                            context_code
                        }) => context_code)
                        contexts = contexts.filter(function(value, index, self) {
                            return self.indexOf(value) === index;
                        })
                        contexts.forEach(async (context_code, ) => {

                            await dispatch('products/getContextBadges', context_code, {
                                root: true
                            });
                            await dispatch('products/getContextQuestions', context_code, {
                                root: true
                            });
                            await dispatch('products/getContextAddons', context_code, {
                                root: true
                            });

                        });

                        resolve();
                    }, (er) => {
                        console.log('loading cart from net failed for ', cartId, er)
                        reject(er);
                    })
                }
            } else {
                reject({
                    error: {
                        message: "Not logged in"
                    }
                });
            }
        });
    },
    saveCart({
        commit,
        state,
        rootState
    }, promocode) {
        return new Promise((resolve, reject) => {
            if (rootState.mydata.token.length > 0) {

                shop.saveCart(rootState.mydata.token, {
                    id: state.cartId,
                    items: state.items,
                    promocode: promocode
                }, (result) => {
                    commit('setcartId', result.id);
                    commit('setCheckoutStatus', {
                        errors: result.errors,
                        state: result.state
                    });
                    commit('setCartItems', result);
                    commit('mydata/updateActiveCart',result, {root: true});
                    commit('clearDirty');
                    resolve(result.id);
                }, (er) => {
                    reject(er);
                })
            } else {
                reject({
                    error: {
                        message: "Not logged in"
                    }
                });
            }
        });
    },
    checkoutCart({
        commit,
        state,
        rootState
    }, payment_system) {
        commit('setCheckoutStatus', null);
        shop.buyProducts(
            rootState.mydata.token,
            state.cartId,
            payment_system || "PayPal",
            (data) => {
                commit('setCheckoutStatus', data);
            },
            (data) => {
                if (typeof data != "string") {
                    commit('setCheckoutStatus', data);
                } else {
                    commit('setCheckoutStatus', {
                        state: 'Failed',
                        errors: []
                    })
                }


            },
        );
    },
    checkoutCartByUUID({
        commit,
        state,
        rootState
    }, uuid) {
        commit('setCheckoutStatus', null);
        shop.checkoutCartUUID(
            uuid,
            (data) => {
                commit('setCheckoutStatus', data);
            },
            (data) => {
                if (typeof data != "string") {
                    commit('setCheckoutStatus', data);
                } else {
                    commit('setCheckoutStatus', {
                        state: 'Failed',
                        errors: []
                    })
                }


            },
        );
    },
    clearCart({
        state,
        commit,
        rootState
    }) {
        if (state.cartId != null &&
            (state.checkoutStatus == null ||
                (typeof state.checkoutStatus == 'object' && state.checkoutStatus.state != 'Completed')
            )
        ) {
            return new Promise((resolve, reject) => {
                shop.deleteCart(
                    rootState.mydata.token,
                    state.cartId,
                    (data) => {
                        commit('resetCart');
                        resolve();
                    },
                    (err) => reject
                );
            });
        } else {
            //We don't know about any cart, just clear it
            console.log('state cartID to delete not found in correct state?', state.cartId)
            commit('resetCart');
        }
    },

    addProductToCart({
        state,
        commit,
        rootState,
    }, badge) {
        commit('setCheckoutStatus', null);
        console.log('addProductToCart', badge)
        const badgeContext = rootState.products.badges[badge.context_code];
        const product = (badgeContext ?? []).find((product) => product.id == badge.badge_type_id);
        // if product not found, and we don't have any, assume loading
        if (product == undefined && rootState.products.gotBadges[badge.context_code] == undefined) {
            console.log('Attempted to add a badge without having loaded the badge info?')
        }
        //Pre-check: Are we allowed to add an item?
        if(!state.canEdit || (product.payment_deferred && state.items.length > 0 && state.checkoutStatus == null && badge.cartIx == -1)){
            console.log('should not add an item to this cart, emptying')
            commit('resetCart');
        }
        const cartItem = state.items.find((item) => item.cartIx === badge.cartIx && item.cartIx != null);
        if (!cartItem) {
            console.log('cartItem not exist')
            badge.cartIx = Math.max.apply(this, state.items.map((l) => l.cartIx)) + 1;
            if (badge.cartIx == -Infinity) {
                badge.cartIx = 0;
            }
            commit('pushProductToCart', badge);
            if (product.quantity == null | product.quantity > 0) {

                // remove 1 item from stock
                commit('products/decrementProductQuantity', {
                    id: product.id,
                    context_code: badge.context_code
                }, {
                    root: true,
                });
            }
        } else {
            console.log('cartItem in cart')
            // Item already in cart, just update it
            commit('updateProductInCart', badge);
        }
    },
    removeProductFromCart({
        state,
        commit,
    }, cartIx) {
        const cartItem = state.items[cartIx];
        if (cartItem) {
            commit('removeProductFromCart', cartItem);
        }
    },
    removePromoFromProduct({
        state,
        commit,
    }, cartIx) {
        const cartItem = state.items[cartIx];
        if (cartItem) {
            commit('removePromoFromProduct', cartItem);
        }
    },

};

// mutations
const mutations = {
    initialiseCart(state) {
        // Check if the ID exists
        if (localStorage.getItem('cart')) {
            // Replace the state object with the stored item
            // this.replaceState(
            Object.assign(state, JSON.parse(localStorage.getItem('cart')));
            // );
        }
    },
    pushProductToCart(state, item) {
        state.items.push(item);
        state.dirty = true;
    },

    updateProductInCart(state, product) {
        Vue.set(state.items, state.items.findIndex((el) => el.cartIx === product.cartIx), product);
        state.dirty = true;
    },
    removeProductFromCart(state, item) {
        const idx = state.items.indexOf(item);
        if (idx > -1) {
            state.items.splice(idx, 1);
        }
        state.dirty = true;
    },
    removePromoFromProduct(state, item) {
        item.payment_promo_code = "";
        item.payment_promo_type = undefined;
        item.payment_promo_amount = undefined;
        item.payment_promo_price = undefined;
        state.items[state.items.findIndex((el) => el.cartIx === item.cartIx)] = item;
        state.dirty = true;
    },
    setcartId(state, id) {
        state.cartId = id;
    },

    setCartItems(state, {
        items,
    }) {
        if (Array.isArray(items))
            state.items = items;
        else {
            console.log('Tried to set cart but items was not Array?', items);
        }
    },
    setCanPay(state, canPay) {
        state.canPay = canPay;
    },
    setRequiresApproval(state, requiresApproval) {
        state.requiresApproval = requiresApproval;
    },
    setCanCheckout(state, canCheckout) {
        state.canCheckout = canCheckout;
    },
    setCanEdit(state, canEdit) {
        state.canEdit = canEdit;
    },

    setCheckoutStatus(state, status) {
        state.checkoutStatus = status;
    },
    setCurrentlyEditingItem(state, item) {
        state.currentlyEditingItem = item;
    },
    clearDirty(state) {
        state.dirty = false;
    },
    resetCart(state) {
        state.cartId= null;
        //Implicit clear but not delete
        state.checkoutStatus= {
            errors: [],
            state: "NotReady"
        };
        state.items= [];
        state.dirty = false;
        state.canPay= false;
        state.requiresApproval= false;
        state.canCheckout= false;
        state.canEdit= true;
    }
};

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations,
};