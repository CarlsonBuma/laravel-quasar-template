<template>
    
    <q-table
        flat
        class="table-width q-mb-md"
        title="Products"
        row-key="id"
        :rows="prices"
        :columns="columnsProducts"
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
                            ? props.row.billing_frequency + 'x ' + props.row.billing_interval 
                            : props.row.duration_months 
                                ? props.row.duration_months + ' month' 
                                : 'none'
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
                    
                    <!-- Cancel subscriptions -->
                    <q-btn 
                        v-if="props.row.has_active_subscription"
                        label="Deactivate"
                        icon="generating_tokens"
                        size="sm"
                        color="purple"
                        outline
                        @click="$emit('cancel', props.row)"
                    />

                    <!-- Get Access -->
                    <q-btn 
                        v-else
                        icon="generating_tokens"
                        size="sm"
                        outline
                        :label="props.row.has_access && !props.row.is_subscription 
                            ? 'Active'
                            : 'Get access'"
                        :color="props.row.has_access && !props.row.is_subscription 
                            ? 'green' 
                            : 'primary'"
                        @click="$emit('action', props.row)"
                    />
                </q-td>
            </q-tr>
        </template>
    </q-table>

</template>

<script>
export default {
    name: 'PricesTable',

    props: {
        prices: Array
    },

    emits: [
        'action',
        'cancel'
    ],

    setup() {
        const columnsProducts = [
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

        return {
            columnsProducts
        };
    },
};
</script>