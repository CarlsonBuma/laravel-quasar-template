<template>

    <CardSimple 
        class="w-card-sm"
        v-if="award.id"
        :title="award.label"
    >
        <div class="flex justify-center q-mt-sm">
            <q-icon 
                :name="award.icon ? award.icon : 'badge'" 
                color="primary" 
                size="120px" 
            />
        </div>

        <q-card-section>
            <p class="text-caption q-my-none">{{ award.description }}</p>
        </q-card-section>

        <q-separator />
        <q-card-section class="text-caption text-right">
            <p class="w-100 q-my-none" v-if="award.open_requests">Open requests: {{ award.open_requests }}</p>
            <p class="w-100 q-my-none">Ongoing collaborations: {{ award.active_collaborations }}</p>
            <p class="w-100 q-my-none">Active collaborators: {{ award.active_collaborators }}</p>
        </q-card-section>

        <q-separator />
        <q-card-actions align="right" class="q-py-md" vertical >
            <q-btn 
                label="Manage collaborations" 
                size="sm" 
                color="primary" 
                icon="emoji_events" 
                outline 
                @click="$emit('loadCollaborations')"
            />
        </q-card-actions>
    </CardSimple>

</template>

<script>
export default {
    name: 'AwardList',
    components: {
        // 
    },

    props: {
        award: Object,
    },

    emits: [
        'loadCollaborations'
    ],

    setup() {
        return {
            
        };
    },

    data() {
        return {
            
        }
    },

    mounted() {
        
    },

    methods: {
        
    },
};
</script>
