<template>

    <PageWrapper 
        title="My rewards" 
        :rendering="loading" 
        leftDrawer
        drawerTitle="My avatar"
    >
        <template #leftDrawer>
            <NavUserAvatar/>
        </template>

        <template #actions>
            <q-btn 
                outline      
                color="primary" 
                icon="diversity_1"
                label="Community avatar"
                @click="$router.push('/community/collaborators/' + $user.user.id)" 
            />
        </template>

        <NoData v-if="collaborations.length === 0" text="No rewards aquired." />
        <div v-else class="w-card-xl">
            <CollaborationsTimeline 
                class="w-card-xl" 
                allowActions
                :collaborations="collaborations"
                @preview="(collaboration) => setSelectedCollaboration(collaboration)"
                @update-start="(date, collaboratorID) => updateCollaboratorStart(date, collaboratorID)"
                @update-duration="(duration, collaboratorID) => updateCollaboratorDuration(duration, collaboratorID)"
                @publish="(status, collaboratorID) => publishCollaboration(status, collaboratorID)"
                @delete="(collaboratorID) => removeCollaboration(collaboratorID)"
            >
                <span class="text-overline">Review:</span>
                <q-list>

                    <q-item>
                        <q-item-section>
                            <q-item-label>Collaboration</q-item-label>
                            <q-item-label caption lines="3">
                                The reward is accurate! The quality of outcome was satisfying and met my expectations.
                            </q-item-label>
                        </q-item-section>
                        <q-item-section side top>
                            <q-rating
                                v-model="ratingModel"
                                size="2em"
                                :max="3"
                                color="primary"
                            />
                        </q-item-section>
                    </q-item>

                    <q-item>
                        <q-editor 
                            class="w-100"
                            v-model="review" 
                            min-height="10rem"
                            ref="editorRef"
                            @paste="onPaste"
                            :toolbar="[
                                ['bold', 'italic', 'underline'],
                                ['unordered', 'outdent', 'indent'],
                            ]" 
                        />
                    </q-item>
                </q-list>
            </CollaborationsTimeline>
        </div>

        <!-- Collaboration Preview -->
        <DialogWrapper 
            v-if="selectedCollaboration" 
            title="Collaboration" 
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
import CollaborationsTimeline from 'components/CollaborationsTimeline.vue';
import CollaborationDetails from 'components/CollaborationDetails.vue';

export default {
    name: 'UserRewards',
    components: {
        NavUserAvatar, CollaborationsTimeline, CollaborationDetails
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

        async publishCollaboration(status, collaboratorID) {
            try {
                this.$toast.load();
                const response = await this.$axios.post('/user-publish-collaboration', {
                    collaborator_id: collaboratorID,
                    is_public: status,
                });
                this.$toast.success(response.data.message)
            } catch (error) {
                this.$toast.error(error.response ? error.response : error)
            }
        },

        async updateCollaboratorStart(start, collaboratorID) {
            try {
                this.$toast.load();
                const response = await this.$axios.post('/user-update-collaboration-start', {
                    collaborator_id: collaboratorID,
                    start: start,
                });
                this.$toast.success(response.data.message)
            } catch (error) {
                this.$toast.error(error.response ? error.response : error)
            }
        },

        async updateCollaboratorDuration(duration, collaboratorID) {
            try {
                this.$toast.load();
                const response = await this.$axios.post('/user-update-collaboration-duration', {
                    collaborator_id: collaboratorID,
                    duration: duration,
                });
                this.$toast.success(response.data.message)
            } catch (error) {
                this.$toast.error(error.response ? error.response : error)
            }
        },

        async removeCollaboration(collaboratorID) {
            try {
                this.$toast.load();
                const response = await this.$axios.post('/user-remove-collaboration', {
                    collaborator_id: collaboratorID,
                });

                this.$toast.success(response.data.message)
                this.selectedCollaboration = null;
                this.collaborations.forEach((collab, index) => {
                    if(collab.collaborator?.id === collaboratorID) {
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
