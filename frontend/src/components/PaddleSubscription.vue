<template>
    <div></div>
</template>

<script>
import { ref, onMounted } from 'vue';
import { initializePaddle } from '@paddle/paddle-js';

export default {
    name: 'PaddleSubscription',

    emits: [
        'paddleEvents',
        'loaded'
    ],

    // Initialize Paddle instance
    // Docs: https://developer.paddle.com/
    setup(props, context) {
        const PaddleCheckout = ref(null);
        onMounted(async () => {
            try {
                PaddleCheckout.value = await initializePaddle({
                    environment: process.env.APP_PADDLE_ENVIRONMENT
                        ? process.env.APP_PADDLE_ENVIRONMENT
                        : 'sandbox',
                    token: process.env.APP_PADDLE_PUBLIC_KEY,
                    eventCallback: function(data) {
                        context.emit('paddleEvents', data)
                    }
                });
                if (PaddleCheckout.value) {
                    context.emit('loaded', PaddleCheckout.value)
                }
            } catch (error) {
                console.error('Error initializing Paddle:', error);
            }
        });

        // Open Paddle Checkout
        const paddleOpenCheckout = async () => {
            await PaddleCheckout.value?.Checkout.open({
                settings: {
                    showAddDiscounts: false,
                    allowLogout: false,
                    // successUrl: 'URL'
                },
                items: [{ 
                    priceId: props['priceID'], 
                    quantity: 1 
                }],
            });
        }

        return {
            PaddleCheckout,
            paddleOpenCheckout,
        };
    },

    data() {
        return {
            //
        }
    },

    mounted() {
        // 
    },

    methods: {
        // None
    },
};
</script>


