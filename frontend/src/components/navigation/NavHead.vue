<style lang="sass">
.my-account-menu-width
  width: 420px
</style>

<template>

    <q-toolbar class="">
                
        <!-- Logo -->
        <q-toolbar-title class="flex no-wrap items-center">
            <q-avatar @click="$emit('logoClick')" icon="school" size="lg" clickable />
            <q-btn-dropdown
                class="transparent text-subtitle1"
                size="md"
                :label="$env.APP_NAME"
                flat
            >
                <q-list>
                    <q-item clickable v-close-popup @click="$router.push('/landingpage')">
                        <q-item-section>
                            <span class="q-ml-xs cursor-pointer">Home</span>
                        </q-item-section>
                    </q-item>
                    <q-item clickable v-close-popup @click="$router.push('/newsfeed')">
                        <q-item-section>
                            <span class="q-ml-xs cursor-pointer">Newsfeed</span>
                        </q-item-section>
                    </q-item>
                    <q-item clickable v-close-popup @click="$router.push('/pricing')">
                        <q-item-section>
                            <span class="q-ml-xs cursor-pointer">Pricing</span>
                        </q-item-section>
                    </q-item>
                    <q-item clickable v-close-popup @click="$router.push('/about')">
                        <q-item-section>
                            <span class="q-ml-xs cursor-pointer">About us</span>
                        </q-item-section>
                    </q-item>
                </q-list>
            </q-btn-dropdown>
        </q-toolbar-title>

        <!-- Authorization -->
        <div v-if="!$user.access.user">
            <q-btn
                v-if="$allowAuth"
                :disable="loading"
                @click="goMemberArea()" 
                flat 
                size="sm"
                icon="person" 
                label="Member Area"
            />
        </div>

        <!-- User Navigation -->
        <div v-else>

            <!-- Small Screen -->
            <q-fab class="lt-sm" padding="sm" color="white" outline glossy square vertical-actions-align="right" icon="apps" direction="down">
                <q-fab-action 
                    color="primary" square glossy text-color="white"
                    @click="$router.push('/user/dashboard')" 
                    icon="contacts" 
                    label="My Avatar" 
                />
                <q-fab-action 
                    v-if="$user.access.tokens[$env.APP_ACCESS_COCKPIT]"
                    color="primary" square glossy text-color="white" 
                    @click="$router.push('/entity/dashboard')" 
                    icon="groups_3" 
                    label="My Cockpit" 
                />
                <q-fab-action 
                    color="primary" square glossy text-color="white"
                    @click="$emit('logoutUser')" 
                    icon="logout" 
                    label="Logout" 
                />
                <q-fab-action 
                    v-if="$user.access.admin" 
                    color="primary" 
                    square glossy text-color="white" 
                    @click="$router.push('/admin/dashboard')" 
                    icon="admin_panel_settings" 
                    label="Backpanel" 
                />
            </q-fab>
            
            <!-- Big Screen -->
            <div class="gt-xs flex justify-end">

                <!-- Quicklinks -->
                <q-btn class="gt-xs" @click="$router.push('/')" flat size="12px" icon="bookmark_added" />

                <!-- Account -->
                <q-separator vertical color="white" class="q-mt-md q-mb-md q-ml-sm q-mr-sm" />
                <q-item clickable v-ripple >
                    <q-item-section>My Account</q-item-section>
                    <q-menu class="my-account-menu-width">
                        <div class="row no-wrap q-pa-md">

                            <!-- Profile Settings -->
                            <div class="col-grow">
                                <span class="text-overline">My Account</span>
                                <q-separator class="w-100"/>
                                <q-list padding >
                                    <q-item 
                                        v-if="$user.access.tokens[$env.APP_ACCESS_COCKPIT]" 
                                        @click="$router.push('/entity/dashboard')" 
                                        clickable v-ripple 
                                    >
                                        <q-item-section avatar>
                                            <q-icon name="groups_3" class="q-mr-sm" />
                                        </q-item-section>
                                        <q-item-section>
                                            <q-item-label>My Cockpit</q-item-label>
                                        </q-item-section>
                                    </q-item>
                                    <q-item @click="$router.push('/user/dashboard')" clickable v-ripple >
                                        <q-item-section avatar>
                                            <q-icon name="contacts" class="q-mr-sm" />
                                        </q-item-section>
                                        <q-item-section>
                                            <q-item-label>My Avatar</q-item-label>
                                        </q-item-section>
                                    </q-item>
                                </q-list>
                            </div>

                            <!-- User Profile -->
                            <q-separator vertical inset class="q-mx-lg" />
                            <div class="col-auto text-center">
                                <q-avatar 
                                    v-if="$user.user.avatar_src"
                                    size="72px"
                                    color="primary" 
                                    text-color="white">
                                    <img :src="$user.user.avatar_src">
                                </q-avatar>
                                <q-avatar 
                                    v-else
                                    size="72px"
                                    color="primary" 
                                    text-color="white">U
                                </q-avatar>
                                <div class="text-subtitle2 q-mt-sm q-mb-xs">{{ $user.user.name }}</div>
                                <div class="w-100">
                                    <q-btn
                                        @click="$emit('logoutUser')"
                                        color="primary"
                                        label="Logout"
                                        push
                                        size="sm"
                                        v-close-popup
                                    />
                                </div>
                                <div class="w-100">
                                    <q-btn
                                        v-if="$user.access.admin"
                                        @click="$router.push('/admin/dashboard')"
                                        label="Backpanel"
                                        class="q-mt-sm"
                                        color="primary"
                                        push
                                        size="sm"
                                        v-close-popup
                                    />
                                </div>

                                <!-- Darkmode -->
                                <q-separator class="w-100 q-mt-md"/>
                                <q-toggle 
                                    :model-value="$q.dark.mode"
                                    @click="$q.dark.toggle()" 
                                    label="Darkmode"
                                />
                            </div>
                        </div>
                    </q-menu>
                </q-item>
            </div>
        </div>
    </q-toolbar>

</template>

<script>
export default {
    name: 'NavHead',

    props: {
        loading: Boolean
    },

    emits: [
        'logoClick',
        'authUser',
        'logoutUser',
    ],

    methods: {
        async goMemberArea() {
            if(!this.$user.checkBearerTokenSet()) this.$router.push('/login')
            else this.$emit('authUser', '/user/dashboard')
        }
    }
};
</script>
