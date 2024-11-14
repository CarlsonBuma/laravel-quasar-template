<style lang="sass" scoped>
.popup-google-location
    max-width: 420px
.avatar-site-verified-image
    bottom: 2px
    right: 2px
</style>

<template>

    <PageWrapper 
        title="My profile" 
        :rendering="loading" 
        leftDrawer
        drawerTitle="My Avatar"  
    >
        <template #leftDrawer>
            <NavUserAvatar />
        </template>

        <template #actions>
            <q-btn 
                outline      
                color="primary" 
                icon="diversity_1"
                label="Community avatar"
                @click="$router.push('/community/collaborators/' + $user.user.id)" 
            />
        </template>

        <div class="avatar-width">
            <!-- Public -->
            <CardSimple 
                title="Join our community" 
                tooltip="By joining our community, other collaborators are able to find your avatar."
                tooltipIconColor="primary"
            >
                <template #actions>
                    <div class="flex justify-end items-center w-100">
                        <q-toggle class="q-mx-md" v-model="avatar.is_public" dense />
                        <div>
                            <q-btn 
                                @click="submitPublicity(avatar.is_public)" 
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
            
            <CardSimple title="Credentials">
                <SectionSplitFix >
                    <template #left>
                        <q-avatar v-if="$user.user.img_src" square size="170px" >
                            <q-img :src="$user.user.img_src" alt="user-avatar" loading="eager" >
                                <q-icon v-if="avatar.is_public" class="absolute avatar-site-verified-image" size="32px" name="verified" color="green" />
                            </q-img>
                        </q-avatar>
                        <q-avatar v-else color="primary" text-color="white" size="180px" square>U</q-avatar>
                    </template>
                    <template #right>
                        <div class="w-100 text-center text-caption">
                            <span>{{ $user.user.name ?? 'Collaborator'}}</span><br>
                        </div>
                    </template>
                </SectionSplitFix>
                
                <q-separator />
                <q-card-section>
                    <div class="flex">
                        <span class="text-caption"><b>Age:</b>&nbsp;{{ avatar.age ? avatar.age : '-' }}</span>
                        <q-space />
                        <q-btn flat dense icon="edit" size="sm">
                            <q-popup-edit class="w-popup" modelValue="2">
                                <q-input label="Age" type="number" v-model="avatar.age" dense autofocus />
                                <div class="row w-100 q-mt-sm">
                                    <q-checkbox v-model="avatar.age_is_public" label="Is public" />
                                    <q-space/>
                                    <q-btn flat dense icon="check_circle" color="positive" @click="submitdateOfBirth(avatar.age, avatar.age_is_public)" />
                                    <q-btn flat dense icon="cancel" color="negative" @click="avatar.age = ''" />
                                </div>
                            </q-popup-edit>
                        </q-btn>
                    </div>

                    <!-- Details Contact -->
                    <div class="flex">
                        <span class="text-caption _text-break"><b>Contact details:</b><br>{{ avatar.contact ? avatar.contact : '-' }}</span>
                        <q-space />
                        <div>
                            <q-btn flat dense icon="edit" size="sm">
                                <q-popup-edit class="w-popup" modelValue="1">
                                    <q-input 
                                        label="Contact" 
                                        v-model="avatar.contact" 
                                        type="textarea" 
                                        maxlength="99"  
                                        autogrow 
                                        dense 
                                        autofocus 
                                    />
                                    <div class="row q-mt-sm w-100">
                                        <q-checkbox v-model="avatar.contact_is_public" label="Is public" />
                                        <q-space />
                                        <q-btn flat dense icon="check_circle" color="positive" @click="submitContact(avatar.contact, avatar.contact_is_public)" />
                                        <q-btn flat dense icon="cancel" color="negative" @click="avatar.contact = ''" />
                                    </div>
                                </q-popup-edit>
                            </q-btn>
                        </div>
                    </div>
                </q-card-section>

                <!-- Geolocation -->
                <q-separator />
                <q-card-section>
                    <div class="flex justify-end w-100">
                        <q-btn flat dense icon="edit" size="sm" >
                            <q-popup-edit modelValue="3">
                                <GoogleLocation 
                                    class="popup-google-location"
                                    :location="avatar.location"
                                    @fetched="(place_id, lng, lat, address, area, area_short, country, country_short, zip_code) => {
                                        avatar.location.place_id = place_id
                                        avatar.location.lng = lng
                                        avatar.location.lat = lat
                                        avatar.location.address = address
                                        avatar.location.area = area
                                        avatar.location.area_short = area_short
                                        avatar.location.country = country
                                        avatar.location.country_short = country_short
                                        avatar.location.zip_code = zip_code
                                    }"
                                />
                                <q-separator class="q-mt-sm" />
                                <div class="row q-mt-sm">
                                    <q-checkbox v-model="avatar.location_is_public" label="Is public" />
                                    <q-space />
                                    <q-btn flat dense icon="check_circle" color="positive" @click="submitLocation(avatar.location, avatar.location_is_public)" />
                                    <q-btn flat dense icon="cancel" color="negative" @click="avatar.location = {}" />
                                </div>
                            </q-popup-edit>
                        </q-btn>
                    </div>
                    <div class="flex">
                        <div class="w-100 flex items-center justify-center w-100 q-mb-sm">
                            <a :href="avatar.location && avatar.location.place_id 
                                ? googleMapsURL + avatar.location.lat + ',' + avatar.location.lng + '&query_place_id=' + avatar.location.place_id
                                : void(0)" target="_blank"
                            >
                                <q-icon name="pin_drop" color="primary" size="24px" class="q-pa-sm"/>
                            </a>
                            <span class="text-center text-caption">{{ avatar.location && avatar.location.address 
                                ? avatar.location.address 
                                : avatar.location && avatar.location.place_id
                                    ? avatar.location.place_id
                                    : 'No location set.' }}
                            </span>
                        </div>
                    </div>
                </q-card-section>
            </CardSimple>
        </div>

        <div class="w-card-lg">
            <CardSimple title="About me">
                <q-card-section >
                    <FormWrapper
                        buttonText="Update about"
                        buttonIcon="update"
                        @submit="submitAbout(avatar.about)"
                    >
                        <q-input
                            v-model="avatar.about"
                            type="textarea"
                            placeholder="Tell us something about you..."
                            counter 
                            maxlength="499" 
                        />
                    </FormWrapper>
                </q-card-section>
            </CardSimple>

            <CardSimple title="Resident" >
                <q-card-section>
                    <FormWrapper
                        buttonText="Update resident"
                        buttonIcon="update"
                        @submit="submitCountry(avatar.country)"
                    >
                        <q-select 
                            v-model="avatar.country" 
                            :options="countryOptions" 
                            option-label="name"
                        />
                    </FormWrapper>
                </q-card-section>
            </CardSimple>

            <CardSimple title="Fluent language skills" >
                <q-card-section>
                    <FormWrapper
                        buttonText="Update language"
                        buttonIcon="update"
                        @submit="submitLanguages(avatar.language)"
                    >
                        <q-select 
                            v-model="avatar.language" 
                            :options="languageOptionsFilter" 
                            option-label="name"
                            
                            multiple
                            use-chips
                            use-input
                            input-debounce="0"
                            new-value-mode="add-unique"
                            @filter="(val, update) => filterLanaguage(val, update)"
                        />
                    </FormWrapper>
                </q-card-section>
            </CardSimple>
        </div>
    </PageWrapper>

