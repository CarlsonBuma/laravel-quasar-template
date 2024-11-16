<template>

    <PageWrapper>
        <CardWrapper
            class="w-card-lg"
            :goBack="true"
            title="Create account"
            iconHeader="admin_panel_settings"
            note="*After registration, plese verify your account by the provided link we send you by email. You are able to set your password, after successful verification."
        >
            <!-- Registration -->
            <FormWrapper
                buttonText="Create account"
                buttonIcon="admin_panel_settings"
                @submit="registerUser(user.name, user.email, user.agreed)"
            >
                <!-- Username -->
                <q-input
                    filled
                    v-model="user.name"
                    label="Username"
                >
                    <template v-slot:prepend>
                        <q-icon name="person" />
                    </template>
                </q-input>

                <!-- Email -->
                <q-input
                    filled
                    type="email"
                    v-model="user.email"
                    label="Email"
                >
                    <template v-slot:prepend>
                        <q-icon name="mail" />
                    </template>
                </q-input>

                <!-- Terms & Conditions -->
                <div class="flex items-center">
                    <q-checkbox v-model="user.agreed" />I agree with&nbsp;
                    <router-link to="/legal" target="_blank">Terms &amp; Conditions</router-link>.
                </div>
            </FormWrapper>
        </CardWrapper>
    </PageWrapper>

</template>

<script>
import { ref } from 'vue';
import { regRules } from 'src/boot/globals.js';
import CardWrapper from 'components/CardWrapper.vue';
import FormWrapper from 'src/components/global/FormWrapper.vue';

export default {
    name: 'CreateNewAccount',
    components: {
         CardWrapper, FormWrapper
    },

    setup() {
        return {
            regRulesEmail: regRules.email,
            loading: ref(false),
            showTerms: ref(false)
        };
    },

    data() {
        return {
            user: {
                name: '',
                email: '',
                agreed: false,
            },
        };
    },
    
    methods: {
        async registerUser(name, email, agreed) {
            try {
                // Validate
                if(!name) throw 'Please enter a name.';
                else if (!this.regRulesEmail.test(email)) throw 'Invalid email.';
                else if (!agreed) throw 'Please agree with our Terms & Conditions.';
                // Create User
                this.$toast.load();
                const response = await this.$axios.post("/create-account", {
                    'name': name,
                    'email': email,
                    'terms': agreed,
                });
                this.$toast.success(response.data.message);
                // Redirect User to Verify Email
                this.$router.push({
                    name: 'EmailVerificationRequest', 
                    params: { 
                        email: email,
                    }
                });
            } catch (error) {
                this.$toast.error(error.response ? error.response : error);
            } finally {
                this.$toast.done();
                this.user.agreed = false;
            }
        },
    }
};
</script>
