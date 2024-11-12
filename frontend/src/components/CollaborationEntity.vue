<template>

    <NoData v-if="!entity?.id" text="No information available." />
    <q-card flat class="w-100 q-my-xs q-mx-sm-xs" v-else>

        <!-- Meta -->
        <q-card-section class="row">
            <div class="col-12 col-sm q-py-xs">
                <span class="text-overline" v-if="entity.slogan">{{ entity.slogan }}</span>
                <p class="text-h5">{{ entity.name }}</p>
                <span class="text-caption text-grey">
                    {{ entity.about }}
                </span>
            </div>
            <div class="col-12 col-sm-auto q-py-xs">
                <div class="flex justify-center w-100">
                    <q-img 
                        v-if="entity.avatar" 
                        width="160px"
                        :src="entity.avatar" 
                        :ratio="1" 
                        alt="entity-avatar"
                        img-class="banner-image"
                        fit="fill" 
                        loading="eager"
                    />
                </div>
                <div class="flex justify-center q-gutter-sm">
                    <q-btn 
                        outline
                        round
                        size="sm" 
                        color="purple" 
                        icon="bookmark_added" 
                        :disable="!$user.access.user" 
                        @click="$emit('connect')" 
                    >
                        <q-tooltip v-if="!$user.access.user">Login</q-tooltip>
                    </q-btn>
                </div>
            </div>
        </q-card-section>

        <!-- Impressum & Redirects -->
        <q-separator/>
        <q-card-section class="row w-100">
            <div class="col-12 col-sm _text-break text-caption q-py-xs">
                <span><b>Impressum:</b><br></span>
                <span >{{ entity.contact ? entity.contact : 'No information available.' }}</span>
            </div>
            <div class="col-auto q-py-xs">
                <div>
                    <q-btn 
                        target="_blank"
                        flat 
                        color="primary" 
                        icon="pin_drop"
                        v-if="entity.location && entity.location.place_id"
                        :label="entity.location.area + ', ' + entity.location.country" 
                        :href="googleMapsURL + entity.location.lat + ',' + entity.location.lng + '&query_place_id=' + entity.location.place_id" 
                    />
                </div>
                <div>
                    <q-btn
                        v-if="entity.website"
                        flat
                        color="primary"
                        icon="domain" 
                        target="_blank"
                        :label="entity.website"
                        :href="'https://' + entity.website"
                    />
                </div>
                <div>
                    <q-btn 
                        flat
                        icon="contacts"
                        color="primary"   
                        label="Go profile"
                        @click="$router.push('/community/entities/' + entity.id)"
                        target="_blank"
                    />
                </div>
            </div>
        </q-card-section>
    </q-card>
    
</template>

<script>
export default {
    name: 'CollaborationEntity',
    components: {
        //
    },

    props: {
        entity: Object,
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