</template>

<script>
import { ref } from 'vue';
import { redirectLinkGoolgeMaps } from 'src/boot/redirects.js';
import NavUserAvatar from 'src/components/navigation/NavUserAvatar.vue';
import FormWrapper from 'components/FormWrapper.vue';
import SectionSplitFix from 'components/SectionSplitFix.vue';
import GoogleLocation from 'src/components/GoogleLocation.vue';

export default {
    name: 'UserProfile',
    components: {
          NavUserAvatar, FormWrapper, GoogleLocation, SectionSplitFix
    },

    setup() {
        return {
            loading: ref(true),
            googleMapsURL: redirectLinkGoolgeMaps,
            languageOptionsFilter: ref([]),
        };
    },

    data() {
        return {
            avatar: {},
            languageOptions: [],
            countryOptions: [],
        }
    },

    mounted() {
        this.loadAttributes()
    },

    methods: {

        async loadAttributes() {
            try {
                this.loading = true;
                const language = await this.$axios.get('/get-app-languages');
                this.languageOptions = language.data.language;
                this.languageOptionsFilter = language.data.language;

                const countries = await this.$axios.get('/get-app-countries');
                this.countryOptions = countries.data.countries;

                const userAvatar = await this.$axios.get('/load-user-avatar');
                this.avatar = userAvatar.data.avatar;
            } catch (error) {
                this.$toast.error(error.response ? error.response : error)
            } finally {
                this.loading = false;
            }
        },

        async submitPublicity(avatar_isPublic) {
            try {
                this.$toast.load();
                const response = await this.$axios.post('/update-user-avatar-publicity', {
                    is_public: avatar_isPublic
                });

                this.$toast.success(response.data.message);
            } catch (error) {
                this.$toast.error(error.response ? error.response : error)
            } finally {
                this.$toast.done();
            }
        },

        async submitContact(contact, contact_is_public) {
            try {
                this.$toast.load();
                const response = await this.$axios.post('/update-user-avatar-contact', {
                    contact: contact,
                    is_public: contact_is_public
                });
                this.$toast.success(response.data.message);
            } catch (error) {
                this.$toast.error(error.response ? error.response : error)
            } finally {
                this.$toast.done();
            }
        },

        async submitdateOfBirth(age, age_isPublic) {
            try {
                this.$toast.load();
                const response = await this.$axios.post('/update-user-avatar-birth', {
                    age: age,
                    is_public: age_isPublic
                });
                this.$toast.success(response.data.message);
            } catch (error) {
                this.$toast.error(error.response ? error.response : error)
            } finally {
                this.$toast.done();
            }
        },

        async submitLocation(geolocation, geolocation_isPublic) {
            try {
                this.$toast.load();
                const response = await this.$axios.post('/update-user-avatar-location', {
                    is_public: geolocation_isPublic,
                    place_id: geolocation.place_id,
                    lng: geolocation.lng,
                    lat: geolocation.lat,
                    address: geolocation.address,
                    country: geolocation.country,
                    country_short: geolocation.country_short,
                    area: geolocation.area,
                    area_short: geolocation.area_short,
                    zip_code: geolocation.zip_code
                });
                this.$toast.success(response.data.message);
            } catch (error) {
                this.$toast.error(error.response ? error.response : error)
            } finally {
                this.$toast.done();
            }
        },

        async submitAbout(about) {
            try {
                this.$toast.load();
                const response = await this.$axios.post('/update-user-avatar-about', {
                    about: about
                });
                this.$toast.success(response.data.message);
            } catch (error) {
                this.$toast.error(error.response ? error.response : error)
            } finally {
                this.$toast.done();
            }
        },

        async submitCountry(country) {
            try {
                this.$toast.load();
                const response = await this.$axios.post('/update-user-avatar-country', {
                    country_id: country?.id
                });
                this.$toast.success(response.data.message);
            } catch (error) {
                this.$toast.error(error.response ? error.response : error)
            } finally {
                this.$toast.done();
            }
        },

        async submitLanguages(languages) {
            try {
                this.$toast.load();
                const response = await this.$axios.post('/update-user-avatar-languages', {
                    languages: languages
                });
                this.$toast.success(response.data.message);
            } catch (error) {
                this.$toast.error(error.response ? error.response : error)
            } finally {
                this.$toast.done();
            }
        },
        
        filterLanaguage(inputValue, update) {
            update(() => {
                if(!inputValue) {
                    this.languageOptionsFilter = this.languageOptions;
                } else {
                    this.languageOptionsFilter = [];
                    this.languageOptions.filter(value => {
                        if(value.name.toLowerCase().includes(inputValue.toLowerCase())) this.languageOptionsFilter.push(value)
                    });
                }
            })
        },
    },
};
</script>
