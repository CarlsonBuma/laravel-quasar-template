<template>
    
    <q-table
        flat
        :rows="entries"
        :columns="columnsTransaction"
        :title="title"
        row-key="id"
        :pagination="{
            rowsPerPage: 7
        }"
    >
        <!-- Search user -->
        <template v-slot:top-right>
            <q-input borderless dense debounce="300" v-model="searchString" placeholder="Search access by email...">
                <template v-slot:append>
                    <q-btn icon="search" color="primary" @click="$emit('search', searchString)"/>
                </template>
            </q-input>
        </template>

        <template v-slot:body="props">
            <q-tr :props="props">
                <q-td key="id" :props="props">
                    {{ props.rowIndex + 1 }}
                </q-td>
                <q-td key="token" :props="props">
                    {{ props.row.price.name ?? 'No price defined.' }}
                </q-td>
                <q-td key="token" :props="props">
                    {{ props.row.access_token }}
                </q-td>
                <q-td key="is_active" :props="props">
                    <q-checkbox v-model="props.row.is_active"/>
                </q-td>
                <q-td key="quantity" :props="props">
                    {{ props.row.quantity }}<br>
                </q-td>
                <q-td key="expiration_date" :props="props">
                    {{ props.row.expiration_date }}
                </q-td>
                <q-td key="has_access" :props="props">
                    <q-icon name="verified" :color="props.row.access?.id ? 'green' : 'grey'" />
                </q-td>
                <q-td key="actions" :props="props">
                    <q-btn outline icon="update" size="sm" color="primary" @click="$emit('update', props.row)" />
                </q-td>
            </q-tr>
        </template>
    </q-table>

</template>

<script>
import { ref } from 'vue';

export default {
    name: 'AccessTable',

    props: {
        title: String,
        entries: Array
    },

    emits: [
        'search',
        'update'
    ],

    setup() {
        const searchString = ref('');
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
                name: 'token',
                label: 'Access token',
                field: 'token',
                align: 'left',
                sortable: true
            }, {
                name: 'is_active',
                label: 'is active',
                field: 'is_active',
                align: 'left',
            }, {
                name: 'quantity',
                label: 'Quantity',
                field: 'quantity',
                align: 'left',
            }, {
                name: 'expiration_date',
                label: 'Expires',
                field: 'expiration_date',
                align: 'left',
            }, {
                name: 'has_access',
                label: 'has access',
                field: 'has_access',
                align: 'center',
            }, {
                name: 'actions',
                label: 'Actions',
                field: 'actions',
            },
        ];

        return {
            searchString,
            columnsTransaction
        };
    },
};
</script>