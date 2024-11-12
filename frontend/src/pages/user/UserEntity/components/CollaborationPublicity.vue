<template>

    <q-btn-dropdown 
        :dropdown-icon="collaborationData.is_public ? 'public' : 'vpn_lock'" 
        size="12px" 
        color="primary"
        flat dense round
    >
        <q-list class="w-card-lg" separator>
            <q-item>
                <q-item-section>
                    <q-item-section>Public Access</q-item-section>
                    <q-item-label caption>
                        Collaboration can publicly be requested by our community collaborators.
                        If set to private, collaborators are able to request only by provided sign-up link.
                    </q-item-label>
                </q-item-section>
                <q-item-section side>
                    <q-toggle 
                        dense 
                        v-model="collaborationData.is_public"
                        @update:model-value="$emit('updatePublicAccess', collaborationData.is_public)"
                    />
                </q-item-section>
            </q-item>
            <q-item>
                <q-item-section class="q-pb-md">
                    <q-input 
                        :model-value="collaborationRedirectLink + collaboration.token" 
                        label="Link to sign-up:"
                        hint="By requesting over provided sign-up link, collaboration will be released immediately."
                    >
                        <template v-slot:append>
                            <q-icon 
                                class="cursor-pointer" 
                                name="content_copy" 
                                @click="copyToClipboard(collaborationRedirectLink + collaboration.token)" 
                            />
                        </template>
                    </q-input>
                </q-item-section>
            </q-item>
            <q-item v-if="collaboration.is_public">
                <q-item-section>
                    <q-select 
                        clearable use-input use-chips dense
                        multiple counter hide-dropdown-icon
                        label="SEO tags"
                        hint="Choose up to 9 tags, to improve your SEO by our search-engine."
                        v-model="collaborationData.tags"
                        max-values="9"
                        input-debounce="0"
                        new-value-mode="add-unique"
                        @clear="collaborationData.tags = []"
                    >
                        <template v-slot:append>
                            <q-btn 
                                color="orange" 
                                icon="update" 
                                outline flat round
                                @click="$emit('updatePublicTags', collaborationData.tags)"
                            />
                        </template>
                    </q-select>
                </q-item-section>
            </q-item>
        </q-list>
    </q-btn-dropdown>

</template>

<script>
import { ref } from 'vue';

export default {
    name: 'CollaborationPublicity',
    props: {
        collaboration: Object,
    },

    emits: [
        'updatePublicAccess',
        'updatePublicTags',
    ],

    setup() {
        const collaborationData = ref({});
        const collaborationRedirectLink = ref('https://redirect-to-access-collaboration/');
        return {
            collaborationData,
            collaborationRedirectLink,
        };
    },

    mounted() {
        this.collaborationData = this.collaboration;
    },

    methods: {
        copyToClipboard(value) {
            navigator.clipboard.writeText(value).then(() => {
                this.$toast.success('Copied to clipboard!')
            });
        }  
    },
};
</script>
