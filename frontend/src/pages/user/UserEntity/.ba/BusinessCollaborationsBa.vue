<template>

    <PageWrapper 
        leftDrawer
        title="Collaborations: Controll Panel" 
        drawerTitle="Business Cockpit" 
        :rendering="loading" 
    >
        <template #leftDrawer>
            <NavUserEntity />
        </template>

        <template #actions>
            <q-select 
                class="w-select q-mx-sm" 
                v-model="selectedAward"  
                :loading="loading"
                :disable="loading"
                :options="awards" 
                label="Reward type"
                dense clearable
                @update:model-value="(award) => loadAwardCollaborations(award)" 
                @clear="resetStats(); selectedAward = null"
            />

            <q-btn icon="groups_3" outline color="primary" @click="$router.push('/my-entity/start-collaboration')"/>
        </template>

        <div class="w-content-md" v-if="selectedAward?.id && award.type.id">
            <CardSimple class="q-mb-md" shadow :title="award.type.label" :tooltip="award.type.description" tooltipIconColor="primary" >
                <template #actions>
                    <q-toggle 
                        label="Archive" dense 
                        v-model="showAwardArchive"
                        @update:model-value="!showAwardArchive 
                            ? loadAwardCollaborations(selectedAward)
                            : loadArchivedAwardCollaborations(selectedAward)"
                    />
                </template>
            </CardSimple>

            <!-- Collaborations -->
            <NoData v-if="award.collaborations.length === 0" text="No collaborations exist."/>
            <CardSimple v-else v-for="(collaboration, index) in award.collaborations" :key="index">
                <CollaborationList 
                    
                    :duration="collaboration.duration"
                    showContact
                    :title="collaboration.title"
                    :icon="award.type.icon"
                    :caption="award.type.label"
                    :about="collaboration.about"
                    :contact="collaboration.contact"
                    @showPreview="showAwardPreview(collaboration)"
                    @remove="deleteCollaboration(collaboration)"
                >
                    <template #shortcuts>
                        <CollaborationPublicity 
                            :collaboration="collaboration"
                            @updatePublicAccess="(collaboration, status) => updateAccessibilty(collaboration, status)"
                            @updatePublicLimits="(collaboration, limit) => updateLimits(collaboration, limit)"
                        />
                    </template>
                    <template #dropdown-actions>
                        <CollaborationActions 
                            :awardArchive="showAwardArchive"
                            :collaborationArchive="showCollaboratorsArchive[index] ?? false"
                            @archiveCollaboration="archiveCollaboration(collaboration)"
                            @unarchiveCollaboration="unarchiveCollaboration(collaboration)"
                            @showSearch="showCollaboratorSearch(collaboration)"
                            @showArchive="(status) => { showCollaboratorsArchive[index] = status }"
                        />
                        <q-separator />
                    </template>
                </CollaborationList>

                <!-- Collaborators -->
                <q-separator />
                <q-expansion-item 
                    header-class="text-weight-bold text-primary text-uppercase" 
                    :header-inset-level="1" 
                    :label="!showCollaboratorsArchive[index] 
                        ? 'Active collaborators: ' + collaboration.collaborators.length
                        : 'Closed collaborators: ' + collaboration.closed_collaborators.length" 
                    switch-toggle-side
                    expand-separator
                >
                    <CollaboratorsTable
                        :archived="showCollaboratorsArchive[index]" 
                        :collaborators="showCollaboratorsArchive[index] 
                            ? collaboration.closed_collaborators 
                            : collaboration.collaborators"
                        @releaseCollaboration="(collaborator) => releaseCollaborationToCollaborator(collaborator)"
                        @issueCollaboration="(collaborator) => confirmCollaboration(collaborator, collaboration)"
                        @removeCollaborator="(collaborator) => removeCollaborator(collaborator, collaboration)"
                    />
                </q-expansion-item>
                <q-separator />
            </CardSimple>
        </div>
        
        <!-- List of Awards -->
        <AwardList
            v-else
            v-for="(award, index) in awards" 
            :key="index" 
            :award="award"
            @loadCollaborations="loadAwardCollaborations(award)"
        />
        
        <!-- Add Collaborators -->
        <DialogWrapper 
            title="Connect Collaborators" 
            :openDialog="showAddUsers" 
            @close="showAddUsers = false"
        >
            <CollaboratorSearch 
                :users="userSearchResult"
                @searchUser="(value) => searchUser(value)"
                @releaseCollaborator="(user_id) => releaseCollaborationToNewCollaborator(user_id, selectedCollaboration)"
            />
        </DialogWrapper>

        <!-- Collaboration Preview -->
        <DialogWrapper 
            class="w-card-lg"
            title="Collaboration Preview" 
            :openDialog="showCollaboration" 
            @close="showCollaboration = false"
        >
            <q-card-section>
                <CollaborationPreview v-if="selectedCollaboration" :collaboration="selectedCollaboration" :entity="entity"/>
            </q-card-section>
        </DialogWrapper>

        <!-- Issue Collaboration -->
        <DialogWrapper 
            class="w-card-lg"
            title="Confirm collaboration" 
            :openDialog="showConfirmation" 
            @close="showConfirmation = false"
        >
            <q-card-section>
                <div class="row w-100">
                    <div class="col-12 col-md-6 q-px-xs">
                        <q-input  label="Start" type="date" v-model="confirmationMeta.period_start"/>
                    </div>
                    <div class="col-12 col-md-6 q-px-xs">
                        <q-input label="Duration" hint="eg. 3 months, 2 hours, individual etc." v-model="confirmationMeta.period_duration" />
                    </div>
                </div>
            </q-card-section>
            <q-separator />
            <q-card-section class="text-right">
                <q-btn 
                    icon="handshake" 
                    outline 
                    color="green" 
                    label="Confirm end of collaboration" 
                    @click="issueCollaborationToCollaborator(selectedCollaborator, confirmationMeta)" 
                />
            </q-card-section>
        </DialogWrapper>
    </PageWrapper>

