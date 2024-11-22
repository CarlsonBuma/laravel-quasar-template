<template>
    
    <q-table
        flat
        :rows="transactions"
        :columns="columnsTransaction"
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

</template>

<script>
import { ref } from 'vue';
import { date } from 'quasar';
import { globalMasks } from 'src/boot/globals.js';

export default {
    name: 'TransactionsTable',

    props: {
        transactions: Array
    },

    setup() {
        const dateFormat = globalMasks.date.switzerland
        const filterInput = ref('');
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

        const columnsTransaction = [
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
            date,
            dateFormat,
            filterInput,
            customSort,
            columnsTransaction
        };
    },
};
</script>