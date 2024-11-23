<template>

    <q-infinite-scroll 
        :disable="disable"
        @load="(index, done) => onLoadRef(index, done)" 
        :offset="scrollOffset"
    >
        <slot />

        <!-- Loading -->
        <div v-if="loading" class="row justify-center">
            <LoadingData text="Loading data..." />
        </div>
    </q-infinite-scroll>
    
</template>

<script>
import { ref } from 'vue'
import LoadingData from 'src/components/global/LoadingData.vue';

export default {
    name: 'InfiteScroll',
    props: {
        loading: Boolean,
        disable: Boolean,
    },

    emits: [
        'load'
    ],

    components: {
        LoadingData
    },

    setup (props, context) {
        const scrollOffset = 1220;      // Trigger load event
        const scrollTargetRef = ref(null)
        const onLoadRef =  (index, done) => {
            context.emit('load')
            done();
        }
        return {
            scrollOffset,
            scrollTargetRef,
            onLoadRef
        };
    }
};
</script>
