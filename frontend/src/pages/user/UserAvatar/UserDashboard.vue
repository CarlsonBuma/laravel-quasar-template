<template>

    <PageWrapper 
        title="Dashboard" 
        :rendering="loading" 
        leftDrawer
        drawerTitle="My avatar"
    >
        <template #leftDrawer>
            <NavUserAvatar/>
        </template>

        <div class="w-card">
            <CardSimple title="Overview">
                <q-list bordered>
                    <q-item  v-if="collaborationRequests.length > 0" clickable v-ripple>
                        <q-item-section>Waiting on participation</q-item-section>
                        <q-item-section avatar>{{ collaborationRequests.length }}</q-item-section>
                    </q-item>
                    <q-item v-if="collaborationConfirmations.length > 0" clickable v-ripple>
                        <q-item-section>Waiting on confirmation</q-item-section>
                        <q-item-section avatar>{{ collaborationConfirmations.length }}</q-item-section>
                    </q-item>
                    <q-item clickable v-ripple>
                        <q-item-section>Ongoing collaborations</q-item-section>
                        <q-item-section avatar>{{ collaborationsOngoing.length }}</q-item-section>
                    </q-item>
                    <q-item clickable v-ripple>
                        <q-item-section>Closed collaborations</q-item-section>
                        <q-item-section avatar>{{ totalClosedCollaborations }}</q-item-section>
                    </q-item>
                </q-list>
            </CardSimple>
        </div>

        <div class="w-card-xl">
            <CardSimple 
                v-if="collaborationRequests.length > 0"
                title="Waiting on participation..."
                tooltip="By agreeing participation, you are an active member whithin the collaboration rewarded."
                tooltipIconColor="orange"
            >
                <!-- Collaboration -->
                <CollaborationList 
                    v-for="(collaboration, index) in collaborationRequests" 
                    :key="index"
                    :datetime="collaboration.date_time_start"
                    :duration="collaboration.duration"
                    :title="collaboration.title"
                    :caption="collaboration.award.label"
                    :about="collaboration.about"
                    :contact="collaboration.contact"
                    @showPreview="setCollaboration(collaboration)"
                    @remove="removeCollaboration(collaboration.collaborator, collaboration)"
                >
                    <template #icon>
                        <q-img
                            v-if="collaboration.entity?.avatar"
                            :src="collaboration.entity.avatar"
                            spinner-color="white"
                            width="64px"
                            alt="logo"
                            loading="eager"
                            @click="$router.push('/community/entities/' + collaboration.entity.id)"
                        />
                        <q-icon 
                            v-else
                            :name="collaboration.award?.icon ? collaboration.award.icon : 'diversity_1'" 
                            color="primary" 
                            size="64px" 
                        />
                    </template>
                    <template #actions>
                        <q-item clickable v-close-popup>
                            <q-item-section avatar>
                                <q-icon color="green" name="diversity_1" />
                            </q-item-section>
                            <q-item-section>
                                <q-item-label class="text-caption" @click="agreeRequestedCollaboration(collaboration.collaborator, collaboration)">Agree collaboration</q-item-label>
                            </q-item-section>
                        </q-item>
                    </template>
                </CollaborationList>
            </CardSimple>

            <CardSimple 
                v-if="collaborationConfirmations.length > 0"
                title="Waiting on confirmation..."
                tooltip="By confirming end of collaboration, the reward will be assigned to your avatar."
                tooltipIconColor="orange"
            >
                <!-- Collaboration -->
                <CollaborationList 
                    v-for="(collaboration, index) in collaborationConfirmations" 
                    :key="index"
                    :datetime="collaboration.collaborator.period_start"
                    :duration="collaboration.collaborator.duration"
                    :title="collaboration.title"
                    :caption="collaboration.award.label"
                    :about="collaboration.about"
                    :contact="collaboration.contact"
                    @showPreview="setCollaboration(collaboration)"
                    @remove="removeCollaboration(collaboration.collaborator, collaboration)"
                >
                    <template #icon>
                        <q-img
                            v-if="collaboration.entity?.avatar"
                            :src="collaboration.entity.avatar"
                            spinner-color="white"
                            width="64px"
                            alt="logo"
                            loading="eager"
                            @click="$router.push('/community/entities/' + collaboration.entity.id)"
                        />
                        <q-icon 
                            v-else
                            :name="collaboration.award?.icon ? collaboration.award.icon : 'diversity_1'" 
                            color="primary" 
                            size="64px" 
                        />
                    </template>
                    <template #actions>
                        <q-item clickable v-close-popup>
                            <q-item-section avatar>
                                <q-icon color="green" name="handshake" />
                            </q-item-section>
                            <q-item-section>
                                <q-item-label class="text-caption" @click="confirmCollaboration(collaboration.collaborator, collaboration)">Confirm collaboration</q-item-label>
                            </q-item-section>
                        </q-item>
                    </template>
                </CollaborationList>
            </CardSimple>
        
            <CardSimple title="Ongoing collaborations">
                <NoData v-if="collaborationsOngoing.length === 0" text="No ongoing collaborations."/>
                <!-- Collaboration -->
                <CollaborationList 
                    v-else separator v-for="(collaboration, index) in collaborationsOngoing" 
                    :key="index"
                    :datetime="collaboration.collaborator.period_start"
                    :duration="collaboration.collaborator.duration"
                    :title="collaboration.title"
                    :caption="collaboration.award.label"
                    :about="collaboration.about"
                    :contact="collaboration.contact"
                    @showPreview="setCollaboration(collaboration)"
                    @remove="removeCollaboration(collaboration.collaborator, collaboration)"
                >
                    <template #icon>
                        <q-img
                            v-if="collaboration.entity?.avatar"
                            :src="collaboration.entity.avatar"
                            spinner-color="white"
                            width="64px"
                            alt="logo"
                            loading="eager"
                            @click="$router.push('/community/entities/' + collaboration.entity.id)"
                        />
                        <q-icon 
                            v-else
                            :name="collaboration.award?.icon ? collaboration.award.icon : 'diversity_1'" 
                            color="primary" 
                            size="64px" 
                        />
                    </template>
                </CollaborationList>
            </CardSimple>
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

        <!-- Confirm Collaboration -->
        <DialogWrapper 
            class="w-card-lg"
            title="Confirm collaboration" 
            :openDialog="showConfirmation" 
            @close="showConfirmation = false"
        >
            <q-card-section>
                <div class="row w-100">
                    <div class="col-12">
                        <q-input  label="Start" type="date" v-model="confirmationMeta.period_start"/>
                    </div>
                    <div class="col-12">
                        <q-input label="Duration" hint="eg. 3 months, 2 hours, individual etc." v-model="confirmationMeta.period_duration" />
                    </div>
                </div>
            </q-card-section>
            <q-separator />
            <q-card-section>
                <span class="text-overline">Review collaboration:</span>
            </q-card-section>
            <q-separator />
            <q-card-section class="text-right">
                <q-btn 
                    icon="handshake" 
                    outline 
                    color="green" 
                    label="Confirm end of collaboration" 
                    @click="confirmByCollaborator(selectedCollaborator.id, confirmationMeta)" 
                />
            </q-card-section>
        </DialogWrapper>
    </PageWrapper>

