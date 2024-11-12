<template>

    <q-list padding >
        <q-item @click="$router.push('/my-avatar')" clickable v-ripple>
            <q-item-section avatar>
                <q-icon name="dashboard" />
            </q-item-section>
            <q-item-section>Dashboard</q-item-section>
        </q-item>

        <q-item @click="$router.push('/my-avatar/rewards')" clickable v-ripple>
            <q-item-section avatar>
                <q-icon name="school" />
            </q-item-section>
            <q-item-section>My rewards</q-item-section>
        </q-item>
       
        <q-separator class="q-my-sm"/>
        <q-item @click="$router.push('/my-avatar/profile')" clickable v-ripple>
            <q-item-section avatar>
                <q-icon name="account_circle" />
            </q-item-section>
            <q-item-section>My profile</q-item-section>
        </q-item>
        
        <q-item @click="$router.push('/account/settings')" clickable v-ripple>
            <q-item-section avatar>
                <q-icon name="settings" />
            </q-item-section>
            <q-item-section>Settings</q-item-section>
        </q-item>

        <q-item @click="$router.push('/account/access')" clickable v-ripple>
            <q-item-section avatar>
                <q-icon name="auto_awesome" />
            </q-item-section>
            <q-item-section>My access</q-item-section>
        </q-item>
    </q-list>

</template>

<script>
export default {
    name: 'NavUserAvatar',
};
</script>
