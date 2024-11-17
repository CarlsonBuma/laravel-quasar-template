<template>

    <PageWrapper>
        <template #navigation>
            <NavUser />
        </template>

        <div class="row w-100 flex justify-center">
            <div class="avatar-width">

                <!-- Public -->
                <CardSimple 
                    title="Join our community" 
                    tooltip="By joining our community, other collaborators are able to find your avatar."
                    tooltipIconColor="primary"
                >
                    <template #actions>
                        <div class="flex justify-end items-center w-100">
                            <q-toggle class="q-mx-md" v-model="$user.user.is_public" dense />
                            <div>
                                <q-btn 
                                    @click="submitPublicity($user.user.is_public)" 
                                    outline 
                                    rounded
                                    size="sm"
                                    color="primary" 
                                    label="Update" 
                                />
                            </div>
                        </div>
                    </template>
                </CardSimple>
                
                <!-- Image -->
                <CardUploadImage 
                    allowUpdate
                    :userAvatar="$user.user.avatar_src"
                    @update="(src, avatar, deleteAvatar) => saveAvatar(src, avatar, deleteAvatar)"
                    @upload="(src, avatar) => {
                        $user.user.avatar_src = avatar;
                    }"
                />

                <!-- User -->
                <CardSimple>
                    <SectionSplitFix class="q-pt-xs q-px-xs q-pb-none">
                        <template #left>
                            <qrcode-vue :value="qr_value" :size="120" level="H" />
                        </template>
                        <template #right >
                            <div class="q-pa-md text-left">
                                <span class="text-caption"><b>ID:</b>&nbsp;#{{ $user.user.id }}</span><br>
                                <span class="text-caption"><b>Username:</b>&nbsp;{{ $user.user.name }}</span><br>
                                <span class="text-caption"><b>Email:</b>&nbsp;{{ $user.user.email }}</span>
                            </div>
                        </template>
                    </SectionSplitFix>
                </CardSimple>
            </div>
            <div class="avatar-width">

                <!-- Username -->
                <CardSimple title="Change username">
                    <q-card-section >
                        <FormWrapper
                            buttonText="Change name"
                            buttonIcon="person"
                            @submit="submitUsername()"
                        >
                            <q-input
                                filled
                                v-model="$user.user.name"
                                label="Username"
                            />
                        </FormWrapper>
                    </q-card-section>
                </CardSimple> 

                <!-- Password -->
                <CardSimple title="Change password">
                    <q-card-section >
                        <FormWrapper
                            buttonText="Change password"
                            buttonIcon="lock"
                            @submit="submitPassword(password.current, password.new, password.confirm)"
                        >
                            <q-input
                                filled
                                type="password"
                                v-model="password.current"
                                label="Confirm current password"
                            />
                            <div>
                                <q-input
                                    filled
                                    type="password"
                                    v-model="password.new"
                                    label="Enter new password"
                                >
                                    <!-- Validation -->
                                    <template v-slot:append>
                                        <q-icon name="info">
                                            <q-tooltip>
                                                <PasswordCheck
                                                    :password="password.new"
                                                    :password_confirm="password.confirm"
                                                />
                                            </q-tooltip>
                                        </q-icon>
                                    </template>
                                </q-input>
                                <q-input
                                    filled
                                    type="password"
                                    v-model="password.confirm"
                                    label="Confirm new password"
                                />
                            </div>
                        </FormWrapper>
                    </q-card-section>
                </CardSimple> 
            </div>

            <!-- Transfer Account -->
            <div class="avatar-width">
                <CardSimple 
                    title="Transfer account" 
                    tooltip="Change the email of your account. The new email must be verified by the new owner."
                    tooltipIconColor="orange"
                    note="*To undo the transfering process, please login with your old credentials and follow the procedure."
                >
                    <q-card-section>
                        <FormWrapper
                            buttonText="Change owner"
                            buttonIcon="people_alt"
                            @submit="submitEmail(this.transferEmail, this.emailPassword)"
                        >
                            <q-input
                                filled
                                disable
                                type="email"
                                v-model="$user.user.email"
                                label="Current owner"
                            />
                            <q-input
                                filled
                                type="email"
                                v-model="transferEmail"
                                label="Transfer account to"
                                placeholder="Enter email"
                            />
                            <q-input
                                filled
                                type="password"
                                v-model="emailPassword"
                                label="Confirm by password"
                            />
                        </FormWrapper>
                    </q-card-section>
                </CardSimple>

                <!-- Delete Account -->
                <CardSimple 
                    title="Delete account"
                    note="*No restore possible! Your account will be removed permanently."
                    tooltip="After deleting the account, your personal data and all its linked data is removed from our system."
                >
                    <q-card-section >
                        <FormWrapper
                            buttonText="Delete account"
                            buttonIcon="delete"
                            buttonColor="red"
                            @submit="deleteAccountConfirm()"
                        >
                            <q-input
                                filled
                                type="password"
                                v-model="deletePassword"
                                label="Confirm by password"
                            />
                        </FormWrapper>
                    </q-card-section>
                </CardSimple>
            </div>
        </div>
    </PageWrapper>

