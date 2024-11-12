<template>

    <q-list separator>
        <q-item v-if="!collaborationArchive" clickable v-close-popup @click="$emit('showSearch')">
            <q-item-section avatar>
                <q-icon color="primary" name="group_add" />
            </q-item-section>
            <q-item-section>
                <q-item-label class="text-caption">Add collaborator</q-item-label>
            </q-item-section>
        </q-item>
        <q-item clickable v-close-popup>
            <q-item-section avatar>
                <q-icon color="orange" name="archive" />
            </q-item-section>
            <q-item-section>
                <q-item-label 
                    class="text-caption" 
                    @click="collaborationArchive
                        ? $emit('unarchiveCollaboration')
                        : $emit('archiveCollaboration')"
                >
                    {{ collaborationArchive ? "Unarchive collaboration" : "Archive collaboration" }}
                </q-item-label>
            </q-item-section>
        </q-item>
        <q-item clickable v-close-popup>
            <q-item-section avatar>
                <q-icon color="red" name="delete" />
            </q-item-section>
            <q-item-section>
                <q-item-label class="text-caption" @click="$emit('deleteCollaboration')">Remove collaboration</q-item-label>
            </q-item-section>
        </q-item>
    </q-list>

</template>

<script>
export default {
    name: 'CollaborationActions',
    props: {
        collaborationArchive: Boolean,
    },

    emits: [
        'showSearch',
        'archiveCollaboration',
        'unarchiveCollaboration',
        'deleteCollaboration',
    ],

    setup() {
        // 
    },
};
</script>
