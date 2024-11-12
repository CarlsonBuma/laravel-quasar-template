<template>

    <div>
        <q-tabs v-model="tab" align="center">
            <!-- <q-route-tab to="/gigs" name="gigs" label="Open Gigs" /> -->
            <q-route-tab to="/community/services" name="services" label="Services" />
            <q-route-tab to="/community/collaborations" name="collaborations" label="Collaborations" />
        </q-tabs>
    </div>

</template>

<script>
export default {
    name: 'NavPage',
    props: {
        awards: Array,
    },

    emits: [
        //
    ],

    data() {
        return {
            tab: 'mails'
        };
    },

    mounted() {
        // 
    },
};
</script>
