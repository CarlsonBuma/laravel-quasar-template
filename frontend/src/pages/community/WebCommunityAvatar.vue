<template>

     <PageWrapper :rendering="loading">
        <NoData v-if="!avatar?.avatar_id" text="No match found." />
        <div v-else class="row w-content justify-center">

            <!-- Profile -->
            <div class="col-auto">
                <CollaborationAvatar class="w-card-xl"  :user="user" :avatar="avatar"/>
            </div>

            <!-- Collaborations -->
            <div class="col-auto">
                <CollaborationsTimeline 
                    class="w-card-xl" 
                    :collaborations="collaborations"
                    @preview="(collaboration) => setSelectedCollaboration(collaboration)"
                />
            </div>
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
import CollaborationAvatar from 'components/CollaborationAvatar.vue';
import CollaborationsTimeline from 'components/CollaborationsTimeline.vue';
import CollaborationDetails from 'components/CollaborationDetails.vue';

export default {
    name: 'WebCommunityAvatar',
    components: {
           CollaborationAvatar, CollaborationsTimeline, CollaborationDetails
    },

    setup() {
        const loading = ref(true);
        const tabPanel = ref(0);
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
        },
    }
};
</script>
