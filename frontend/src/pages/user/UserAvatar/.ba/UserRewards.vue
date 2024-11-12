<template>

    <PageWrapper 
        title="Reward collection" 
        :rendering="loading" 
        leftDrawer
        drawerTitle="My avatar"
    >
        <template #leftDrawer>
            <NavUserAvatar/>
        </template>

        <NoData v-if="collaborations.length === 0" text="No rewards aquired." />
        <div v-else class="w-card-xl">
            <CollaborationsTimeline 
                class="w-card-xl" 
                :collaborations="collaborations"
                @preview="(collaboration) => setSelectedCollaboration(collaboration)"
                @publish="(status, collaboratorID) => publishCollaboration(status, collaboratorID)"
                @delete="(collaboratorID) => removeCollaboration(collaboratorID)"
            />


            <CardSimple v-for="(reward, index) in collaborations" :key="index" >
                <q-list>
                    <q-expansion-item
                        group="somegroup"
                        switch-toggle-side
                        expand-separator
                        :icon="reward.award.icon"
                        :label="reward.award.label"
                    >
                        <!-- Collaboration Details -->
                        <CollaborationList 
                            v-for="(collaborator, indexItem) in reward.collaborations" 
                            :key="indexItem"
                            :datetime="collaborator.period_start"
                            :duration="collaborator.duration"
                            :title="collaborator.collaboration.title"
                            :caption="collaborator.collaboration.entity?.name"
                            :about="collaborator.collaboration.about"
                            :contact="collaborator.collaboration.contact"
                            allowShortcuts
                            @showPreview="setSelectedCollaboration(collaborator.collaboration)"
                            @remove="removeCollaboration(collaborator)"
                        >
                            <template #icon>
                                <q-img
                                    v-if="collaborator.collaboration.entity?.avatar"
                                    :src="collaborator.collaboration.entity.avatar"
                                    spinner-color="white"
                                    width="64px"
                                    alt="logo"
                                    loading="eager"
                                />
                                <q-icon 
                                    v-else
                                    :name="collaborator.collaboration.award?.icon ? collaborator.collaboration.award.icon : 'diversity_1'" 
                                    color="primary" 
                                    size="42px" 
                                />
                            </template>
                            <template #actions>
                                <q-item>
                                    <q-item-section avatar>
                                        <q-toggle 
                                            dense 
                                            v-model="collaborator.is_public"
                                            @update:model-value="(status) => publishCollaboration(status, collaborator)"
                                        />
                                    </q-item-section>
                                    <q-item-section>
                                        <q-item-label class="text-caption">Published</q-item-label>
                                    </q-item-section>
                                </q-item>
                            </template>
                        </CollaborationList>
                    </q-expansion-item>
                </q-list>
            </CardSimple>
        </div>

        <!-- Collaboration Preview -->
        <DialogWrapper 
            v-if="selectedCollaboration" 
            title="Collaboration details" 
            :openDialog="showCollaboration" 
            @close="showCollaboration = false"
        >
            <CollaborationDetails :collaboration="selectedCollaboration"/>
        </DialogWrapper>
    </PageWrapper>

</template>

<script>
import { ref } from 'vue';
import NavUserAvatar from 'src/components/navigation/NavUserAvatar.vue';
import CollaborationDetails from 'components/CollaborationDetails.vue';
import CollaborationList from 'components/CollaborationList.vue';

export default {
    name: 'UserRewards',
    components: {
        NavUserAvatar, CollaborationDetails, CollaborationList
    },

    setup() {
        const tabPanel = ref('reward')
        const loading = ref(true);
        const showCollaboration = ref(false);
        const selectedCollaboration = ref(null);
        const selectedAward = ref(null)

        const setSelectedCollaboration = (collaboration) => {
            selectedCollaboration.value = collaboration;
            showCollaboration.value = true;
        }
        return {
            tabPanel,
            loading,
            showCollaboration,
            selectedCollaboration,
            selectedAward,
            setSelectedCollaboration
        };
    },

    data() {
        return {
            awards: [],
            collaborations: [],
        }
    },

    mounted() {
        this.loadCollaborations()
    },

    methods: {
        async loadCollaborations() {
            try {
                this.loading = true;
                const response = await this.$axios.get('/get-user-released-collaborations');

                this.collaborations = response.data.collaborations;
            } catch (error) {
                this.$toast.error(error.response ? error.response : error)
            } finally {
                this.loading = false;
            }
        },

        async publishCollaboration(status, collaborator) {
            try {
                this.$toast.load();
                const response = await this.$axios.post('/user-publish-collaboration', {
                    collaborator_id: collaborator.id,
                    is_public: status,
                });
                this.$toast.success(response.data.message)
            } catch (error) {
                this.$toast.error(error.response ? error.response : error)
            }
        },

        async removeCollaboration(collaborator) {
            try {
                this.$toast.load();
                const response = await this.$axios.post('/user-remove-collaboration', {
                    collaborator_id: collaborator.id,
                });
                this.$toast.success(response.data.message)
                this.collaborations.forEach((collab, index) => {
                    if(collab.collaborator.id === collaborator.id) {
                        this.collaborations.splice(index, 1)
                    }
                });
            } catch (error) {
                this.$toast.error(error.response ? error.response : error)
            }
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
    },
};
</script>
