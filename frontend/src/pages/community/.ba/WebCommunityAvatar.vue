<template>

     <PageWrapper :rendering="loading" showExpansion>
        <template #expansion-item>
            <q-tabs v-model="tabPanel">
                <q-tab label="Rewards" name="rewards" />
                <q-tab label="Avatar" name="profile" />
            </q-tabs>
        </template>
        
        <!-- Content -->
        <NoData v-if="!avatar.avatar_id" text="No match found." />
        <div v-else class="row w-content justify-center">

            <!-- Profile -->
            <div class="col-auto">
                <CollaborationAvatar class="w-card-xl"  :user="user" :avatar="avatar"/>
            </div>

            <!-- Rewards -->
            <div class="col-12 col-md flex justify-center">
                <div class="w-card-xl" >
                    <q-list v-for="(reward, index) in collaborations" :key="index">
                        <q-expansion-item
                            outline color="primary"
                            expand-separator
                            default-opened
                            :icon="reward.award.icon"
                            :label="reward.award.label"
                        >
                            <q-card-section>
                                <q-timeline color="secondary">
                                    <q-timeline-entry
                                        v-for="(collaborator, indexItem) in reward.collaborations" 
                                        :key="indexItem"
                                        :title="collaborator.collaboration.title"
                                        :subtitle="collaborator.period_start ?? 'Individual'"
                                        :icon="reward.award.icon"
                                        side="right"
                                    >
                                        <div>
                                            {{ collaborator.collaboration.about ?? 'No information available.'  }}
                                        </div>
                                    </q-timeline-entry>
                                </q-timeline>
                            </q-card-section>
                        </q-expansion-item>
                    </q-list>
                    
                  

                    <CardSimple v-for="(reward, index) in collaborations" :key="index" color="secondary">
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
                                    noActions allowShortcuts
                                    @showPreview="setSelectedCollaboration(collaborator.collaboration)"
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
                                </CollaborationList>
                            </q-expansion-item>
                        </q-list>
                    </CardSimple>
                </div>
            </div>
        </div>



        <q-tab-panels class="w-100 bg-transparent" v-model="tabPanel" animated>
            
            <!-- Rewards -->
            <q-tab-panel name="rewards" class="flex justify-center q-pa-none" >
                <NoData v-if="collaborations.length === 0" text="No rewards published." />
                <div v-else class="w-card-xl">
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
                                    noActions allowShortcuts
                                    @showPreview="setSelectedCollaboration(collaborator.collaboration)"
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
                                </CollaborationList>
                            </q-expansion-item>
                        </q-list>
                    </CardSimple>
                </div>
            </q-tab-panel>

            <!-- Impressum -->
            <q-tab-panel name="profile" class="flex justify-center q-pa-none" >
                <CollaborationAvatar class="w-card-xl" :user="user" :avatar="avatar"/>
            </q-tab-panel>
        </q-tab-panels>

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
import CollaborationAvatar from 'components/CollaborationAvatar.vue';
import CollaborationList from 'components/CollaborationList.vue';
import CollaborationDetails from 'components/CollaborationDetails.vue';

export default {
    name: 'WebCommunityAvatar',
    components: {
           CollaborationAvatar,  CollaborationList, CollaborationDetails
    },

    setup() {
        const loading = ref(true);
        const tabPanel = ref('profile');
        const selectedCollaboration = ref(null);
        const showCollaboration = ref(false);

        const setSelectedCollaboration = (collaboration) => {
            selectedCollaboration.value = collaboration;
            showCollaboration.value = true;
        }

        return {
            tabPanel,
            loading,
            showCollaboration,
            selectedCollaboration,
            setSelectedCollaboration
        }
    },

    data() {
        return {
            avatar_id: this.$route.params.avatar_id
                ? this.$route.params.avatar_id
                : null,
            avatar: {},
            user: {},
            collaborations: []
        }
    },

    mounted() {
        if(this.avatar_id) this.loadCommunityAvatar(this.avatar_id)
    },

    methods: {
        async loadCommunityAvatar(avatar_id) {
            try {
                this.loading = true;
                const response = await this.$axios.get('/get-community-avatar', { params: { 
                    avatar_id: avatar_id
                }})
                this.avatar = response.data.avatar;
                this.user = this.avatar.user_profile
                this.collaborations = response.data.user_collaborations
            } catch (error) {
                console.log('CommunityAvatar', error.response ?? error)
            } finally {
                this.loading = false;
            }
        }
    }
};
</script>
