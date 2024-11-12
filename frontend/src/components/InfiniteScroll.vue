<template>

    <q-infinite-scroll 
        :disable="disable"
        @load="(index, done) => onLoadRef(index, done)" 
        :offset="1220"
    >
        <slot />

        <!-- Loading -->
        <div class="row justify-center">
            <LoadingData v-if="loading" text="Loading data..." />
        </div>
    </q-infinite-scroll>
    
</template>

<script>
import { ref } from 'vue'
import LoadingData from 'components/LoadingData.vue';

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
        const scrollTargetRef = ref(null)
        const onLoadRef =  (index, done) => {
            context.emit('load')
            done();
        }
        return {
            scrollTargetRef,
            onLoadRef
        };
    }
};
</script>
