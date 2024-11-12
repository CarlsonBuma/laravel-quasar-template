<template>

    <div>
        <q-card-section>
            <q-input label="Enter email" v-model="searchUserEmail"/>
            <div class="q-my-md text-right">
                <q-btn label="Search" icon="group_add" @click="search(searchUserEmail, collaboration.id)" />
                <q-btn class="q-ml-sm" icon="restore" @click="users = []" />
            </div>
        </q-card-section>
        <q-separator />
        <q-table
            :rows="users"
            :columns="collaboratorColumn"
            row-key="email"
        >
            <template v-slot:body="props">
                <q-tr :props="props">
                    <q-td key="name" :props="props">
                        {{ props.row.name }}
                    </q-td>
                    <q-td key="email" :props="props">
                        {{ props.row.email }}
                    </q-td>
                    <q-td key="collaborations" :props="props">
                        {{ props.row.collaborations }}
                    </q-td>
                    <q-td key="request" :props="props">
                        <q-btn 
                            :disabled="props.row.ongoing_collaborations > 0"
                            label="Release certificate" 
                            size="sm" 
                            color="green" 
                            icon="new_releases" 
                            @click="$emit('releaseCollaborator', props.row.id)"
                        />
                    </q-td>
                </q-tr>
            </template>
        </q-table>
    </div>
    
</template>

<script>
import { ref } from 'vue';

export default {
    name: 'CollaboratorSearch',

    props: {
        collaboration: Object,
    },

    emits: [
        'releaseCollaborator'
    ],

    setup() {
        const users = ref([]);
        const searchUserEmail = ref('');
        const collaboratorColumn = ref([
            {
                name: 'name',
                field: 'name',
                required: true,
                label: 'Name',
                align: 'left',
                sortable: true
            }, {
                name: 'email',
                field: 'email',
                required: true,
                label: 'Email',
                align: 'left',
                sortable: true
            }, {
                name: 'collaborations',
                field: 'collaborations',
                required: true,
                label: 'Closed collaborations',
                align: 'left',
                sortable: true
            }, {
                name: 'request',
                field: 'request',
                required: true,
                label: 'Send request',
                align: 'right',
            }
        ]);

        return {
            users,
            collaboratorColumn,
            searchUserEmail,
        };
    },

    methods: {
        async search(email, collaborationID)
        {
            try {
                if(!email) throw 'Email field is required.'
                this.$toast.load();
                const response = await this.$axios.get('/search-new-collaborator', { params: { 
                    collaboration_id: collaborationID,
                    email: email 
                }});

                this.$toast.done();
                if(response.data.collaborator) 
                    this.users.push(response.data.collaborator);
            } catch (error) {
                this.$toast.error(error.response ? error.response : error)
            } finally {
                this.searchUserEmail = '';
            }
        },
    },
};
</script>
