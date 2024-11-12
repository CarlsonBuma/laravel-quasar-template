<template>

    <!-- Collaborators -->
    <q-table
        :rows="collaborators"
        :columns="collaboratorsColumn"
        row-key="email"
        flat
    >
        <template v-slot:body="props">
            <q-tr :props="props">
                <q-td key="collaborator" :props="props">
                    <span class="_hover" @click="$router.push('/community/collaborators/' + props.row.user.id)">
                        {{ props.row.user.name }}<br>
                    </span>
                    <span>{{ props.row.user.email }}</span>
                </q-td>
                <q-td key="date_requested" :props="props">
                    <span>{{ props.row.date_requested ?? 'Pending...' }}</span>
                </q-td>
                <q-td key="date_released" :props="props">
                    <span v-if="props.row.date_released">{{ props.row.date_released }}</span>
                    <q-btn 
                        v-else 
                        icon="diversity_1" 
                        label="Release collaboration" 
                        size="sm" 
                        color="green" 
                        @click="releaseCollaboration(props.row)"
                    />
                </q-td>
                <q-td key="date_issued" :props="props">
                    <span v-if="props.row.date_confirmed">{{ props.row.date_issued }}</span>
                    <q-btn 
                        v-else-if="props.row.date_requested && props.row.date_released && props.row.date_issued"
                        icon="emoji_events" 
                        :label="props.row.date_issued" 
                        size="sm" outline
                        color="green" 
                        @click="reopenCollaboration(props.row)"
                    />
                    <q-btn 
                        v-else 
                        icon="emoji_events" 
                        :disabled="!props.row.date_requested || !props.row.date_released" 
                        label="Issue collaboration" 
                        size="sm" 
                        color="green" 
                        @click="issueCollaboration(props.row)"
                    />
                </q-td>
                <q-td key="date_confirmed" :props="props">
                    {{ props.row.date_confirmed ?? 'Pending...' }}
                </q-td>
                <q-td key="manage" :props="props">
                    <q-btn-dropdown dropdown-icon="more_vert" size="12px" flat dense round>
                        <q-list separator>
                            <q-item 
                                clickable v-close-popup :disabled="props.row.date_confirmed">
                                <q-item-section avatar>
                                    <q-icon color="red" name="do_not_disturb" />
                                </q-item-section>
                                <q-item-section>
                                    <q-item-label class="text-caption" @click="confirmDelete(props.row)">Remove collaborator</q-item-label>
                                </q-item-section>
                            </q-item>
                        </q-list>
                    </q-btn-dropdown>
                </q-td>
            </q-tr>
        </template>
    </q-table>

</template>

<script>
import { ref } from 'vue';

export default {
    name: 'CollaboratorsTable',
    props: {
        collaborators: Array,
    },

    emits: [
        'releaseCollaboration',
        'issueCollaboration',
        'reopenCollaboration',
        'archiveCollaborator',
        'unarchiveCollaborator',
        'removeCollaborator'
    ],

    setup() {
        const collaboratorsColumn = ref([
            {
                name: 'collaborator',
                field: 'collaborator',
                required: true,
                label: 'Collaborator',
                align: 'left',
                sortable: true
            }, {
                name: 'date_requested',
                field: 'date_released',
                required: true,
                label: 'Date requested',
                align: 'left',
                sortable: true
            }, {
                name: 'date_released',
                field: 'date_released',
                required: true,
                label: 'Date released',
                align: 'left',
                sortable: true
            }, {
                name: 'date_issued',
                field: 'date_issued',
                required: true,
                label: 'Date issued',
                align: 'left',
                sortable: true
            }, {
                name: 'date_confirmed',
                field: 'date_confirmed',
                required: true,
                label: 'Date confirmed',
                align: 'left',
                sortable: true
            }, {
                name: 'manage',
                field: 'manage',
                required: true,
                label: 'Manage',
                align: 'right',
                sortable: true
            }
        ]);
        return {
            collaboratorsColumn,
        };
    },

    data() {
        return {
            
        }
    },

    mounted() {
        
    },

    methods: {
        releaseCollaboration(collaborator) {
            this.$q.dialog({
                title: 'Initialize collaboration',
                message: 'Release collaboartion! Collaborators are now active members within collaboration.',
                cancel: true,
            }).onOk(() => {
                this.$emit('releaseCollaboration', collaborator)
            })
        },

        issueCollaboration(collaborator) {
            this.$q.dialog({
                title: 'Close collaboration',
                message: 'End collaboration! The collaboration is rewarded to participant. Collaborators access will be removed.',
                cancel: true,
            }).onOk(() => {
                this.$emit('issueCollaboration', collaborator)
            })
        },

        reopenCollaboration(collaborator) {
            this.$q.dialog({
                title: 'Reopen collaboration',
                message: 'Reopen participation! Collaborator will be active member within collaboration again.',
                cancel: true,
            }).onOk(() => {
                this.$emit('reopenCollaboration', collaborator)
            })
        },

        confirmDelete(collaborator) {
            if(collaborator.date_confirmed) return;
            this.$q.dialog({
                title: 'Confirm remove of collaborator',
                message: 'Please confirm end of collaboration. The collaboration will be removed from participating collaborator.',
                cancel: true,
            }).onOk(() => {
                this.$emit('removeCollaborator', collaborator)
            })
        }
    },
};
</script>
