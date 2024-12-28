<template>
    <q-toolbar>
        <span class="gt-xs">{{ title ?? 'My cockpit' }}</span>
        <q-space />
        <q-tabs class="text-weight-regular" active-color="primary" shrink stretch>
            <q-route-tab to="/cockpit/dashboard" icon="dashboard" />
            <q-route-tab to="/cockpit/profile" label="Profile settings" />
        </q-tabs>
    </q-toolbar>

</template>

<script>
export default {
    name: 'NavCockpit',
    props: {
        title: String,
    },
};
</script>
