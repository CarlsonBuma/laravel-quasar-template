<template>

    <PageWrapper :rendering="loading" >
        <template #navigation>
            <NavEntity />
        </template>

        <div class="avatar-width">
            
            <!-- Make public -->
            <CardSimple 
                title="Join our community" 
                tooltip="Publish your entity."
                tooltipIconColor="primary"
            >
                <template #actions>
                    <div class="flex justify-end w-100">
                        <q-toggle 
                            v-model="entity.is_public"
                            class="q-ml-md q-mr-sm" 
                            dense 
                        />
                        <q-btn 
                            @click="updatePublicity(entity.is_public)" 
                            :disabled="!entity.name || !entity.avatar"
                            outline 
                            rounded
                            size="sm"
                            color="primary" 
                            label="Update" 
                        >
                            <q-tooltip v-if="!entity.name || !entity.avatar">
                                Entity name &amp; avatar are required.
                            </q-tooltip>
                        </q-btn>
                    </div>
                </template>
            </CardSimple>

            <!-- Avatar -->
            <CardUploadImage 
                :userAvatar="entity.avatar" 
                :name="entity.name" 
                :slogan="entity.slogan"
                allowUpdate
                @update="(src, avatar, deleteAvatar) => udpateAvatar(src, deleteAvatar)"
                @upload="(src, avatar) => {
                    entity.avatar = avatar;
                }"
            />

            <!-- Name -->
            <CardSimple title="Entity name">           
                <q-card-section>
                    <FormWrapper
                        buttonText="Update name"
                        buttonIcon="update"
                        @submit="updateName(entity.name)"
                    >
                        <q-input v-model="entity.name" label="Entity name"/>
                    </FormWrapper>
                </q-card-section>
            </CardSimple>
        </div>

        <div class="w-card-lg">

            <!-- About -->
            <CardSimple title="About us">            
                <q-card-section>
                    <FormWrapper
                        buttonText="Update about"
                        buttonIcon="update"
                        @submit="updateAbout(entity.about)"
                    >
                        <q-input
                            class="q-mt-md"
                            label="About us"
                            v-model="entity.about"
                            maxlength="999"
                            type="textarea"
                            placeholder="Tell us about your business..."
                            counter
                        />
                    </FormWrapper>
                </q-card-section>
            </CardSimple>

            <!-- Impressum -->
            <CardSimple title="Impressum">        
                <q-card-section>
                    <FormWrapper
                        buttonText="Update Impressum"
                        buttonIcon="update"
                        @submit="updateImpressum(entity.website, entity.contact)"
                    >
                        <q-input 
                            v-model="entity.website" 
                            label="Website" 
                            placeholder="www.website.io"
                        />
                        <q-input
                            v-model="entity.contact"
                            maxlength="199"
                            label="Contact details"
                            type="textarea"
                            counter
                            autogrow
                        />
                    </FormWrapper>
                </q-card-section>
            </CardSimple>
        </div>

        <!-- SEO -->
        <div class="avatar-width">
            <!-- Geolocation -->
            <CardSimple title="Location">
                <q-card-section>
                    <FormWrapper
                        buttonText="Update Location"
                        buttonIcon="update"
                        @submit="updateLocation(entity.location)"
                    >
                        <GoogleLocation 
                            :location="entity.location"
                            @reset="() => entity.location = {}"
                            @fetched="(place_id, lng, lat, address, area, area_short, country, country_short, zip_code) => {
                                entity.location.place_id = place_id
                                entity.location.lng = lng
                                entity.location.lat = lat
                                entity.location.address = address
                                entity.location.area = area
                                entity.location.area_short = area_short
                                entity.location.country = country
                                entity.location.country_short = country_short
                                entity.location.zip_code = zip_code
                            }"
                        />
                    </FormWrapper>
                </q-card-section>
            </CardSimple>

            <!-- Bulletpoints -->
            <CardSimple title="Bulletpoints">            
                <q-card-section>
                    <FormWrapper
                        buttonText="Update bulletpoints"
                        buttonIcon="update"
                        @submit="updateBulletpoints(entity.tags)"
                    >
                        <q-select
                            label="Enter bulletpoints..."
                            v-model="entity.tags"
                            use-input
                            use-chips
                            multiple
                            max-values="9"
                            counter
                            hide-dropdown-icon
                            input-debounce="0"
                            new-value-mode="add-unique"
                        />
                    </FormWrapper>
                </q-card-section>
            </CardSimple>
        </div>
    </PageWrapper>

