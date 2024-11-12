<style lang="sass">
.stepper-customize .q-stepper__step-inner
    padding: 0 !important
    margin: 0 !important
</style>

<template>

    <PageWrapper 
        title="Start a new collaboration!" 
        :rendering="loading" 
        leftDrawer
        goBack
        drawerTitle="Business Cockpit" 
    >
        <template #leftDrawer>
            <NavUserEntity />
        </template>

        <template #actions>
            <q-btn 
                class="q-mx-xs" 
                icon="preview" 
                outline 
                color="primary" 
                @click="showCollaboration = true"
            />
        </template>

        <!-- Progress -->
        <div class="w-content-md">
            <q-stepper
                v-model="progress"
                class="bg-transparent stepper-customize w-100"
                animated flat
            >
                <q-step
                    :name="1"
                    :done="progress > 1"
                    class="stepper-customize"
                    title="Initialization"
                    caption="Define reward"
                    icon="create_new_folder"
                >
                    <q-separator class="q-mb-lg"/>
                    <CollaborationStartCreate v-model="collaboration.reward" :awards="awards">
                        <template #navigation>
                            <q-btn 
                                @click="validateRewardSetup(collaboration.reward)" 
                                color="primary" 
                                label="Continue" 
                            />
                        </template>
                    </CollaborationStartCreate>
                </q-step>

                <q-step
                    :name="2"
                    :done="progress > 2"
                    class="stepper-customize"
                    title="Collaboration"
                    caption="Provide details"
                    icon="list"
                >
                    <q-separator class="q-mb-lg"/>
                    <CollaborationStartDetails 
                        v-model="collaboration.content"
                        :limitDetails="detailsMaxLenght" 
                    >
                        <template #navigation>
                            <q-btn 
                                @click="validateDetails(collaboration.content.details)" 
                                color="primary" 
                                label="Continue" 
                            />
                            <q-btn 
                                flat 
                                color="primary" 
                                @click="progress--" 
                                label="Back" 
                                class="q-ml-sm" 
                            />
                        </template>
                    </CollaborationStartDetails>
                </q-step>

                <q-step
                    :name="3"
                    :done="progress > 3"
                    class="stepper-customize"
                    title="Setting"
                    caption="Define workflow"
                    icon="settings"
                >
                    <q-separator class="q-mb-lg"/>
                    <CollaborationStartSettings v-model="collaboration.settings">
                        <template #navigation>
                            <q-btn 
                                @click="createCollaboration(collaboration)" 
                                color="primary" 
                                label="Create collaboration" 
                            />
                            <q-btn 
                                flat 
                                color="primary" 
                                @click="progress--" 
                                label="Back" 
                                class="q-ml-sm" 
                            />
                        </template>
                    </CollaborationStartSettings>
                </q-step>
            </q-stepper>
        </div>

        <!-- Preview -->
        <DialogWrapper 
            v-if="collaboration" 
            title="Collaboration preview" 
            :openDialog="showCollaboration" 
            @close="showCollaboration = false"
        >
            <q-card-section>
                <CollaborationPreview :collaboration="collaboration"/>
            </q-card-section>
        </DialogWrapper>
    </PageWrapper>

</template>

<script>
import { ref } from 'vue'
import NavUserEntity from 'src/components/navigation/NavUserEntity.vue';
import CollaborationStartCreate from './components/CollaborationStartCreate.vue';
import CollaborationStartDetails from './components/CollaborationStartDetails.vue';
import CollaborationStartSettings from './components/CollaborationStartSettings.vue';
import CollaborationPreview from 'components/CollaborationPreview.vue';