</template>

<script>
import { ref } from 'vue';
import NavUserAvatar from 'src/components/navigation/NavUserAvatar.vue';
import CollaborationDetails from 'components/CollaborationDetails.vue';
import CollaborationList from 'components/CollaborationList.vue';

export default {
    name: 'UserDashboard',
    components: {
        NavUserAvatar, CollaborationDetails, CollaborationList
    },

    setup() {
        const loading = ref(true);
        const tabPanel = ref('reward')

        const showCollaboration = ref(false);
        const selectedCollaboration = ref({});

        const setCollaboration = (collaboration) => {
            selectedCollaboration.value = collaboration;
            showCollaboration.value = true;
        }

        const showConfirmation = ref(false);
        const selectedCollaborator = ref({});
        const confirmationMeta = ref({});

        const confirmCollaboration = (collaborator, collaboration) => {
            selectedCollaboration.value = collaboration;
            confirmationMeta.value.period_start = collaborator.period_start ?? collaborator.date_released;
            confirmationMeta.value.period_duration = '';
            selectedCollaborator.value = collaborator;
            showConfirmation.value = true
        }

        return {
            loading,
            tabPanel,
            showCollaboration,
            selectedCollaboration,
            setCollaboration,
            showConfirmation,
            selectedCollaborator,
            confirmationMeta,
            confirmCollaboration,
        };
    },

    data() {
        return {
            totalClosedCollaborations: 0,
            collaborationRequests: [],
            collaborationConfirmations: [],
            collaborationsOngoing: [],
        }
    },

    mounted() {
        this.loadCollaborations()
    },

    methods: {
        async loadCollaborations() {
            try {
                this.loading = true;
                const response = await this.$axios.get('/load-user-dashboard');
                this.totalClosedCollaborations = response.data.total_closed_collaborations;
                this.collaborationRequests = response.data.collaboration_requests;
                this.collaborationConfirmations = response.data.collaboration_confirmations;
                this.collaborationsOngoing = response.data.collaborations_ongoing;
            } catch (error) {
                this.$toast.error(error.response ? error.response : error)
            } finally {
                this.loading = false;
            }
        },

        async agreeRequestByCollaborator(collaborator) {
            try {
                this.$toast.load();
                const response = await this.$axios.post('/user-agree-collaboration-request', {
                    collaborator_id: collaborator.id,
                });
                this.$toast.success(response.data.message)
                this.collaborationRequests.forEach((collab, index) => {
                    if(collab.collaborator.id === collaborator.id) {
                        this.collaborationsOngoing.push(collab)
                        this.collaborationRequests.splice(index, 1)
                    }
                })
            } catch (error) {
                this.$toast.error(error.response ? error.response : error)
            }
        },

        agreeRequestedCollaboration(collaborator) {
            this.$q.dialog({
                title: 'New request!',
                message: 'By accepting, you agree beeing an active participant whithin the collaboration rewarded.',
                cancel: true,
            }).onOk(() => {
                this.agreeRequestByCollaborator(collaborator)
            })
        },

        async confirmByCollaborator(collaboratorID, confirmationMeta) {
            try {
                if(!confirmationMeta.period_duration || !confirmationMeta.period_start) throw 'Please enter period details.'
                this.$toast.load();
                const response = await this.$axios.post('/user-confirm-collaboration', {
                    'collaborator_id': collaboratorID,
                    'period_start': confirmationMeta.period_start,
                    'period_duration': confirmationMeta.period_duration,
                });

                this.$toast.success(response.data.message)
                this.collaborationConfirmations.forEach((collab, index) => {
                    if(collab.collaborator.id === collaboratorID) {
                        this.collaborationConfirmations.splice(index, 1)
                    }
                })

                this.confirmationMeta = {};
                this.showConfirmation = false;
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
                this.collaborationRequests.forEach((collab, index) => {
                    if(collab.collaborator.id === collaborator.id) {
                        this.collaborationRequests.splice(index, 1)
                    }
                })

                this.collaborationConfirmations.forEach((collab, index) => {
                    if(collab.collaborator.id === collaborator.id) {
                        this.collaborationConfirmations.splice(index, 1)
                    }
                })

                this.collaborationsOngoing.forEach((collab, index) => {
                    if(collab.collaborator.id === collaborator.id) {
                        this.collaborationsOngoing.splice(index, 1)
                    }
                })
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