</template>

<script>
import { ref } from 'vue'
import { regRules } from 'src/boot/globals';
import NavEntity from 'src/components/navigation/NavEntity.vue';
import FormWrapper from 'src/components/global/FormWrapper.vue';
import GoogleLocation from 'src/components/GoogleLocation.vue';
import CardUploadImage from 'components/CardUploadImage.vue';

export default {
    name: 'EntityProfile',
    components: {
        NavEntity, FormWrapper, GoogleLocation, CardUploadImage
    },

    setup() {
        return {
            loading: ref(true),
            regRules
        };
    },

    data() {
        return {
            entity: {}
        }
    },

    mounted() {
        this.loadAttributes()
    },

    methods: {
        async loadAttributes() {
            try {
                this.loading = true;
                const entityResponse = await this.$axios.get('/load-entity-profile');
                this.entity = entityResponse.data.entity;
            } catch (error) {
                this.$toast.error(error.response ? error.response : error)
            } finally {
                this.loading = false;
            }
        },

        async updatePublicity(entity_isPublic) {
            try {
                this.$toast.load();
                const response = await this.$axios.post('/update-entity-public-access', {
                    is_public: entity_isPublic
                });
                this.$toast.success(response.data.message);
            } catch (error) {
                this.$toast.error(error.response ?? error)
            } finally {
                this.$toast.done();
            }
        },

        async udpateAvatar(src, deleteAvatar) {
            try {
                if(!src && !deleteAvatar) return;
                const formData = new FormData;
                if(src) formData.append("src", src);
                formData.append("avatar_delete", deleteAvatar ? '1' : '0');
                this.$toast.load();
                const response = await this.$axios.post('/update-entity-avatar', formData);
                this.$toast.success(response.data.message);
            } catch (error) {
                this.$toast.error(error.response ?? error);
            }
        },

        async updateName(name) {
            try {
                this.$toast.load();
                const response = await this.$axios.post('/update-entity-credits', {
                    name: name,
                });
                this.$toast.success(response.data.message);
            } catch (error) {
                this.$toast.error(error.response ?? error);
            }
        },

        async updateAbout(about) {
            try {
                this.$toast.load();
                const response = await this.$axios.post('/update-entity-about', {
                    about: about,
                });
                this.$toast.success(response.data.message);
            } catch (error) {
                this.$toast.error(error.response ?? error)
            }
        },

        async updateLocation(location) {
            try {
                if(!location.place_id) throw 'Please enter address.';
                this.$toast.load();
                const response = await this.$axios.post('/update-entity-location', {
                    place_id: location.place_id,
                    lng: location.lng,
                    lat: location.lat,
                    address: location.address,
                    country: location.country,
                    country_short: location.country_short,
                    area: location.area,
                    area_short: location.area_short,
                    zip_code: location.zip_code
                });
                this.$toast.success(response.data.message);
            } catch (error) {
                this.$toast.error(error.response ?? error)
            }
        },

        async updateBulletpoints(tags) {
            try {
                this.$toast.load();
                const response = await this.$axios.post('/update-entity-bulletpoints', {
                    tags: tags,
                });
                this.$toast.success(response.data.message);
            } catch (error) {
                this.$toast.error(error.response ?? error)
            }
        },

        async updateImpressum(website, contact) {
            try {
                if(!this.regRules.sanitizeLink.test(website)) throw 'Invalid link.'
                this.$toast.load();
                const response = await this.$axios.post('/update-entity-impressum', {
                    website: website,
                    contact: contact,
                });
                this.$toast.success(response.data.message);
            } catch (error) {
                this.$toast.error(error.response ?? error)
            }
        },
    },
};
</script>
