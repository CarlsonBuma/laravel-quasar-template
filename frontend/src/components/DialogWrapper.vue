<template>

    <div class="absolute"> <!-- Div Required! -->
        <q-dialog 
            :full-width="fullWidth"
            :model-value="openDialog"
            :maximized="$q.screen.lt.sm || maximized"
            @hide="$emit('close')"
        >
            <q-card class="w-card">
                <q-card-section class="row items-center q-pb-none">
                    <div class="text-h6">{{ title }}</div>
                    <q-space />
                    <q-btn icon="close" flat round dense v-close-popup />
                </q-card-section>
                <q-separator class="q-mt-md"/>
                <slot />
            </q-card>
        </q-dialog>
    </div>
    
</template>

<script>
export default {
    name: 'DialogWrapper',

    props: {
        openDialog: Boolean,
        title: String,
        maximized: Boolean,
        fullWidth: Boolean
    },
    
    emits: [
        'close'
    ],
};
</script>
