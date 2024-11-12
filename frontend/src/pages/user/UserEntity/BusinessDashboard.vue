<template>

    <PageWrapper leftDrawer drawerTitle="Business Cockpit" :rendering="loading" showExpansion>
        
        <template #leftDrawer>
            <NavUserEntity />
        </template>

        <!-- Search -->
        <template #expansion-item>
            <div class="row q-pa-sm w-100">
                <!-- Navigate by collaboration-awards -->
                <q-select 
                    class="col-grow q-mx-sm" 
                    v-model="selectedAward"  
                    :loading="loading"
                    :disable="loading"
                    :options="awards" 
                    label="Collaboration Panel:"
                    dense clearable
                    @update:model-value="(selectedAward) => loadActiveCollaborations(selectedAward)" 
                    @clear="resetStats()"
                />

                <!-- Start new Collaboration -->
                <q-btn 
                    icon="groups_3" 
                    outline 
                    color="primary" 
                    @click="$router.push('/my-entity/start-collaboration')"
                >
                    <q-tooltip>Provide new collaboration!</q-tooltip>
                </q-btn>

                <!-- Redirect Profile-->
                <q-btn 
                    class="q-ml-sm"
                    outline      
                    color="primary" 
                    icon="open_in_new"
                    label="My site" 
                    @click="$router.push('/community/entities/' + $user.entity.id)" 
                />
            </div>
        </template>

        <div class="w-content-md" v-if="selectedAward?.id && awardCollection.type.id">

            <!-- Switch between Active & Archived Collborations -->
            <CardSimple :title="awardCollection.type.label">
                <template #actions>
                    <q-toggle 
                        label="Archive" dense 
                        v-model="showCollaborationArchive"
                        @update:model-value="!showCollaborationArchive 
                            ? loadActiveCollaborations(selectedAward)
                            : loadArchivedCollaborations(selectedAward)"
                    />
                </template>

                <q-card-section>
                    <span class="text-caption">{{ awardCollection.type.description }}</span>
                </q-card-section>
            </CardSimple>

            <!-- Collaborations -->
            <NoData v-if="awardCollection.collaborations.length === 0" text="No collaborations exist."/>
            <CardSimple v-else v-for="(collaboration, index) in awardCollection.collaborations" :key="index">
                <CollaborationList
                    :title="collaboration.title"
                    :icon="awardCollection.type.icon"
                    :caption="awardCollection.type.label"
                    :about="collaboration.about"
                    :contact="collaboration.contact"
                    @showPreview="collaborationPreview(collaboration)"
                >
                    <template #shortcuts>
                        <CollaborationPublicity 
                            :collaboration="collaboration"
                            @updatePublicAccess="(status) => updateAccessibilty(collaboration, status)"
                            @updatePublicTags="(tags) => updateTags(collaboration, tags)"
                        />
                        <CollaborationLimits
                            :collaboration="collaboration"
                            @updatePublicLimits="(limit) => updateLimits(collaboration, limit)"
                        />
                    </template>
                    <template #dropdown-actions>
                        <CollaborationActions 
                            :collaborationArchive="showCollaborationArchive"
                            @showSearch="collaborationAddUser(collaboration)"
                            @archiveCollaboration="archiveCollaboration(collaboration)"
                            @unarchiveCollaboration="unarchiveCollaboration(collaboration)"
                            @deleteCollaboration="confirmDelete(collaboration)"
                        />
                    </template>
                </CollaborationList>

                <!-- Collaborators -->
                <q-separator />
                <q-expansion-item 
                    header-class="text-overline text-primary" 
                    :header-inset-level="1" 
                    :label="'Active collaborators: ' + collaboration.collaborators.length" 
                    switch-toggle-side expand-separator
                >
                    <CollaboratorsTable
                        :collaborators="collaboration.collaborators"
                        @releaseCollaboration="(collaborator) => releaseCollaborationToCollaborator(collaborator)"
                        @issueCollaboration="(collaborator) => issueCollaborationToCollaborator(collaborator, collaboration)"
                        @reopenCollaboration="(collaborator) => reopenCollaboration(collaborator, collaboration)"
                        @removeCollaborator="(collaborator) => removeCollaborator(collaborator, collaboration)"
                    />
                </q-expansion-item>

                <!-- Closed -->
                <q-separator />
                <q-expansion-item 
                    header-class="text-overline text-primary" 
                    :header-inset-level="1" 
                    :label="'Closed collaborators: ' + collaboration.closed_collaborators.length" 
                    switch-toggle-side expand-separator
                >
                    <CollaboratorsTable
                        :collaborators="collaboration.closed_collaborators"
                        @reopenCollaboration="(collaborator) => reopenCollaboration(collaborator, collaboration)"
                        @removeCollaborator="(collaborator) => removeCollaborator(collaborator, collaboration)"
                    />
                </q-expansion-item>
            </CardSimple>
        </div>
        
        <!-- List of Awards -->
        <AwardList
            v-else
            v-for="(award, index) in awards" 
            :key="index" 
            :award="award"
            @loadCollaborations="loadActiveCollaborations(award)"
        />
        
        <!-- Add Collaborators -->
        <DialogWrapper 
            v-if="selectedCollaboration"
            title="Search collaborator" 
            :openDialog="showAddUsers" 
            @close="showAddUsers = false"
        >
            <CollaboratorSearch 
                :collaboration="selectedCollaboration"
                @releaseCollaborator="(user_id) => releaseCollaborationToNewCollaborator(user_id, selectedCollaboration)" 
            />
        </DialogWrapper>

        <!-- Collaboration Preview -->
        <DialogWrapper 
            v-if="selectedCollaboration"
            class="w-card-lg"
            title="Collaboration" 
            :openDialog="showCollaborationPreview" 
            @close="showCollaborationPreview = false"
        >
            <q-card-section>
                <CollaborationPreview :collaboration="selectedCollaboration"/>
            </q-card-section>
        </DialogWrapper>
    </PageWrapper>

