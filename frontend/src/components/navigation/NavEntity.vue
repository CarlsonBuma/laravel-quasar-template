<template>
    <q-toolbar>
        <span class="gt-xs">{{ title ?? 'My Cockpit' }}</span>
        <q-space />
        <q-tabs class="text-weight-regular" active-color="primary" shrink stretch>
            <q-route-tab to="/entity/dashboard" label="My Cockpit" />
            <q-route-tab to="/entity/profile" label="Profile" />
        </q-tabs>
    </q-toolbar>

</template>

<script>
export default {
    name: 'NavEntity',
    props: {
        title: String,
    },
};
</script>
