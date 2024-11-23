<template>

    <q-toolbar>
        <span>{{ title ?? 'My Avatar' }}</span>
        <q-space />
        <q-tabs class="text-weight-regular" active-color="primary" shrink stretch>
            <q-route-tab to="/user/dashboard" exact label="Dashboard" />
            <q-btn-dropdown auto-close stretch flat label="My profile">
                <q-list>
                    <q-item clickable @click="$router.push('/account/access')">
                        <q-item-section>My access</q-item-section>
                    </q-item>
                    <q-item clickable @click="$router.push('/account/settings')">
                        <q-item-section>Settings</q-item-section>
                    </q-item>
                </q-list>
            </q-btn-dropdown>
        </q-tabs>
    </q-toolbar>

</template>

<script>
export default {
    name: 'NavUser',
    props: {
        title: String,
    },
};
</script>
