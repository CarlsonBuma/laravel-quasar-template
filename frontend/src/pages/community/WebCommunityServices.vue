
<style lang="sass" scoped>
#map-div
    height: 500px
#map-circle-geolocation
    position: absolute
    border-radius: 50%
    border: 2px solid $primary
    background-color: rgba($primary, 0.2)
#map-cirlcle-geolocation-dot
    position: absolute
    top: 50%
    left: 50%
    transform: translate(-50%, -50%)
    width: 7px
    height: 7px
    border-radius: 50%
    border: 1px solid $primary
    background-color: rgba($primary, 0.7)
.result-width
    width: 100%
    max-width: 360px
.popup-content
    max-width: 1240px
.google-width
    width: 360px
.select-search-box
    max-width: 720px
</style>


<template>

    <PageWrapper 
        :rendering="loading"
        @refresh="searchEntities()"
        allowRefresh
        showExpansion
        leftDrawer
        drawerTitle="Search Community"
    >
        <template #leftDrawer>

            <div class="row q-py-md">
                <q-input outlined class="w-100" label="Search community..." v-model="searchString"/>
            </div>

            <q-list>
                <q-item clickable v-close-popup>
                    <q-item-section>Preferences</q-item-section>
                    <q-item-section side>
                        <q-icon name="keyboard_arrow_right" />
                    </q-item-section>

                    <q-menu anchor="top end" self="top start">
                        <q-item>
                            sdfsd
                        </q-item>
                    </q-menu>
                </q-item>
            </q-list>
        </template>

        <template #expansion-item>
            <div class="row w-100 flex justify-end">
                <!-- Search -->
                <div class="col-grow q-pa-sm">
                    <div class="row">
                        <!-- Geolocation -->
                        <div class="col-auto flex q-ml-sm-sm q-pb-sm">
                            <q-btn color="primary" label="Location" icon="pin_drop" outline>
                                <GoogleMaps
                                    :allowLocation="allowGeolocation" 
                                    @setLocation="(value) => allowGeolocation = value"
                                    @updateLocation="(radius, latitude, longitude) => setMapsLocation(radius, latitude, longitude)"
                                />
                            </q-btn>
                        </div>
                    </div>                    
                </div>

                <!-- Search Event -->
                <div class="col-auto flex items-center q-px-sm q-pb-sm">
                    <q-btn 
                        class="q-ml-sm"
                        outline
                        icon="history"
                        color="orange"
                        @click="resetSearch()"
                    />
                    <q-btn 
                        class="q-ml-sm"
                        outline
                        icon="refresh"
                        color="primary"
                        @click="searchEntities()"
                    />
                </div>
            </div>
        </template>

        <!-- Entity Results -->  
        <div class="row justify-center w-100">
            <div class="col-12 flex justify-center">
                <NoData v-if="!entities[selectedEntityIndex]" text="No matches found." />
                <div v-else class="row w-100 flex justify-center">
                    <EntitySite 
                        class="popup-content" 
                        :entity="entities[selectedEntityIndex]"
                        @next="() => {
                            selectedEntityIndex === entities.length - 1 ? null : selectedEntityIndex++
                            postVisits()
                        }"
                        @undo="selectedEntityIndex === 0 ? selectedEntityIndex : selectedEntityIndex--" 
                        @visit="$router.push(redirectToEntitySite + entities[selectedEntityIndex].entity_id)"
                        @connect="connectUser(entities[selectedEntityIndex].entity_id)"
                        navigation
                        allowActions
                    >
                        <template #navigation>
                            <span class="text-primary">
                                {{ selectedEntityIndex + 1 }} / {{ entities.length }}
                            </span>
                        </template>

                        <template #navActions>
                            <q-btn 
                                @click="connectUser(entities[selectedEntityIndex].entity_id)" 
                                flat 
                                round 
                                color="purple" 
                                :disable="!$user.user.id" 
                                icon="diversity_1" 
                            />
                        </template>
                    </EntitySite>
                </div>
            </div>
        </div>
    </PageWrapper>

</template>

<script>
import { ref } from 'vue';
import GoogleMaps from 'components/GoogleMaps.vue';
import EntitySite from 'pages/community/components/EntitySite.vue';

export default {
    name: 'WebCommunityServices',
    components: {
        EntitySite, GoogleMaps
    },

    props: {
        keywords: Array      // URL Query
    },

    setup() {

        // Setup
        const loading = ref(true);  
        const redirectToEntitySite = 'community/entities/'
        const entitiesVisited = ref([]);
        const allowGeolocation = ref(false);
        const searchString = ref('');
        
        return {
            loading,
            redirectToEntitySite,
            entitiesVisited,
            allowGeolocation,
            searchString,
        }
    },

    data() {
        return {
            selectedEntity: null,
            selectedEntityIndex: 0,
        }
    },

    mounted() {
        this.searchEntities();
    },

    methods: {

        async searchEntities() {
            this.selectedEntityIndex = 0;
            // Get Serach Location
            const allowGeolocation = this.allowGeolocation;
            const mapRadius = this.mapSearchRadius;
            const mapLatitude = this.latitude;
            const mapLongitude = this.longitude;

            try {
                this.loading = true;
                const response = await this.$axios.get('/search-community-entities', { params: { 
                        geolocation_set: allowGeolocation ? '1' : '0',
                        geolocation_lat: mapLatitude,
                        geolocation_lng: mapLongitude,
                        geolocation_radius: mapRadius
                    } 
                });
                this.entities = response.data.entity_collection
                this.selectedEntity = this.entities[this.selectedEntityIndex] 
                    ? this.entities[this.selectedEntityIndex] 
                    : this.entities;
            } catch (error) {
                const errorMessage = error.response ? error.response.data.message : error;
                console.log('CommunitySearch', errorMessage)
                this.$toast.error(errorMessage)
            } finally {
                this.loading = false;
            }
        },

        setMapsLocation(radius, latitude, longitude) {
            this.mapSearchRadius = radius;
            this.latitude = latitude;
            this.longitude = longitude  
        },

        resetSearch() {
            this.allowGeolocation = false;
        },

        async connectUser(entity_id) {
            try {
                this.$toast.load();
                const response = await this.$axios.post('/add-user-entity-shortcut', {
                    entity_id: entity_id
                });
                this.$toast.success(response.data.message)
            } catch (error) {
                const errorMessage = error.response ? error.response.data.message : error;
                console.log('UserCollaboration', errorMessage)
                this.$toast.error(errorMessage)
            }
        },
    }
};
</script>
