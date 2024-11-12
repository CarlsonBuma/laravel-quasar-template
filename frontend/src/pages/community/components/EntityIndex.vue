<template>

    <NoData 
        v-if="!entity?.name" 
        text="Entity does not exist."
    />

    <CardSimple v-else>
        <q-img 
            v-if="entity.avatar" 
            :src="entity.avatar" 
            :ratio="1" 
            alt="entity-avatar"
            img-class="banner-image"
            fit="fill" 
            loading="eager"
        >
            <div v-if="entity.name" class="absolute-bottom">
                <div class="flex justify-center text-h6">{{ entity.name }}</div>
                <div class="flex justify-center text-caption">{{ entity.slogan }}</div>
            </div>
        </q-img>

        <div class="w-100 flex justify-center q-pa-lg" v-else>
            <q-avatar  color="primary" text-color="white" size="220px" square>U</q-avatar>
        </div>
        
        <!-- Shortcuts -->
        <q-card-actions align="around" v-if="allowActions">
            <slot name="actions" />
        </q-card-actions>

        <q-separator />
        <q-card-section>
            <p class="text-caption q-ma-none">{{ entity.about ? entity.about : 'No descriptions available.'}}</p>
        </q-card-section>
    </CardSimple>
    
</template>

<script>
export default {
    name: 'EntityIndex',
    components: {
        //
    },
    
    props: {
        entity: Object,
        defaultOpen: Boolean,
        allowActions: Boolean,
    },

    emits: [
        // Emits
    ],

    setup() {
        return {
            // Code
        }
    },
};
</script>
