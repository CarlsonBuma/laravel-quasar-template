<template>

    <PageWrapper :rendering="loading" >
        <template #navigation>
            <NavUser />
        </template>

        <!-- Price -->
        <PricesTable 
            :prices="prices"
            @action="(price) => openPaymentGateway(price.price_token)"
            @cancel="(price) => confirmCancelSubscription(price)"
        />

        <!-- Transactions -->
        <div class="row w-100 justify-center">
            <TransactionsTable :transactions="transactions"/>
            <SectionNote>
                *Transactions are payments corresponding to our provided products, you gained access.<br>
                For further informations please check our
                <router-link to="/legal">Terms &amp; Conditions</router-link>.
            </SectionNote>
        </div>
        
        <!-- Paddle -->
        <PaddleSubscription 
            @paddleEvents="(data) => paddleEventHandling(data)"
            @loaded="(PaddleCheckout) => this.Paddle = PaddleCheckout"
        />
    </PageWrapper>

</template>

<script>
import { ref } from 'vue';
import PaddleSubscription from 'components/PaddleSubscription.vue';
import PricesTable from './components/PricesTable.vue';
import TransactionsTable from './components/TransactionsTable.vue';

export default {
    name: 'UserAccess',
    components: {
        PaddleSubscription, TransactionsTable, PricesTable
    },

    setup() {

        // Defaults
        const loading = ref(true);
        const showDialog = ref(false);

        // Paddle Checkout
        const Paddle = ref(null);
        const openPaymentGateway = (priceToken) => {
            Paddle.value?.Checkout.open({
                settings: {
                    showAddDiscounts: false,
                    allowLogout: false,
                    // successUrl: 'URL'
                },
                items: [{ 
                    priceId: priceToken, 
                    quantity: 1 
                }],
            });
        };

        return {
            loading,
            showDialog,
            openPaymentGateway,
            Paddle
        };
    },

    data() {
        return {
            prices: [],
            transactions: [],
        }
    },

    async mounted() {
        this.loadAccess();
    },

    methods: {

        async loadAccess(){
            try {
                this.loading = true;
                const response = await this.$axios.get('load-user-access')
                this.prices = response.data.prices;
                this.transactions = response.data.transactions;
            } catch (error) {
                this.$toast.error(error.response ?? error);
                console.log('user.access.error', error.response ?? error)
            } finally {
                this.loading = false;
            }
        }, 

        // Client checkout completed
        // Initialize client checkout
        async paddleEventHandling(data) {
            try {
                if(data?.name === 'checkout.completed') {
                    const transactionID = data.data?.transaction_id;
                    const customerID = data.data?.customer?.id;
                    await this.$axios.post('set-user-client-access', {
                        transaction_token: transactionID,
                        customer_token: customerID
                    });

                    // Verify transaction by webhook interval
                    this.checkTransactionWebhookVerificationInterval(transactionID)
                }
            } catch (error) {
                this.$toast.error(error.response ?? error)
                console.log('user.checkout.error', error.response ?? error)
            }
        },

        // Set interval a 5sec
        // Verify transaction and set user-access
        checkTransactionWebhookVerificationInterval(transactionID) {
            const intervalId = setInterval(async () => {
                try {
                    // Request
                    const response = await this.$axios.post('verify-user-client-access', {
                        'transaction_token': transactionID,
                    });

                    // Check new access set
                    if(response.data.access_token) {
                        this.$user.setUserAccess(
                            response.data.access_token, 
                            response.data.expiration_date
                        );

                        // Check if its a subscription
                        this.prices.forEach((price, index) => {
                            if(price.id === response.data.price_id && price.is_subscription) 
                                this.prices[index].has_active_subscription = true;
                        });

                        // Clear interval
                        this.$toast.success(response.data.message);
                        clearInterval(intervalId);
                    }
                } catch (error) {
                    clearInterval(intervalId);
                    this.$toast.error(error.response ?? error)
                    console.log('user.transaction.verification.error', error.response ?? error);
                }
            }, 5000);
        },

        // Cancel user subscription
        // if price-type === 'subscription'
        async cancelSubscription(price) {
            try {
                this.$toast.load();
                const response = await this.$axios.post('cancel-user-subscription' , {
                    'price_token': price.price_token,
                });
                this.$toast.success(response.data.message);
                price.has_active_subscription = false;
            } catch (error) {
                this.$toast.error(error.response ?? error);
                console.log('subscription.cancel.error', error.response ?? error)
            }
        },

        confirmCancelSubscription(price) {
            this.$q.dialog({
                title: 'Cancel: ' + price.name,
                message: 'Sure you want to cancel your subscription? You will not have access to the provided service after latest transaction expires.',
                cancel: true,
            }).onOk(() => {
                this.cancelSubscription(price)
            })
        },
    },
};
</script>