</template>

<script>
import { ref } from 'vue'
import NavUserEntity from 'src/components/navigation/NavUserEntity.vue';
import CollaborationPreview from 'components/CollaborationPreview.vue';
import CollaborationList from 'components/CollaborationList.vue';
import CollaborationActions from './components/CollaborationActions.vue';
import CollaborationPublicity from './components/CollaborationPublicity.vue';
import CollaborationLimits from './components/CollaborationLimits.vue';
import AwardList from './components/AwardList.vue';
import CollaboratorsTable from './components/CollaboratorsTable.vue';
import CollaboratorSearch from './components/CollaboratorSearch.vue';

export default {
    name: 'BusinessCollaborations',
    components: {
        NavUserEntity, CollaborationPublicity, CollaborationActions, CollaborationLimits,
        AwardList, CollaborationPreview, CollaborationList, CollaboratorsTable, CollaboratorSearch
    },

    setup() {
        const loading = ref(true);
        const selectedAward = ref(null);
        const selectedCollaboration = ref({})
        const showAddUsers = ref(false);
        const showCollaborationPreview = ref(false);
        const showCollaborationArchive = ref(false);

        const collaborationPreview = (collaboration) => {
            showCollaborationPreview.value = true;
            selectedCollaboration.value = collaboration;
        }

        const collaborationAddUser = (collaboration) => {
            showAddUsers.value = true;
            selectedCollaboration.value = collaboration;
        }

        return {
            loading,
            selectedAward,
            selectedCollaboration,
            showCollaborationPreview,
            showAddUsers,
            showCollaborationArchive,
            collaborationPreview,
            collaborationAddUser
        };
    },

    data() {
        return {
            entity: null,
            awards: [],
            awardCollection: {
                type: {},
                collaborations: []
            },
        }
    },

    mounted() {
        this.loadAttributes();
    },

    methods: {

        resetStats() {
            this.showAddUsers = false;
            this.showCollaborationPreview = false;
            this.awardCollection = {
                type: {},
                collaborations: []
            };
        },

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

        async loadActiveCollaborations(award) {
            try {
                if(!award?.id) return;
                this.$toast.load();
                const response = await this.$axios.get('/get-active-reward-collaborations', { params: { 
                    award_id: award.id 
                }});

                this.resetStats();
                this.selectedAward = award;
                this.showCollaborationArchive = false
                this.awardCollection = response.data.award;
                this.entity = response.data.entity;
            } catch (error) {
                this.$toast.error(error.response ? error.response : error)
            } finally {
                this.$toast.done();
            }
        },

        async loadArchivedCollaborations(award) {
            try {
                if(!award?.id) return;
                this.$toast.load();
                const response = await this.$axios.get('/get-closed-reward-collaborations', { params: { 
                    award_id: award.id 
                }});
                
                this.resetStats();
                this.showCollaborationArchive = true;
                this.awardCollection = response.data.award;
                this.entity = response.data.entity
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

        async updateTags(collaboration, tags) {
            try {
                this.$toast.load();
                const response = await this.$axios.post('/update-entity-collaboration-tags', {
                    collaboration_id: collaboration.id,
                    tags: tags,
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
                this.awardCollection.collaborations.forEach((collab, index) => {
                    if(collab.id === collaboration.id)
                        this.awardCollection.collaborations.splice(index, 1);
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
                this.awardCollection.collaborations.forEach((collab, index) => {
                    if(collab.id === collaboration.id)
                        this.awardCollection.collaborations.splice(index, 1);
                })
            } catch (error) {
                this.$toast.error(error.response ? error.response : error)
            }
        },

        async removeCollaboration(collaboration) {
            try {
                console.log('wermisdaf')
                this.$toast.load();
                const response = await this.$axios.post('/delete-entity-collaboration', {
                    collaboration_id: collaboration.id,
                });

                this.$toast.success(response.data.message)
                this.awardCollection.collaborations.forEach((collab, index) => {
                    if(collab.id === collaboration.id)
                        this.awardCollection.collaborations.splice(index, 1);
                })
            } catch (error) {
                this.$toast.error(error.response ? error.response : error)
            }
        },

        confirmDelete(collaboration) {
            this.$q.dialog({
                title: 'Confirm delete',
                message: 'The current collaboration will be removed from your entity! Please make sure, ongoing collaborations are rewarded or removed.',
                cancel: true,
            }).onOk(() => {
                this.removeCollaboration(collaboration)
            })
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

        async issueCollaborationToCollaborator(collaborator)
        {
            try {
                this.$toast.load();
                const response = await this.$axios.post('/issue-collaboration-to-collaborator', {
                    'collaborator_id': collaborator.id,
                });

                this.$toast.success(response.data.message);
                collaborator.date_issued = response.data.date_issued;
            } catch (error) {
                this.$toast.error(error.response ? error.response : error)
            }
        },

        async reopenCollaboration(collaborator) {
            try {
                this.$toast.load();
                const response = await this.$axios.post('/reopen-collaboration-collaborator', {
                    'collaborator_id': collaborator.id,
                });

                this.$toast.success(response.data.message);
                collaborator.date_issued = '';
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
