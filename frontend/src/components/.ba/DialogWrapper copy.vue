<style lang="sass">
.component-dialog-wrapper-bg-transparent
    background: rgba(0,0,0,0.9)
.component-dialog-wrapper-bg-white
    background: rgba(255, 255, 255, 0.5)
.component-dialog-wrapper-outline
    border: solid white 1px
.component-dialog-wrapper-nav
  position: sticky
  top: 0
  z-index: 1000 !important
</style>

<template>

    <q-dialog 
        v-model="openDialog"
        :maximized="$q.screen.width < 1024 || maximized"
        :full-width="$q.screen.width >= 1024"
        @hide="$emit('close')"
        transition-show="slide-up"
        transition-hide="slide-down"
        transition-duration="600"
    >
        <div class="container q-pb-md component-dialog-wrapper-bg-transparent">
            <q-bar class="bg-design component-dialog-wrapper-nav q-mb-lg" >
                <span class="text-white text-caption" v-if="title">{{ title }}</span>
                <q-space />
                <q-btn color="white" v-if="!noSearch" @click="$emit('search')" flat icon="search" v-close-popup>
                    <q-tooltip class="bg-white text-dark">Search</q-tooltip>
                </q-btn>
                <q-btn flat color="white" icon="close" v-close-popup>
                    <q-tooltip class="bg-white text-dark">Close</q-tooltip>
                </q-btn>
            </q-bar>
            <slot />
        </div>
    </q-dialog>
    
</template>

<script>
export default {
    name: 'DialogWrapper',

    props: {
        showDialog: Boolean,
        noSearch: Boolean,
        title: String,
        maximized: Boolean,
    },
    
    emits: [
        'search',
        'close'
    ],

    data() {
        return {
            openDialog: false
        };
    },
    
    watch: {
        showDialog() {
            this.openDialog = true
        },
    },
};
</script>
