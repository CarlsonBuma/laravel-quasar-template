<template>

     <PageWrapper :rendering="loading">

        <NoData v-if="collaborations.length === 0" text="No match found." />
        <div v-else class="row w-content justify-center">

            <!-- Profile -->
            <div class="col-auto">
                <CollaborationEntity 
                    class="w-card-xl"
                    allowActions 
                    :entity="entity" 
                    @connect="addEntityShortcut(entity_id)"
                />
            </div>

            <!-- Collaborations -->
            <div class="col-auto">
                <CardSimple
                    class="w-card-xl"
                    v-for="(collaboration, index) in collaborations" 
                    :key="index"
                >
                    <!-- Collaboration -->
                    <CollaborationList
                        :title="collaboration.title"
                        :caption="collaboration.award.label"
                        :icon="collaboration.award.icon"
                        :about="collaboration.about"
                        :contact="collaboration.contact"
                        noActions
                        @showPreview="setCollaboration(collaboration)"
                    >
                        <template #items-top>
                            <q-item>
                                <q-item-section avatar>
                                    <q-item-label caption>
                                        <b>Duration:</b><br>
                                        {{ collaboration.duration ?? 'Individual' }}
                                    </q-item-label>
                                </q-item-section>
                                <q-item-section></q-item-section>
                                <q-item-section side>
                                    <q-item-label class="text-right" caption>
                                        <q-btn 
                                            size="12px"
                                            outline
                                            :color="!collaboration.is_active_collaborator ? 'green' : 'orange'" 
                                            :icon="collaboration.is_active_collaborator ? 'notifications_active' : 'today'"
                                            :disable="collaboration.is_active_collaborator || !$user.access.user"
                                            @click="agreeUserCollaboration(collaboration)"
                                        >
                                            <q-tooltip v-if="!$user.access.user">Login</q-tooltip>
                                            <q-tooltip v-if="collaboration.is_active_collaborator">Ongoing collaboration</q-tooltip>
                                        </q-btn>
                                    </q-item-label>
                                </q-item-section>
                            </q-item>
                        </template>
                    </CollaborationList>
                </CardSimple>
            </div>
        </div>

        <!-- Collaboration Preview -->
        <DialogWrapper 
            class="w-card-lg"
            title="Collaboration" 
            :openDialog="showCollaboration" 
            @close="showCollaboration = false"
        >
            <q-card-section>
                <CollaborationPreview 
                    v-if="selectedCollaboration"
                    allowActions 
                    :collaboration="selectedCollaboration" 
                    :entity="entity"
                    @participate="(collaboration) => agreeUserCollaboration(collaboration)"
                />
            </q-card-section>
        </DialogWrapper>
    </PageWrapper>

</template>

<script>
import { ref } from 'vue';
import CollaborationList from 'components/CollaborationList.vue';
import CollaborationPreview from 'components/CollaborationPreview.vue';
import CollaborationEntity from 'components/CollaborationEntity.vue';

export default {
    name: 'WebCommunityEntity',
    components: {
        CollaborationList,  CollaborationPreview, CollaborationEntity
    },

    emits: [
        'authorize',
    ],

    created() {
        // Check user-authorization
        this.$watch(() => this.$user.access.user,
        (newValue) => {
            if(newValue) 
                this.loadCommunityEntity(this.entity_id)
        });
    },

    setup() {
        const googleMapsURL = 'https://www.google.com/maps/search/?api=1&query=';
        const loading = ref(true);
        
        const selectedCollaboration = ref(null);
        const showCollaboration = ref(false);

        const setCollaboration = (collaboration) => {
            selectedCollaboration.value = collaboration;
            showCollaboration.value = true;
        }

        return {
            googleMapsURL,
            loading,
            showCollaboration,
            selectedCollaboration,
            setCollaboration
        }
    },

    data() {
        return {
            entity_id: this.$route.params.entity_id
                ? this.$route.params.entity_id
                : null,
            entity: null,
            collaborations: []
        }
    },

    mounted() {
        this.loadCommunityEntity(this.entity_id)
    },

    methods: {
        async loadCommunityEntity(entity_id) {
            try {
                if(!entity_id) return;
                this.loading = true;
                const response = await this.$axios.get('/get-community-entity', { params: { 
                    entity_id: entity_id
                }});

                this.entity = response.data.entity;
                this.collaborations = response.data.collaborations;
            } catch (error) {
                console.log('CommunityEntity', error.response ? error.response.data.message : error)
            } finally {
                this.loading = false;
            }
        },

        async agreeToCollaboration(collaboration) {
            try {
                this.$toast.load();
                const response = await this.$axios.post('/user-request-collaboration', {
                    collaboration_id: collaboration.id
                });
                this.$toast.success(response.data.message)
                collaboration.is_active_collaborator = true;
            } catch (error) {
                const errorMessage = error.response ? error.response.data.message : error;
                console.log('UserCollaboration', errorMessage)
                this.$toast.error(errorMessage)
            }
        },

        agreeUserCollaboration(collaboration) {
            this.$q.dialog({
                title: 'Request participation',
                message: 'Hereby, you confirm beeing an active member within the collaboration! The collaboration will be rewarded to your avatar after confirmation by publisher.',
                cancel: true,
            }).onOk(() => {
                this.agreeToCollaboration(collaboration)
            })
        },

        async addEntityShortcut(entity_id) {
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
