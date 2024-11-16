<template>

    <PageWrapper 
        title="My access" 
        :rendering="loading" 
        leftDrawer
        drawerTitle="My Avatar" 
    >
        <template #leftDrawer>
            <NavUser />
        </template>

        <template #actions>
            <q-btn 
                @click="$router.push('/pricing')" 
                outline 
                color="primary" 
                icon="auto_awesome"
                label="See services" 
            />
        </template>

        <!-- Price -->
        <q-table
            flat
            :rows="prices"
            :columns="columns_subscriptions"
            title="Provided services"
            row-key="id"
            class="table-width q-mb-md"
            :sort-method="customSort"
            :pagination="{
                rowsPerPage: 5
            }"
        >
            <template v-slot:header-cell="props">
                <q-th :props="props">
                    {{ props.col.label }}
                    <q-icon v-if="props.col.note" name="help_outline" size="14px" color="primary" >
                        <q-tooltip>
                            <p class="q-ma-none tooltip-width">{{ props.col.note }}</p>
                        </q-tooltip> 
                    </q-icon>
                </q-th>
            </template>
            <template v-slot:body="props">
                <q-tr :props="props">
                    <q-td key="id" :props="props">
                        {{ props.rowIndex + 1 }}
                    </q-td>
                    <q-td key="name" :props="props">
                        {{ props.row.name }}
                    </q-td>
                    <q-td key="billing_type" :props="props">
                        {{ props.row.type }}
                    </q-td>
                    <q-td key="billing_period" :props="props">
                        {{ 
                            props.row.billing_frequency 
                                ? props.row.billing_frequency + ' ' + props.row.billing_interval 
                                : props.row.duration_months + ' month' 
                        }}
                    </q-td>
                    <q-td key="trial_mode" :props="props">
                        {{ 
                            props.row.trial_frequency 
                                ? props.row.trial_frequency + ' ' + props.row.trial_interval + 's'
                                : 'none' 
                        }}
                    </q-td>
                    <q-td key="price" :props="props">
                        {{ props.row.currency_code + ' ' + props.row.price }}
                    </q-td>
                    <q-td key="status" :props="props">
                        <q-btn 
                            v-if="props.row.is_subscription"
                            label="Deactivate"
                            icon="key"
                            size="sm"
                            color="purple"
                            outline
                            @click="props.row.type === 'subscription'
                                ? confirmCancelSubscription(props.row)
                                : null
                            "
                        />
                        <q-btn 
                            v-else
                            label="Get access"
                            icon="generating_tokens"
                            size="sm"
                            color="primary"
                            outline
                            @click="openPaymentGateway(props.row.price_token)"
                        />
                    </q-td>
                </q-tr>
            </template>
        </q-table>

        <!-- Transactions -->
        <q-table
            flat
            :rows="transactions"
            :columns="columns_transactions"
            title="Transactions"
            row-key="id"
            class="table-width"
            :sort-method="customSort"
            :filter="filterInput"
            :pagination="{
                rowsPerPage: 7
            }"
        >
            <template v-slot:top-right>
                <q-input borderless dense debounce="300" v-model="filterInput" placeholder="Search">
                    <template v-slot:append>
                        <q-icon name="search" />
                    </template>
                </q-input>
            </template>
            <template v-slot:body="props">
                <q-tr :props="props">
                    <q-td key="id" :props="props">
                        {{ props.rowIndex + 1 }}
                    </q-td>
                    <q-td key="name" :props="props">
                        {{ props.row.name }}
                    </q-td>
                    <q-td key="status" :props="props">
                        {{ props.row.status }}
                    </q-td>
                    <q-td key="quantity" :props="props">
                        {{ props.row.quantity }}<br>
                    </q-td>
                    <q-td key="price" :props="props">
                        {{ props.row.currency_code + ' ' + props.row.total }}<br>
                    </q-td>
                    <q-td key="tax" :props="props">
                        {{ props.row.tax }}
                    </q-td>
                    <q-td key="created_at" :props="props">
                        {{ date.formatDate(props.row.created_at, dateFormat) }}
                    </q-td>
                    <q-td key="expiration_date" :props="props">
                        {{ date.formatDate(props.row.expiration_date, dateFormat) }}
                    </q-td>
                    <q-td key="active" :props="props">
                        <q-icon 
                            name="verified" 
                            :color="props.row.is_active 
                                && date.formatDate(props.row.expiration_date, 'YYYY-MM-DD') 
                                    > date.formatDate(new Date(), 'YYYY-MM-DD')  
                                ? 'green' 
                                : 'grey'
                            "
                        />
                    </q-td>
                </q-tr>
            </template>
        </q-table>
        <SectionNote>
            *Transactions are payments corresponding to our provided services, you gained access.<br>
            For further informations please check our
            <router-link to="/legal">Terms &amp; Conditions</router-link>.
        </SectionNote>
        
        <!-- Paddle -->
        <PaddleSubscription 
            @paddleEvents="(data) => paddleEventHandling(data)"
            @loaded="(PaddleCheckout) => this.Paddle = PaddleCheckout"
        />
    </PageWrapper>

</template>

