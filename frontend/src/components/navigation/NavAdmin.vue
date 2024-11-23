<template>

    <q-toolbar>
        <span class="gt-xs">{{ title ?? 'Backpanel' }}</span>
        <q-space />
        <q-tabs class="text-weight-regular" active-color="primary" shrink stretch>
            <q-route-tab to="/admin/dashboard" exact label="Dashboard" />
            <q-route-tab to="/admin/newsfeed" exact label="Newsfeed" />
        </q-tabs>
    </q-toolbar>

</template>

<script>
export default {
    name: 'NavAdmin',
    props: {
        title: String,
    },
};
</script>