</template>

<script>
import { ref } from 'vue'
import NavUserEntity from 'src/components/navigation/NavUserEntity.vue';
import CollaborationPreview from 'components/CollaborationPreview.vue';
import CollaborationActions from './components/CollaborationActions.vue';
import CollaborationPublicity from './components/CollaborationPublicity.vue';
import AwardList from './components/AwardList.vue';
import CollaborationList from 'components/CollaborationList.vue';
import CollaboratorsTable from './components/CollaboratorsTable.vue';
import CollaboratorSearch from './components/CollaboratorSearch.vue';

export default {
    name: 'BusinessCollaborations',
    components: {
        NavUserEntity, CollaborationPublicity, CollaborationActions, 
        AwardList, CollaborationPreview,  CollaborationList, CollaboratorsTable, CollaboratorSearch
    },

    setup() {
        const loading = ref(true);
        const showCollaboration = ref(false);
        const showAwardArchive = ref(false)
        const showAddUsers = ref(false);
        const showCollaboratorsArchive = ref([]);
        const selectedAward = ref(null);
        const selectedCollaboration = ref({});

        const showConfirmation = ref(false);
        const selectedCollaborator = ref({});
        const confirmationMeta = ref({});

        const confirmCollaboration = (collaborator, collaboration) => {
            confirmationMeta.value.period_start = collaborator.date_released
            confirmationMeta.value.period_duration = collaboration.duration ?? 'Individual'
            selectedCollaborator.value = collaborator
            showConfirmation.value = true
        }
        
        const showAwardPreview = (collaboration) => {
            selectedCollaboration.value = collaboration
            showCollaboration.value = true
        }

        const showCollaboratorSearch = (collaboration) => {
            selectedCollaboration.value = collaboration
            showAddUsers.value = true
        }

        const resetStats = () => {
            showAwardArchive.value = false;
            showCollaboration.value = false;
            showAddUsers.value = false;
            showCollaboratorsArchive.value = []
        }

        return {
            loading,
            showConfirmation,
            showCollaboration,
            showAddUsers,
            showAwardArchive,
            showCollaboratorsArchive,
            selectedAward,
            selectedCollaboration,
            selectedCollaborator,
            confirmationMeta,
            showAwardPreview,
            showCollaboratorSearch,
            confirmCollaboration,
            resetStats,
        };
    },

    data() {
        return {
            entity: null,
            userSearchResult: [],
            awards: [],
            award: {
                type: {},
                collaborations: []
            },
        }
    },

    mounted() {
        this.loadAttributes();
    },

    methods: {

        async loadAttributes() {
            try {
                this.loading = true;
                const response = await this.$axios.get('/load-entity-awards');
                this.awards = response.data.public_awards;
            } catch (error) {
                this.$toast.error(error.response ? error.response : error)
            } finally {
                this.loading = false;
            }
        },

        async loadAwardCollaborations(award) {
            try {
                this.resetStats();
                if(!award?.id) return;
                this.$toast.load();
                const response = await this.$axios.get('/get-active-reward-collaborations', { params: { 
                    award_id: award.id 
                }});

                this.award = response.data.award;
                this.entity = response.data.entity;
                this.selectedAward = award;
            } catch (error) {
                this.$toast.error(error.response ? error.response : error)
            } finally {
                this.$toast.done();
            }
        },

        async loadArchivedAwardCollaborations(award) {
            try {
                if(!award?.id) return;
                this.$toast.load();
                const response = await this.$axios.get('/get-closed-reward-collaborations', { params: { 
                    award_id: award.id 
                }});
                this.award = null;
                this.award = response.data.award;
                this.entity = response.data.entity
                this.selectedAward = award;
            } catch (error) {
                this.$toast.error(error.response ? error.response : error)
            } finally {
                this.$toast.done();
            }
        },

        //* Manage Collaboration 
        async updateAccessibilty(collaboration, is_public) {
            try {
                this.$toast.load();
                const response = await this.$axios.post('/make-entity-collaboration-public', {
                    collaboration_id: collaboration.id,
                    is_public: is_public,
                });
                this.$toast.success(response.data.message)
            } catch (error) {
                this.$toast.error(error.response ? error.response : error)
            }
        },

        async updateLimits(collaboration, limit) {
            try {
                this.$toast.load();
                const response = await this.$axios.post('/update-entity-collaboration-limit', {
                    collaboration_id: collaboration.id,
                    limit: limit,
                });
                this.$toast.success(response.data.message)
            } catch (error) {
                this.$toast.error(error.response ? error.response : error)
            }
        }, 

        async archiveCollaboration(collaboration) {
            try {
                
                this.$toast.load();
                const response = await this.$axios.post('/archive-entity-collaboration', {
                    collaboration_id: collaboration.id,
                });
                this.$toast.success(response.data.message)
                this.award.collaborations.forEach((collab, index) => {
                    if(collab.id === collaboration.id)
                        this.award.collaborations.splice(index, 1);
                })
            } catch (error) {
                this.$toast.error(error.response ? error.response : error)
            }
        },

        async unarchiveCollaboration(collaboration) {
            try {
                
                this.$toast.load();
                const response = await this.$axios.post('/unarchive-entity-collaboration', {
                    collaboration_id: collaboration.id,
                });
                this.$toast.success(response.data.message)
                this.award.collaborations.forEach((collab, index) => {
                    if(collab.id === collaboration.id)
                        this.award.collaborations.splice(index, 1);
                })
            } catch (error) {
                this.$toast.error(error.response ? error.response : error)
            }
        },

        async deleteCollaboration(collaboration) {
            try {
                
                this.$toast.load();
                const response = await this.$axios.post('/delete-entity-collaboration', {
                    collaboration_id: collaboration.id,
                });

                this.$toast.success(response.data.message)
                this.award.collaborations.forEach((collab, index) => {
                    if(collab.id === collaboration.id)
                        this.award.collaborations.splice(index, 1);
                })
            } catch (error) {
                this.$toast.error(error.response ? error.response : error)
            }
        },

        //* Collaborators
        async searchUser(email)
        {
            try {
                if(!email) throw 'Email field is required.'
                this.$toast.load();
                const response = await this.$axios.get('/search-new-collaborator', { params: { 
                    email: email 
                }});
                if(response.data.collaborator) this.userSearchResult.push(response.data.collaborator);
                this.$toast.success(response.data.message)
            } catch (error) {
                this.$toast.error(error.response ? error.response : error)
            }
        },

        async releaseCollaborationToNewCollaborator(userID, collaboration)
        {
            try {
                this.$toast.load();
                const response = await this.$axios.post('/release-collaboration-to-new-collaborator', {
                    collaboration_id: collaboration.id,
                    user_id: userID
                });
                this.$toast.success(response.data.message);
                collaboration.collaborators.unshift(response.data.collaborator);
            } catch (error) {
                this.$toast.error(error.response ? error.response : error)
            }
        },

        async releaseCollaborationToCollaborator(collaborator)
        {
            try {
                this.$toast.load();
                const response = await this.$axios.post('/release-collaboration-to-collaborator', {
                    collaborator_id: collaborator.id,
                });

                collaborator.date_released = response.data.date_released;
                this.$toast.success(response.data.message);
            } catch (error) {
                this.$toast.error(error.response ? error.response : error)
            }
        },

        async issueCollaborationToCollaborator(collaborator, confirmationMeta)
        {
            try {
                this.$toast.load();
                const response = await this.$axios.post('/issue-collaboration-to-collaborator', {
                    'collaborator_id': collaborator.id,
                    'period_start': confirmationMeta.period_start,
                    'period_duration': confirmationMeta.period_duration,
                });

                this.$toast.success(response.data.message);
                collaborator.date_issued = response.data.date_issued;
                this.selectedCollaborator = {};
                this.confirmationMeta = {};
                this.showConfirmation = false;
            } catch (error) {
                this.$toast.error(error.response ? error.response : error)
            }
        },

        async removeCollaborator(collaborator, collaboration) {
            try {
                this.$toast.load();
                const response = await this.$axios.post('/delete-collaboration-collaborator', {
                    collaborator_id: collaborator.id,
                });

                this.$toast.success(response.data.message);
                collaboration.collaborators.forEach((collab, index) => {
                    if(collab.id === collaborator.id) 
                        collaboration.collaborators.splice(index, 1)
                });

                collaboration.closed_collaborators.forEach((collab, index) => {
                    if(collab.id === collaborator.id) {
                        collaboration.closed_collaborators.splice(index, 1)
                    }   
                });
            } catch (error) {
                this.$toast.error(error.response ? error.response : error)
            }
        },
    },
};
</script>
