<template>

    <div class="w-100 flex justify-center q-pa-sm">
        <div class="w-title-lg">
            <span class="text-caption" v-if="header"><em>{{ header }}</em></span>
            <h1 class="text-h4 q-my-sm" v-if="title">{{ title }}</h1>
            <p class="text-subtitle1 q-mt-sm" v-if="subtitle">{{ subtitle }}</p>
            <slot />
        </div>
    </div>
    
</template>

<script>
export default {
    name: 'SectionTitle',
    props: {
        header: String,
        title: String,
        subtitle: String,
    },
};
</script>
