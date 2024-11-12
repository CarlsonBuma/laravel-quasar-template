<template>

    <q-btn-dropdown 
        dropdown-icon="accessibility_new" 
        size="12px" 
        color="primary"
        flat dense round
    >
        <q-list class="w-card-lg" separator>

            <!-- Request Limit -->
            <q-item class="q-mb-sm">
                <q-item-section>
                    <q-item-section>Request Limit</q-item-section>
                    <q-item-label caption>
                        Max. amount of ongoing collaborations. Set '0' for no limit.
                    </q-item-label>
                    <q-item-section>
                        <q-input 
                            v-model="collaborationData.access_limit" 
                            type="number" dense
                        >
                            <template #append>
                                <q-btn 
                                    color="orange" 
                                    icon="update" 
                                    outline flat round
                                    @click="$emit('updatePublicLimits', collaborationData.access_limit)"
                                />
                            </template>
                        </q-input>
                    </q-item-section>
                </q-item-section>
            </q-item>
        </q-list>
    </q-btn-dropdown>

</template>

<script>
import { ref } from 'vue';

export default {
    name: 'CollaborationPublicity',
    props: {
        collaboration: Object,
    },

    emits: [
        'updatePublicLimits',
    ],

    setup() {
        const collaborationData = ref({});
        return {
            collaborationData,
        };
    },

    mounted() {
        this.collaborationData = this.collaboration;
    },

    methods: {
        // COde 
    },
};
</script>
