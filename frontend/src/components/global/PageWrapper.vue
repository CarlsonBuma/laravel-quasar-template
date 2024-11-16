<template>
    
    <q-page id="page-wrapper" :class="{'bg-design': bgDesign}">

        <!-- Leftdrawer DELETE -->
        <PageDrawer 
            v-if="leftDrawer" 
            :drawerTitle="drawerTitle"
            :drawerIsStatic="drawerIsStatic"
        />

        <!-- Navigation -->
        <div class="" :class="$q.dark.isActive ? 'text-sm bg-dark text-white' : 'bg-grey-1 text-dark'">
            <slot name="navigation" />
        </div>
        
        <!-- Content -->
        <q-pull-to-refresh class="w-100" :disable="!allowRefresh" @refresh="(done) => refresh(done)" >
            <div 
                class="row justify-center" 
                :class="noMargin ? '' : 'q-px-md-md q-py-sm'" 
            >
                <LoadingData 
                    v-if="rendering"
                    text="Processing data..."
                    :colorIcon="bgDesign ? 'white' : 'primary'"
                    :colorText="bgDesign ? 'text-white' : 'text-grey'"
                />
                <slot v-else />
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
        allowRefresh: Boolean,      // Reloading Site
        rendering: Boolean,         // Render content
        loading: Boolean,           // Loading data

        // Drawer
        leftDrawer: Boolean,
        
        // Design
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