export default {
    name: 'BusinessCollaborationStart',
    components: {
        NavUserEntity, CollaborationStartCreate, CollaborationStartDetails, CollaborationStartSettings, CollaborationPreview
    },

    setup() {
        const detailsMaxLenght = 4999;
        const loading = ref(true);
        const progress = ref(1);
        const showCollaboration = ref(false);
        const collaboration = ref({
            reward: {
                award: null,
                title: '',
                meta: '',
                about: '',
                contact: '',
                skills: [],
            },
            content: {
                duration: '',
                details: '',
            },
            settings: {
                public_access: {
                    is_public: false,
                    access_limit: 1,
                    tags: []
                },
                request_form: {
                    required: false,
                    label: '',
                    attachement_labels: []
                }
            }
        })

        return {
            detailsMaxLenght,
            loading,
            progress,
            showCollaboration,
            collaboration
        };
    },

    data() {
        return {
            awards: [],
            departements: [], 
        }
    },

    mounted() {
        this.loadAttributes();
    },

    methods: {
        
        async loadAttributes() {
            try {
                this.loading = true;
                const departementsResponse = await this.$axios.get('/load-app-departements');
                this.departements = departementsResponse.data.departements;
                const awardsResponse = await this.$axios.get('/load-entity-awards/all');
                this.awards = awardsResponse.data.public_awards;
                this.collaboration.reward.contact = awardsResponse.data.entity.contact;
            } catch (error) {
                this.$toast.error(error.response ? error.response : error)
            } finally {
                this.loading = false;
            }
        },

        async createCollaboration(collaboration) {
            try {
                console.log(collaboration);
                if(
                    !collaboration.award
                    || !collaboration.title
                    || !collaboration.about
                    || !collaboration.contact
                ) throw 'Required fields: Award Type, Title, About and Contact.'

                this.$toast.load();
                const response = await this.$axios.post('/create-entity-collaboration', {
                    award_id: collaboration.award?.id,
                    title: collaboration.title,
                    meta: collaboration.meta,
                    about: collaboration.about,
                    details: collaboration.details,
                    contact: collaboration.contact,
                    rewarded_skills: collaboration.skills,
                    // date_start: collaboration.participation.date_start,
                    // time_start: collaboration.participation.time_start,
                    // duration: collaboration.participation.duration,
                    is_public: collaboration.settings.public_access.is_public,
                    access_limit: collaboration.settings.public_access.access_limit,
                    tags: collaboration.settings.public_access.tags,
                    request_form_required: collaboration.settings.request_form.required,
                    request_form_label: collaboration.settings.request_form.label,
                    request_form_attachement_labels: collaboration.settings.request_form.attachement_labels,
                });

                this.$toast.success(response.data.message);
            } catch (error) {
                this.$toast.error(error.response ? error.response : error)
            }
        },

        validateRewardSetup(reward) {
            if( !reward.award || !reward.title || !reward.about) 
                return this.$toast.error('Please define type, title and about fields.')
            return this.progress++;
        },

        validateDetails(detailsHTML) {
            if(detailsHTML.length <= this.detailsMaxLenght)
                return this.progress++;
            return this.$toast.error('Maximum length of ' + this.detailsMaxLenght + ' characters allowed.')
        },

        // async submitAward(collaboration) {
        //     try {
        //         if(
        //             !collaboration.award
        //             || !collaboration.title
        //             || !collaboration.about
        //             || !collaboration.contact
        //         ) throw 'Required fields: Award Type, Title, About and Contact.'

        //         this.$toast.load();
        //         const response = await this.$axios.post('/create-entity-collaboration', {
        //             award_id: collaboration.award?.id,
        //             departement_id: collaboration.departement?.id,
        //             title: collaboration.title,
        //             meta: collaboration.meta,
        //             about: collaboration.about,
        //             contact: collaboration.contact,
        //             tasks: collaboration.tasks,
        //             skills: collaboration.skills,
        //             date_start: collaboration.participation.date_start,
        //             time_start: collaboration.participation.time_start,
        //             duration: collaboration.participation.duration,
        //             tags: collaboration.tags
        //         });

        //         this.$toast.success(response.data.message);
        //     } catch (error) {
        //         this.$toast.error(error.response ? error.response : error)
        //     }
        // },

        // resetCollaboration() {
        //     this.collaboration.is_public = false
        //     this.collaboration.access_limit = 1
        //     this.collaboration.title = '';
        //     this.collaboration.meta = '';
        //     this.collaboration.about = '';
        //     this.collaboration.contact = '';
        //     this.collaboration.tasks = [];
        //     this.collaboration.skills = [];
        //     this.collaboration.departement = null;
        //     this.collaboration.tags = [];
        //     this.collaboration.participations = [];
        //     this.collaboration.files = [];
        // }
    },
};
</script>
