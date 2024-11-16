<template>

    <q-tabs
        class="text-weight-regular"
        active-color="primary"
        inline-label
    >
        <q-route-tab to="/dashboard" exact label="Dashboard" />
        <q-btn-dropdown auto-close stretch flat label="My avatar">
            <q-list>
                <q-item clickable @click="$router.push('/avatar/profile')">
                    <q-item-section>My profile</q-item-section>
                </q-item>
                <q-item clickable @click="$router.push('/account/settings')">
                    <q-item-section>Settings</q-item-section>
                </q-item>
                <q-item clickable @click="$router.push('/account/access')">
                    <q-item-section>My access</q-item-section>
                </q-item>
            </q-list>
        </q-btn-dropdown>
    </q-tabs>

</template>

<script>
export default {
    name: 'NavUser',
    setup() {
        return {
            // 
        };
    },
};
</script>
