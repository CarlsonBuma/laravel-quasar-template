<template>

    <PageWrapper :rendering="loading" >
        <template #navigation>
            <NavAdmin />
        </template>

        <!-- Price -->
        <div class="row table-width justify-center">
            <PricesTable 
                class="w-100"
                title="Manage prices"
                :prices="prices"
                @update="(price) => updatePrice(price.id, price.is_active)"
            />
            <SectionNote>
                Set the price public available, to allow users gain access to certain features within our app.<br>
                For further information, please see documentation.
            </SectionNote>
        </div>
        

        <!-- Access -->
        <q-separator class="table-width q-my-md" />
        <div class="row table-width q-mb-md justify-center">
            <AccessTable 
                class="w-100"
                title="User access"
                description="Defines user access."
                :entries="userAccess"
                @search="(email) => serachUser(email)"
                @update="(access) => updateAccess(access.id, access.is_active)"
            />
        </div>

        <div class="row justify-center table-width">
            <TransactionsTable
                class="w-100" 
                title="User transactions"
                :entries="userTransactions"
            />
            <SectionNote>
                Transactions are payments corresponding to our provided prices, users gained access.<br>
            </SectionNote>
        </div>
    </PageWrapper>

</template>

<script>
import { ref } from 'vue';
import PricesTable from './components/PricesTable.vue';
import AccessTable from './components/AccessTable.vue';
import TransactionsTable from './components/TransactionsTable.vue';

export default {
    name: 'UserAccess',
    components: {
        PricesTable, AccessTable, TransactionsTable
    },

    setup() {
        const loading = ref(true);

        return {
            loading,
        };
    },

    data() {
        return {
            prices: [],
            userAccess: [],
            userTransactions: []
        }
    },

    async mounted() {
        this.loadPrices();
    },

    methods: {

        async loadPrices(){
            try {
                this.loading = true;
                const response = await this.$axios.get('get-app-prices')
                this.prices = response.data.prices;
            } catch (error) {
                this.$toast.error(error.response ?? error);
                console.log('admin.prices.error', error.response ?? error)
            } finally {
                this.loading = false;
            }
        }, 

        async serachUser(email) {
            try {
                this.$toast.load();
                const response = await this.$axios.get("/get-app-user-access", { params: { 
                    email: email
                }});

                this.userAccess = response.data.access;
                this.userTransactions = response.data.transactions;
                console.log(response.data)
                this.$toast.success(response.data.message)
            } catch (error) {
                this.$toast.error(error.response ?? error);
                console.log('admin.user.access.error', error.response ?? error)
            }
        },

        async updatePrice(priceID, status){
            try {
                this.$toast.load();
                const response = await this.$axios.post("/update-app-price", {
                    price_id: priceID,
                    is_active: status
                });
                this.$toast.success(response.data.message)
            } catch (error) {
                this.$toast.error(error.response ?? error);
                console.log('admin.user.access.error', error.response ?? error)
            }
        }, 

        async updateAccess(accessID, status){
            try {
                this.$toast.load();
                const response = await this.$axios.post("/update-app-user-access", {
                    access_id: accessID,
                    is_active: status
                });
                this.$toast.success(response.data.message)
            } catch (error) {
                this.$toast.error(error.response ?? error);
                console.log('admin.user.access.error', error.response ?? error)
            }
        }, 
    },
};
</script>
