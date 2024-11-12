<style lang="sass">
#pagewrapper-top-expansion
  width: 100%
.pagewrapper-top-expansion-header
    display: none
.rounded-border
    border-radius: 3px
.q-drawer--left
    box-shadow: $shadow-1
</style>

<template>
    
    <q-page id="page-wrapper" :class="{'bg-design': bgDesign}">
        
        <!-- Left Navigation -->
        <q-drawer
            class="shadow-sm" 
            v-if="leftDrawer"
            v-model="showDrawerLeft"
            @hide="$drawerLeft.value = false"
            :mini="drawerIsStatic ? false : $drawerLeft.value"
            show-if-above
            :width="280"
        >
            <q-scroll-area class="fit">
                <!-- Handle Drawer -->
                <div class="row flex q-pt-sm" >
                    <q-item-label header v-if="drawerTitle">{{ drawerTitle }}</q-item-label> 
                    <q-space />
                    <div class="lt-md q-px-xs">
                        <q-btn
                            @click="hideDrawer()" 
                            dense 
                            flat
                            color="primary" 
                            icon="close" 
                            v-close-popup
                        />
                    </div>
                </div>
                <!-- Content -->
                <q-separator v-if="drawerTitle" class="w-100"/>
                <slot name="leftDrawer"/>
            </q-scroll-area>
        </q-drawer>

        <!-- Expansion -->
        <q-expansion-item 
            id="pagewrapper-top-expansion" 
            :model-value="showExpansion" 
            header-class="pagewrapper-top-expansion-header"
            class="shadow-sm"
        >
            <q-card class="q-pt-sm">
                 <q-card-section v-if="expansionActions" class="row items-center q-pb-none">
                    <div class="text-h6">{{ expansionTitle }}</div>
                    <q-space />
                    <q-btn  icon="check" color="green" flat round dense @click="$emit('expansionSet')" />
                    <q-btn icon="close" flat round dense @click="$emit('expansionClose')" />
                </q-card-section>
                <q-separator v-if="expansionActions" class="q-mt-md"/>
                <!-- Content -->
                <slot name="expansion-item"/>
            </q-card>
        </q-expansion-item>
        
        <!-- Refresher -->
        <q-pull-to-refresh 
            :disable="!allowRefresh"
            @refresh="(done) => refresh(done)"
            class="w-100"
        >

            <!-- Header -->
            <div 
                v-if="title" 
                class="shadow-1 q-pa-sm" 
                :class="{
                    'bg-dark': $q.dark.isActive,
                    'bg-white': !$q.dark.isActive,
                }"
            >
                <div class="row flex items-center q-py-xs">
                    <!-- Title & Navigation -->
                    <div class="col-12 col-sm-auto flex q-pt-xs">
                        <q-btn 
                            flat round dense
                            text-color="primary" 
                            :icon="goBack ? 'arrow_left' : 'arrow_right'"
                            @click="goBack ? $router.go(-1) : null"
                        />
                        <span class="flex items-center text-subtitl">{{ title }}</span>
                    </div>
                    <div class="col flex justify-end items-center q-mx-md">
                        <slot name="actions"/>
                    </div>
                </div>
            </div>

            <!-- Content Container -->
            <div 
                class="container" 
                :class="{
                    'q-py-lg': !noMargin
                }"
            >
                <!-- Content Head -->
                <div class="row">
                    <slot name="head" />
                </div>
                
                <!-- Rendering -->
                <div v-if="rendering" class="row flex justify-center">
                    <LoadingData 
                        text="Processing data..."
                        :colorIcon="bgDesign ? 'white' : 'primary'"
                        :colorText="bgDesign ? 'text-white' : 'text-grey'"
                    />
                </div>

                <!-- Content -->
                <div v-else 
                    class="w-100 flex justify-center q-px-none" 
                    :class="{
                        'q-px-md-md': !noMargin,
                        'q-py-sm': !noMargin
                    }"
                >
                    <slot />
                </div> 
            </div>
        </q-pull-to-refresh>
    </q-page>

</template>

<script>
import { QSpinnerGears } from 'quasar'
import LoadingData from 'components/LoadingData.vue';

export default {
    name: 'PageWrapper',

    components: {
        LoadingData
    },

    emits: [
        'refresh',
        'expansionClose',
        'expansionSet'
    ],

    props: {
        // Contentnavigation
        goBack: Boolean,            // Navigate Back
        allowRefresh: Boolean,      // Reloading Site

        // Request Handling
        rendering: Boolean,         // Navigation allowed
        loading: Boolean,           // Navigation forbidden

        // Drawer
        leftDrawer: Boolean,
        drawerIsStatic: Boolean,
        drawerTitle: String,

        // Expansion Item
        expansionActions: Boolean,
        showExpansion: Boolean,
        expansionTitle: String,

        // Content
        title: String,
        subtitle: String,
        
        // Design
        overflow: Boolean,
        bgDesign: Boolean,
        noMargin: Boolean,
    },

    data() {
        return {
            showDrawerLeft: true,
        };
    },

    watch: {
        // Rendering Page
        loading(value) {
            value ? this.startRendering() : this.stopRendering();
        },

        // If lower than Width- MD, we do not want Mini-Mode
        // Instead We want to Show / Hide DrawerLeft
        '$drawerLeft.value'(value) {
            this.showDrawerLeft = true;
            if(window.innerWidth < 1024) this.showDrawerLeft = value;
        }
    },

    methods: {

        hideDrawer() {
            this.$drawerLeft.value = false;
            this.showDrawerLeft = false;
        },

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
