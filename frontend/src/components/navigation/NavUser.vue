<style lang="sass">
.my-account-menu-width
  width: 420px
</style>

<template>

    <q-toolbar class="q-pa-md">
                
        <!-- Logo -->
        <q-toolbar-title class="flex no-wrap items-center">
            <q-avatar @click="$emit('expandDrawer')" icon="school" font-size="34px" clickable />
            <q-btn-dropdown
                class="transparent text-subtitle1"
                :label="$env.APP_NAME"
                flat
            >
                <q-list>
                    <q-item clickable v-close-popup @click="$router.push('/landingpage')">
                        <q-item-section>
                            <span class="q-ml-xs cursor-pointer">Home</span>
                        </q-item-section>
                    </q-item>
                    <q-item clickable v-close-popup @click="$router.push('/awards')">
                        <q-item-section>
                            <span class="q-ml-xs cursor-pointer">Award Standards</span>
                        </q-item-section>
                    </q-item>
                    <q-item clickable v-close-popup @click="$router.push('/newsfeed')">
                        <q-item-section>
                            <span class="q-ml-xs cursor-pointer">Newsfeed</span>
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
                @click="$emit('authUser')" 
                flat 
                icon="person" 
                label="Member Area"
            />
        </div>
        <div v-else>

            <!-- Small Screen -->
            <q-fab class="lt-sm" padding="sm" color="white" outline glossy square vertical-actions-align="right" icon="apps" direction="down">
                <q-fab-action 
                    color="primary" square glossy text-color="white"
                    @click="$router.push('/my-avatar')" 
                    icon="contacts" 
                    label="My avatar" 
                />
                <q-fab-action 
                    v-if="$user.access.tokens[$env.APP_ACCESS_access_cockpit]"
                    color="primary" square glossy text-color="white" 
                    @click="$router.push('/my-entity')" 
                    icon="groups_3" 
                    label="Business Cockpit" 
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
                    @click="$router.push('/backpanel')" 
                    icon="admin_panel_settings" 
                    label="Backpanel" 
                />
            </q-fab>
            
            <!-- Big Screen -->
            <div class="gt-xs flex justify-end">

                <!-- Quicklinks -->
                <q-btn class="gt-xs" @click="$router.push('/shortcuts/entities')" flat size="12px" icon="bookmark_added" />

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
                                    <q-item v-if="$user.access.tokens[$env.APP_ACCESS_access_cockpit]" @click="$router.push('/my-entity')" clickable v-ripple >
                                        <q-item-section avatar>
                                            <q-icon name="groups_3" class="q-mr-sm" />
                                        </q-item-section>
                                        <q-item-section>
                                            <q-item-label>Business Cockpit</q-item-label>
                                        </q-item-section>
                                    </q-item>
                                    <q-item @click="$router.push('/my-avatar')" clickable v-ripple >
                                        <q-item-section avatar>
                                            <q-icon name="contacts" class="q-mr-sm" />
                                        </q-item-section>
                                        <q-item-section>
                                            <q-item-label>My avatar</q-item-label>
                                        </q-item-section>
                                    </q-item>
                                </q-list>
                            </div>

                            <!-- User Profile -->
                            <q-separator vertical inset class="q-mx-lg" />
                            <div class="col-auto text-center">
                                <q-avatar 
                                    v-if="$user.user.img_src"
                                    size="72px"
                                    color="primary" 
                                    text-color="white">
                                    <img :src="$user.user.img_src">
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
                                        @click="$router.push('/backpanel')"
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
    name: 'NavUser',

    props: {
        loading: Boolean
    },

    emits: [
        'expandDrawer',
        'authUser',
        'logoutUser',
    ],

    data() {
        return {
            // Data
        };
    },

    setup() {
        return {
            // Code
        }
    },
};
</script>
