<template>

    <q-list padding >
        <q-item 
            @click="$router.push('/backpanel')"
            clickable 
            v-ripple
        >
            <q-item-section avatar>
                <q-icon name="dashboard" />
            </q-item-section>
            <q-item-section>
                Dashboard
            </q-item-section>
        </q-item>
        <q-item 
            @click="$router.push('/releasemanagement')"
            clickable 
            v-ripple
        >
            <q-item-section avatar>
                <q-icon name="new_releases" />
            </q-item-section>
            <q-item-section>
                Newsfeed &amp; Releases
            </q-item-section>
        </q-item>
    </q-list>

</template>

<script>
export default {
    name: 'NavAdmin',
};
</script>
