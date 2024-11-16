<template>
    
    <q-page id="page-wrapper" :class="{'bg-design': bgDesign}">

        <!-- Leftdrawer -->
        <PageDrawer 
            v-if="leftDrawer" 
            :drawerTitle="drawerTitle"
            :drawerIsStatic="drawerIsStatic"
        />

        <!-- Navigation -->
        <slot name="navigation" />
        
        <!-- Refresher -->
        <q-pull-to-refresh class="w-100" :disable="!allowRefresh" @refresh="(done) => refresh(done)" >
            <div class="container" :class="noMargin ? '' : 'q-py-lg'" >
                
                <!-- Rendering -->
                <div v-if="rendering" class="row flex justify-center">
                    <LoadingData 
                        text="Processing data..."
                        :colorIcon="bgDesign ? 'white' : 'primary'"
                        :colorText="bgDesign ? 'text-white' : 'text-grey'"
                    />
                </div>

                <!-- Content -->
                <div 
                    v-else 
                    class="w-100 flex justify-center q-px-none" 
                    :class="noMargin ? '' : 'q-px-md-md q-py-sm'" 
                >
                    <slot />
                </div> 
            </div>
        </q-pull-to-refresh>
    </q-page>

</template>

<script>
import { QSpinnerGears } from 'quasar'

export default {
    name: 'PageWrapper',
    components: {
        // 
    },

    emits: [
        'refresh',
    ],

    props: {
        // Contentnavigation
        allowRefresh: Boolean,      // Reloading Site

        // Request Handling
        rendering: Boolean,         // Navigation allowed
        loading: Boolean,           // Navigation forbidden

        // Drawer
        leftDrawer: Boolean,
        
        // Design
        overflow: Boolean,
        bgDesign: Boolean,
        noMargin: Boolean,
    },

    setup() {
        return {
            //
        };
    },

    watch: {
        loading(value) {
            value ? this.startRendering() : this.stopRendering();
        },
    },

    methods: {

        refresh(done) {
            this.$emit('refresh');
            done();
        },

        startRendering() {
            this.$q.loading.show({
                boxClass: 'page-loading-block',
                spinner: QSpinnerGears,
                message: 'Loading data. Please wait...',
            })
        },

        stopRendering() {
            this.$q.loading.hide()
        }
    }
};
</script>
