<template>

    <div class="row flex justify-center w-100">
        <div class="w-card">
            <CardSimple shadow>
                <q-img 
                    v-if="entity.avatar" 
                    :src="entity.avatar" 
                    :ratio="1" 
                    alt="entity-avatar"
                    img-class="banner-image"
                    fit="fill" 
                    loading="eager"
                >
                    <div v-if="entity.name" class="absolute-bottom">
                        <div class="flex justify-center text-h6">{{ entity.name }}</div>
                        <div class="flex justify-center text-caption">{{ entity.slogan }}</div>
                    </div>
                </q-img>
                
                <div class="w-100 flex justify-center q-pa-lg" v-else>
                    <q-avatar color="primary" text-color="white" size="140px" square>U</q-avatar>
                </div>

                <!-- Shortcuts -->
                <q-card-actions v-if="allowActions" align="around">
                    <q-btn 
                        :disable="!$user.access.user" 
                        flat 
                        round 
                        color="purple" 
                        icon="bookmark_added" 
                        @click="$emit('connect')" 
                    />
                </q-card-actions>
            </CardSimple>
        </div>

        <div class="w-card">
            <CardSimple title="About">
                <q-card-section>
                    <p class="_text-break text-caption">{{ entity.about ? entity.about : 'Nothing to read.' }}</p>
                </q-card-section>

                <!-- Location -->
                <q-separator v-if="entity.location && entity.location.lng && entity.location.lat"/>
                <q-card-section v-if="entity.location && entity.location.lng && entity.location.lat" >
                    <div class="w-100 text-center">
                        <q-btn 
                            v-if="entity.location && entity.location.place_id"
                            :href="googleMapsURL + entity.location.lat + ',' + entity.location.lng + '&query_place_id=' + entity.location.place_id" 
                            size="12px"
                            target="_blank"
                            flat 
                            rounded
                            color="primary" 
                            icon="pin_drop"
                            :label="entity.location.area + ', ' + entity.location.country" 
                        />
                    </div>
                </q-card-section>
            </CardSimple>

            <!-- Impressum -->
            <CardSimple title="Impressum" class="text-center">
                <q-card-section class="_text-break text-caption" >
                    <span>{{ entity.contact ? entity.contact : 'No information available.' }}</span>
                </q-card-section>

                <q-separator v-if="entity.website" />
                <q-card-section v-if="entity.website">
                    <div class="w-100 flex justify-center">
                        <q-btn
                            :href="'https://' + entity.website"
                            target="_blank"
                            size="12px"
                            :label="entity.website"
                            color="primary"
                            icon="launch"
                            flat 
                            rounded
                        />
                    </div>
                </q-card-section>
            </CardSimple>
        </div>
    </div>
    
</template>

<script>
export default {
    name: 'EntitySite',
    components: {
        // 
    },

    props: {
        entity: Object,
        allowActions: Boolean,
        navigation: Boolean,
    },

    emits: [
        'connect',
    ],

    setup() {
        const googleMapsURL = 'https://www.google.com/maps/search/?api=1&query='
        return {
            googleMapsURL
        }
    },
};
</script>
