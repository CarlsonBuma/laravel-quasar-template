<template>

    <q-list padding >   
        <q-item @click="$router.push('/my-entity')" clickable v-ripple>
            <q-item-section avatar>
                <q-icon name="dashboard" />
            </q-item-section>
            <q-item-section>Dashboard</q-item-section>
        </q-item>
        <q-item @click="$router.push('/my-entity/impressum')" clickable v-ripple>
            <q-item-section avatar>
                <q-icon name="business" />
            </q-item-section>
            <q-item-section>Impressum</q-item-section>
        </q-item>
    </q-list>

</template>

<script>
export default {
    name: 'NavUserEntity',
};
</script>
