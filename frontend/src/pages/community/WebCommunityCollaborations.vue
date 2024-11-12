<template>

     <PageWrapper :rendering="loading" showExpansion >

        <!-- Search -->
        <template #expansion-item>
            <SearchQuery 
                :loading="loading" 
                :awards="publicAwards"
                @search="(searchQuery, selectedAwardID) => searchCollaborations(searchQuery, selectedAwardID)"
            />
        </template>
        
        <!-- Collaborations Listings -->
        <div class="w-card-xl">
            <NoData v-if="collaborations.length === 0" text="No results found."/>
            <CardSimple
                class="design-card q-my-sm shadow-4"
                v-else
                v-for="(collaboration, index) in collaborations"
                :key="index" 
            >
                <CollaborationList
                    :title="collaboration.title"
                    :caption="collaboration.award.label"
                    :about="collaboration.about"
                    :contact="collaboration.contact"
                    noActions allowShortcuts
                    @showPreview="setSelectedCollaboration(collaboration)"
                >
                    <template #icon>
                        <q-img
                            v-if="collaboration.entity?.avatar"
                            :src="collaboration.entity.avatar"
                            spinner-color="white"
                            width="64px"
                            alt="logo"
                            loading="eager"
                        />
                        <q-icon 
                            v-else
                            :name="collaboration.award?.icon ? collaboration.award.icon : 'diversity_1'" 
                            color="primary" 
                            size="64px" 
                        />
                    </template>
                    <template #items-top>
                        <q-item>
                            <q-item-section>
                                <q-item-label 
                                    class="_hover" 
                                    @click="$router.push('/community/entities/' + collaboration.entity.id)"
                                >
                                    {{ collaboration.entity.name }}
                                </q-item-label>
                                <q-item-label caption lines="2">{{ collaboration.entity.slogan }}</q-item-label>
                            </q-item-section>
                            <q-item-section side>
                                <q-item-label caption class="text-right">
                                    <b>Duration:</b><br> 
                                    {{ collaboration.duration ?? 'Individual' }}
                                </q-item-label>
                            </q-item-section>
                        </q-item>
                    </template>
                </CollaborationList>
            </CardSimple>
        </div>

        <!-- Collaboration Preview -->
        <DialogWrapper 
            v-if="selectedCollaboration" 
            title="Collaboration details" 
            :openDialog="showCollaboration" 
            @close="showCollaboration = false"
        >
            <CollaborationDetails  :collaboration="selectedCollaboration"/>
        </DialogWrapper>
    </PageWrapper>

</template>

<script>
import { ref } from 'vue';

// import CollaborationSkills from 'src/components/CollaborationSkills.vue';
import CollaborationDetails from 'components/CollaborationDetails.vue';
import CollaborationList from 'components/CollaborationList.vue';
import SearchQuery from './components/SearchQuery.vue';

export default {
    name: 'WebCollaborations',
    components: {
          CollaborationDetails, CollaborationList, SearchQuery
    },

    setup() {
        const cancelTokenSource = ref(null)
        const axiosRequest = ref(0);
        const loading = ref(true);

        // Collaborations
        const showCollaboration = ref(false);
        const selectedCollaboration = ref({});
        const setSelectedCollaboration = (collaboration) => {
            selectedCollaboration.value = collaboration
            showCollaboration.value = true
        }

        // Skills
        const recommendedSkills = ref([]);
        const matchingSkills = ref([]);
        const setRecommendedSkills = (collaborations) => {
            recommendedSkills.value = [];
            const uniqueSkills = new Map();
            collaborations.forEach((collaboration) => {
                recommendedSkills.value.push(...collaboration.skills);
            });

            recommendedSkills.value.forEach(skill => {
                uniqueSkills.set(skill.label, skill);
            });
            
            recommendedSkills.value = Array.from(uniqueSkills.values());
        }

        return {
            cancelTokenSource,
            axiosRequest,
            loading,
            showCollaboration,
            selectedCollaboration,
            recommendedSkills,
            matchingSkills,
            setRecommendedSkills,
            setSelectedCollaboration
        }
    },

    data() {
        return {
            collaborations: [],
            publicAwards: [],
            selectedAward: null
        }
    },

    created() {
        this.getAwards();
    },

    methods: {

        async getAwards() {
            try {
                const response = await this.$axios.get('/load-app-awards');
                this.publicAwards = response.data.public_awards;
            } catch (error) {
                this.$toast.error(error.response ?? error)
            }
        },

        // Check ongoing reqeuests
        async searchCollaborations(searchQuery, selectedAwardID) {
            try {
                // Cancel any ongoing request 
                if (this.cancelTokenSource) { 
                    this.cancelTokenSource.cancel('Operation canceled due to new request.'); 
                } 
                
                // Create a new cancel token 
                this.cancelTokenSource = this.$axios.CancelToken.source()
                this.axiosRequest++;
                this.loading = true;

                const response = await this.$axios.get('/search-community-collaborations', { params: { 
                    searchQuery: searchQuery,
                    award_id: selectedAwardID,
                },
                    cancelToken: this.cancelTokenSource.token
                });

                this.collaborations = response.data.collaborations;
                this.matchingSkills = response.data.matching_skills;
                this.setRecommendedSkills(response.data.collaborations)
            } catch (error) {
                console.log(error)
                if(error.response) 
                    this.$toast.error(error.response ?? error)
            } finally {
                this.axiosRequest--;
                if(this.axiosRequest === 0) {
                    this.loading = false;
                }
            }
        },

        async addUserToCollaboration(collaboration) {
            try {
                this.$toast.load();
                const response = await this.$axios.post('/user-request-collaboration', {
                    collaboration_id: collaboration.id
                });
                this.$toast.success(response.data.message)
                collaboration.is_active_collaborator = true;
            } catch (error) {
                this.$toast.error(error.response ?? error)
            }
        },

        agreeUserCollaboration(collaboration) {
            if(!this.$user.access.user) {
                this.$emit('authorize')
                return;
            }

            this.$q.dialog({
                title: 'Request participation',
                message: 'Hereby, you confirm beeing an active member within the collaboration! The collaboration will be rewarded to your avatar after confirmation by publisher.',
                cancel: true,
            }).onOk(() => {
                this.addUserToCollaboration(collaboration)
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
