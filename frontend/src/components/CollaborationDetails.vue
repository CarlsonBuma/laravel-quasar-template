<template>

    <NoData v-if="!collaboration" text="No information available."/>
    <div v-else class="container">
        <q-tabs v-model="tabPanel">
            <q-tab label="Details" name="details" />
            <q-tab 
                label="Impressum" 
                name="impressum" 
                v-if="collaboration.entity?.id" 
                :disable="!collaboration.entity?.id" 
            />
        </q-tabs>
        <q-separator />
        <q-tab-panels v-model="tabPanel" animated>
            <q-tab-panel name="details" >
                <CollaborationPreview :collaboration="collaboration"/>
            </q-tab-panel>
            <q-tab-panel name="impressum" class="flex justify-center">
                <NoData v-if="!collaboration.entity?.id" text="No information available."/>
                <CollaborationEntity 
                    v-else
                    allowActions 
                    :entity="collaboration.entity" 
                    @connect="addEntityShortcut(collaboration.entity.id)"
                />
            </q-tab-panel>
        </q-tab-panels>
    </div>
   
</template>

<script>
import { ref } from 'vue';
import CollaborationEntity from 'components/CollaborationEntity.vue';
import CollaborationPreview from 'components/CollaborationPreview.vue';

export default {
    name: 'CollaborationDetails',
    components: {
        CollaborationEntity, CollaborationPreview
    },
    props: {
        collaboration: Object,
    },

    setup() {
        const tabPanel = ref('details');
        return {
            tabPanel,
        }
    },

    methods: {
        async addEntityShortcut(entity_id) {
            if(!entity_id) return;
            try {
                this.$toast.load();
                const response = await this.$axios.post('/add-user-entity-shortcut', {
                    entity_id: entity_id
                });
                this.$toast.success(response.data.message)
            } catch (error) {
                const errorMessage = error.response ? error.response.data.message : error;
                console.log('UserShortcuts', errorMessage)
                this.$toast.error(errorMessage)
            }
        },
    }
};
</script>