</template>

<script>
import { passwordRequirements } from 'src/boot/globals.js';
import PasswordCheck from 'components/PasswordCheck.vue';
import CardUploadImage from 'components/CardUploadImage.vue';
import QrcodeVue from 'qrcode.vue'

export default {
    name: 'UserAccountSettings',
    components: {
        PasswordCheck, CardUploadImage, QrcodeVue
    },
    
    emits: [
        'removeSession'
    ],

    data() {
        return {
            qr_value: process.env.APP_URL,
            password: {
                current: '',
                new: '',
                confirm: ''
            },
            emailPassword: '',
            transferEmail: '',
            deletePassword: ''
        };
    },

    methods: {
        async submitPublicity(isPublic) {
            try {
                this.$toast.load();
                const response = await this.$axios.post('/update-user-publicity', {
                    is_public: isPublic
                });

                this.$toast.success(response.data.message);
            } catch (error) {
                this.$toast.error(error.response ?? error)
            }
        },

        async saveAvatar(src, avatar, deleteAvatar) {
            if(!src && !deleteAvatar ) return;
            try {
                const formData = new FormData;
                if(src) formData.append("avatar", src);
                formData.append("avatar_delete", deleteAvatar ? '1' : '0');
                this.$toast.load();
                const response = await this.$axios.post('/update-user-avatar', formData);
                this.$toast.success(response.data.message);
                this.$user.user.avatar_src = avatar;
            } catch (error) {
                this.$toast.error(error.response ?? error);
            }
        },

        async submitUsername() {
            try {
                if(this.$user.user.name.length === 0) throw ('Please enter name.');
                this.$toast.load();
                const response = await this.$axios.post('/update-user-name', {
                    name: this.$user.user.name
                });
                this.$toast.success(response.data.message);
            } catch (error) {
                this.$toast.error(error.response ?? error);
            }
        },

        async submitPassword(current, newPw, confirmed) {
            try {
                if(!current) throw 'Please enter new password.';
                const passwordCheck = passwordRequirements(newPw, confirmed);
                if(passwordCheck) throw passwordCheck;
                
                // Request
                this.$toast.load();
                const response = await this.$axios.post('update-user-password', {
                    'password_current': current,
                    'password': newPw,
                    'password_confirmation': confirmed
                });
                
                this.$toast.success(response.data.message)
            } catch (error) {
                this.$toast.error(error.response ?? error);
            } finally {
                this.password.current = '';
                this.password.new = '';
                this.password.confirm = '';
            }
        },

        async submitEmail(transferMail, password) {
            try {
                if(!transferMail) throw 'Email field is required.';
                if(!password) throw 'Please cofirm by password.';
                this.$toast.load();
                const response = await this.$axios.post('transfer-user-account', {
                    'email': transferMail,
                    'password': password,
                })
                this.$toast.success(response.data.message);
                this.$emit('removeSession');
            } catch (error) {
                this.$toast.error(error.response ?? error);
            } finally {
                this.emailPassword = '';
            }
        },
        
        async deleteAccount() {
            try {
                if(!this.deletePassword) throw 'Please confirm by your password.'
                this.$toast.load();
                const response = await this.$axios.post('user-delete-account', {
                    'password': this.deletePassword,
                });
                this.$toast.success(response.data.message);
                this.$emit('removeSession');
            } catch (error) {
                this.$toast.error(error.response ?? error);
            } finally {
                this.deletePassword = '';
            }
        },

        deleteAccountConfirm(entity) {
            this.$q.dialog({
                title: 'Confirm delete',
                message: 'Sure you want to delete your account? Your data will be deleted permanently.',
                cancel: true,
            }).onOk(() => {
                this.deleteAccount(entity)
            })
        }
    }
};
</script>
