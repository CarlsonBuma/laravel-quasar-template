<template>

    <q-tabs
        class="text-weight-regular"
        active-color="primary"
        inline-label
    >
        <q-route-tab to="/admin/dashboard" exact label="Dashboard" />
        <q-route-tab to="/admin/newsfeed" exact label="Newsfeed" />
    </q-tabs>

</template>

<script>
export default {
    name: 'NavAdmin',
};
</script>