<script>
import { ref } from 'vue';
import { date } from 'quasar';
import { globalMasks } from 'src/boot/globals.js';
import NavUser from 'src/components/navigation/NavUser.vue';
import PaddleSubscription from 'components/PaddleSubscription.vue';
import SectionNote from 'components/SectionNote.vue';

export default {
    name: 'UserAccess',
    components: {
        NavUser,  PaddleSubscription, SectionNote
    },

    setup() {
        const Paddle = ref(null);
        const filterInput = ref('');

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

        const customSort = (rows, sortBy, descending) => {
            const data = [...rows]
            if (sortBy) {
                data.sort((a, b) => {
                    const x = descending ? b : a
                    const y = descending ? a : b
                    if (sortBy === 'name' || sortBy === 'expiration_date')
                        return x[sortBy] > y[sortBy] ? 1 : x[sortBy] < y[sortBy] ? -1 : 0
                    else if(sortBy === 'price')
                        return parseFloat(x[sortBy]) - parseFloat(y[sortBy]);
                })
            }
            return data
        };

        const columns_subscriptions = [
            {
                name: 'id',
                label: 'ID',
                field: 'id',
                align: 'left',
            }, {
                name: 'name',
                label: 'Product',
                field: 'name',
                align: 'left',
                sortable: true
            }, {
                name: 'billing_type',
                label: 'Billing type',
                field: 'billing_type',
                align: 'left',
                note: 'Depending on type, transactions will be generated automatically or manually (one-time). You are able to cancel subscriptions any time.'
            }, {
                name: 'billing_period',
                label: 'Access period',
                field: 'billing_period',
                align: 'left',
                note: 'Amount of time, you will gain access to the provided service per transaction.'
            }, {
                name: 'trial_mode',
                label: 'Trial mode',
                field: 'trial_mode',
                align: 'left',
            }, {
                name: 'price',
                label: 'Price',
                field: 'price',
                align: 'left',
                sortable: true
            }, {
                name: 'status',
                label: 'Status',
                field: 'status',
            },
        ];

        const columns_transactions = [
            {
                name: 'id',
                label: 'ID',
                field: 'id',
                align: 'left',
            }, {
                name: 'name',
                label: 'Product',
                field: 'name',
                align: 'left',
                sortable: true
            }, {
                name: 'status',
                label: 'Status',
                field: 'status',
                align: 'left',
            }, {
                name: 'quantity',
                label: 'Quantity',
                field: 'quantity',
                align: 'left',
            }, {
                name: 'price',
                label: 'Total (incl. Tax)',
                field: 'price',
                align: 'left',
            }, {
                name: 'tax',
                label: 'Tax',
                field: 'tax',
                align: 'left',
            }, {
                name: 'created_at',
                label: 'Billed at',
                field: 'created_at',
                align: 'left',
            }, {
                name: 'expiration_date',
                label: 'Expiration',
                field: 'expiration_date',
                align: 'left',
                sortable: true
            }, {
                name: 'active',
                label: 'Access',
                field: 'active',
            },
        ];

        return {
            dateFormat: globalMasks.date.switzerland,
            loading: ref(false),
            showDialog: ref(false),
            openPaymentGateway,
            Paddle,
            filterInput,
            columns_subscriptions,
            columns_transactions,
            customSort,
            date
        };
    },

    data() {
        return {
            transactionInitializedSuccessfully: false,
            prices: [],
            subscriptions: [],
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
                this.$toast.error(error.response ? error.response : error);
                console.log('load-access-error', error.response ? error.response : error)
            } finally {
                this.loading = false;
            }
        }, 

        async cancelSubscription(price) {
            try {
                this.$toast.load();
                const response = await this.$axios.post('cancel-user-subscription' , {
                    'price_token': price.price_token,
                });
                this.$toast.success(response.data.message);
                price.is_subscription = false;
            } catch (error) {
                this.$toast.error(error.response ? error.response : error);
                console.log('subscription-cancel-error', error.response ? error.response : error)
            }
        },

        async paddleEventHandling(data) {
            try {
                const transactionID = data.data?.transaction_id;
                const customerID = data.data?.customer?.id;
                
                if(data?.name === 'checkout.completed') {
                    this.transactionInitializedSuccessfully = true;
                    await this.$axios.post('set-user-client-access', {
                        transaction_token: transactionID,
                        customer_token: customerID
                    });
                    return;
                }

                // Validation, when transaction completed
                else if (
                    this.transactionInitializedSuccessfully 
                    && data?.name === 'checkout.closed' 
                ) {
                    this.$toast.load();
                    const response = await this.$axios.post('verify-user-client-access' , {
                        'transaction_token': transactionID,
                    })
                    this.transactionInitializedSuccessfully = false;
                    this.$toast.success(response.data.message);

                    // Set Access,
                    // no access to subscribe anymore
                    if(response.data.access_token) {
                        this.$user.setUserAccess(
                            response.data.access_token, 
                            response.data.expiration_date
                        );

                        // Check if its a subscription
                        this.prices.forEach((price, index) => {
                            if(price.id === response.data.price_id) 
                                this.prices[index].is_subscription = true;
                        });
                    }
                }
            } catch (error) {
                const errorMessage = error.response ? error.response.data : error;
                this.$toast.error(errorMessage)
                console.log('subscription-checkout', errorMessage)
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
