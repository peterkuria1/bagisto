{!! view_render_event('bagisto.shop.checkout.onepage.address.guest.before') !!}

<!-- Guest Address Vue Component -->
<v-checkout-address-guest
    :cart="cart"
    @onStepForward="stepForward"
    @onStepProcessed="stepProcessed"
></v-checkout-address-guest>

{!! view_render_event('bagisto.shop.checkout.onepage.address.guest.after') !!}

@include('shop::checkout.onepage.address.form')

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-checkout-address-guest-template"
    >
        <!-- Address Form -->
        <x-shop::form
            v-slot="{ meta, errors, handleSubmit }"
            as="div"
        >
            <form @submit="handleSubmit($event, store)">
                <!-- Guest Billing Address -->
                <div class="mb-4">
                    {!! view_render_event('bagisto.shop.checkout.onepage.address.guest.billing.before') !!}

                    <!-- Billing Address Header -->
                    <div class="flex justify-between items-center">
                        <h2 class="text-xl font-medium max-sm:text-xl">
                            @lang('shop::app.checkout.onepage.address.billing-address')
                        </h2>
                    </div>
                
                    <!-- Billing Address Form -->
                    <v-checkout-address-form
                        control-name="billing"
                        :address="cart.billing_address || undefined"
                    ></v-checkout-address-form>

                    <!-- Use for Shipping Checkbox -->
                    <x-shop::form.control-group class="flex items-center gap-2.5 !mb-0">
                        <x-shop::form.control-group.control
                            type="checkbox"
                            name="billing.use_for_shipping"
                            id="use_for_shipping"
                            for="use_for_shipping"
                            value="1"
                            @change="useBillingAddressForShipping = ! useBillingAddressForShipping"
                            ::checked="!! useBillingAddressForShipping"
                        />

                        <label
                            class="text-base text-[#6E6E6E] max-sm:text-xs ltr:pl-0 rtl:pr-0 select-none cursor-pointer"
                            for="use_for_shipping"
                        >
                            @lang('shop::app.checkout.onepage.addresses.billing.use-different-address-for-shipping')
                        </label>
                    </x-shop::form.control-group>

                    {!! view_render_event('bagisto.shop.checkout.onepage.address.guest.billing.after') !!}
                </div>

                <!-- Guest Shipping Address -->
                <div
                    class="mt-8"
                    v-if="! useBillingAddressForShipping"
                >
                    {!! view_render_event('bagisto.shop.checkout.onepage.address.guest.shipping.before') !!}

                    <!-- Shipping Address Header -->
                    <div class="flex justify-between items-center">
                        <h2 class="text-xl font-medium max-sm:text-xl">
                            @lang('shop::app.checkout.onepage.address.shipping-address')
                        </h2>
                    </div>
                
                    <!-- Shipping Address Form -->
                    <v-checkout-address-form
                        control-name="shipping"
                        :address="cart.shipping_address || undefined"
                    ></v-checkout-address-form>

                    {!! view_render_event('bagisto.shop.checkout.onepage.address.guest.shipping.after') !!}
                </div>

                <!-- Proceed Button -->
                <div class="flex justify-end mt-4">
                    <x-shop::button
                        class="primary-button py-3 px-11 rounded-2xl"
                        :title="trans('shop::app.checkout.onepage.address.proceed')"
                        ::loading="isLoading"
                        ::disabled="isLoading"
                    />
                </div>
            </form>
        </x-shop::form>
    </script>

    <script type="module">
        app.component('v-checkout-address-guest', {
            template: '#v-checkout-address-guest-template',

            props: ['cart'],

            data() {
                return {
                    useBillingAddressForShipping: true,

                    isLoading: false,
                }
            },

            created() {
                if (this.cart.billing_address) {
                    this.useBillingAddressForShipping = this.cart.billing_address.use_for_shipping;

                    this.cart.billing_address.address1 = this.cart.billing_address.address1.split('\n');
                }

                if (this.cart.shipping_address) {
                    this.cart.shipping_address.address1 = this.cart.shipping_address.address1.split('\n');
                }
            },

            methods: {
                store(params, { setErrors }) {
                    this.isLoading = true;

                    params['billing']['use_for_shipping'] = this.useBillingAddressForShipping;

                    this.moveToNextStep();

                    this.$axios.post('{{ route('shop.checkout.onepage.addresses.store') }}', params)
                        .then((response) => {
                            this.isLoading = false;

                            if (response.data.data.redirect_url) {
                                window.location.href = response.data.data.redirect_url;
                            } else {
                                this.$emit('onStepProcessed', response.data.data.shippingMethods);
                            }
                        })
                        .catch(error => {
                            this.isLoading = false;

                            if (error.response.status == 422) {
                                setErrors(error.response.data.errors);
                            }
                        });
                },

                moveToNextStep() {
                    if (this.cart.have_stockable_items) {
                        this.$emit('onStepForward', 'shipping');
                    } else {
                        this.$emit('onStepForward', 'payment');
                    }
                }
            }
        });
    </script>
@endPushOnce