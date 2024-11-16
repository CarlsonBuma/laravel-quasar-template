<template>    
    
    <div id="app-wrapper">
        <div id="app-design"></div>
        <q-layout view="hhh LpR fff">
            
            <!-- Header Top -->
            <q-header 
                reveal
                elevated 
                height-hint="98"
                class="text-white"
                :class="{
                    'bg-dark': $q.dark.isActive,
                    'bg-header-bright-mode': !$q.dark.isActive,
                }"
            >
                <NavVisitor 
                    :loading="loading"
                    @authUser="(route) => authUser(route)"
                    @logoutUser="logoutUser()"
                    @expandDrawer="$drawerLeft.value = !$drawerLeft.value"
                />
                <q-separator color="white" />
            </q-header>

            <!-- Content -->
            <q-page-container 
                id="app-content"
                :class="{
                    'background': !$q.dark.isActive,
                    'background-dark': $q.dark.isActive
                }"
            >
                <router-view 
                    @authorize="(route) => authUser(route)"
                    @removeSession="removeSession()"
                />
            </q-page-container>

            <!-- Footer -->
            <q-footer
                id="app-footer"
                bordered 
                :class="{
                    'bg-dark': $q.dark.isActive,
                    'bg-grey-1': !$q.dark.isActive,
                    'text-white': $q.dark.isActive,
                    'text-dark': !$q.dark.isActive,
                }"
            >
                <NavFoot />
            </q-footer>
        </q-layout>
    </div>
    
</template>

<script>
import { ref } from 'vue';
import NavVisitor from 'src/components/navigation/NavVisitor.vue';
import NavFoot from 'src/components/navigation/NavFoot.vue';
import CookieConsentOptions from 'src/boot/cookieConsent.js';

export default {
    name: 'App',
    components: {
        NavVisitor, NavFoot
    },

    setup() {
        const loading = ref(false);
        return {
            loading,
        }
    },

    mounted() {
        const darkMode = false;         // this.$q.dark.isActive;
        this.$q.dark.set(darkMode);
        this.$cc.run(CookieConsentOptions);
    },

    methods: {
        async authUser(route = '') {
            try {
                // Check Session Storage
                // Bearer Token - OAuth2
                this.loading = true;
                if(!this.$user.checkBearerTokenSet()) throw 'No valid session set.'
                if(!this.$user.access.user && this.$user.checkBearerTokenSet()) {
                    
                    // Authorize User
                    this.$user.setSession();
                    this.$toast.load('Authorizing...')
                    const response = await this.$axios.get('/auth');
                    this.$user.setUser(
                        response.data.user.id, 
                        response.data.user.name, 
                        response.data.user.avatar, 
                        response.data.user.email, 
                        response.data.access.is_admin,
                        response.data.access.access_cockpit
                    );
                }
                
                // Redirect if requested
                this.$toast.success('Session started.')
                if(route === 'back') this.$router.go(-1);
                else if(route) this.$router.push(route)
            } catch (error) {
                if(error.response) {
                    console.log('app.auth', error.response ? error.response : error)
                    this.$toast.error(error.response)
                }
            } finally {
                this.$toast.done();
                this.loading = false;
            }
        },

        async logoutUser() {
            try {
                this.$toast.load();
                const response = await this.$axios.post('/logout');
                this.$toast.success(response.data.message);
                this.removeSession();
            } catch (error) {
                console.log('app.logout', error.response ?? error)
            } finally {
                this.$toast.done()
            }
        },

        removeSession() {
            this.$user.removeBearerToken();
            this.$user.removeSession();
            this.$router.push('/');
        },

        showLogs() {
            console.log('Cookie', this.$cc);
            console.log('Quasar', this.$q);
            console.log('Axios', this.$axios);
            console.log('ENV', this.$env);
            console.log('Store', this.$user);
            console.log('Toast', this.$toast);
        }
    },
};
</script>
