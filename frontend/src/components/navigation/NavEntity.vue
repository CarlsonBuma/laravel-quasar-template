<template>

    <q-tabs class="text-weight-regular" active-color="primary" inline-label>
        <q-route-tab to="/entity/dashboard" label="My Cockpit" />
        <q-route-tab to="/entity/profile" label="Profile" />
    </q-tabs>

</template>

<script>
export default {
    name: 'NavEntity',
};
</script>
