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
    
    <q-drawer
        class="shadow-sm" 
        :width="280"
        v-model="showDrawerLeft"
        @hide="$drawerLeft.value = false"
        :mini="drawerIsStatic ? false : $drawerLeft.value"
        show-if-above
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

</template>

<script>
export default {
    name: 'PageWrapper',

    components: {
        // Code
    },

    emits: [
        // Code
    ],

    props: {
        drawerIsStatic: Boolean,
        drawerTitle: String,
    },

    data() {
        return {
            showDrawerLeft: true,
        };
    },

    watch: {
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
    }
};
</script>
