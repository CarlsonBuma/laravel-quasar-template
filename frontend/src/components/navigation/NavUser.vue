<template>

    <div class="">
        <q-tabs
            class="text-weight-regular"
            :class="$q.dark.isActive ? 'text-sm bg-dark text-white' : 'bg-grey-1 text-dark'"
            active-color="primary"
            inline-label
        >
            <q-route-tab to="/avatar/profile" exact label="My profile" />
            <q-route-tab to="/account/settings" exact label="Settings" />
            <q-route-tab to="/account/access" exact label="My access" />
        </q-tabs>
        <q-separator />
    </div>

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
