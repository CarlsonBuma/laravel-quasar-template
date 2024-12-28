<template>

    <div class="container">
        <q-input 
            class="w-100" 
            v-model="google_maps_coordinates" 
            label="Enter address, plus code or coordinates..." 
        />
        <div class="flex w-100 justify-center items-center q-py-sm">
            <q-btn 
                href='https://www.google.ch/maps' 
                target="_blank" 
                class="q-ma-sm" 
                outline 
                color="orange" 
                icon="open_in_new" 
                label="Google Maps" 
            />
            <q-btn 
                @click="requestGoogleAPI(google_maps_coordinates)"
                :disable="!google_maps_coordinates" 
                :loading="fetching" 
                class="q-ma-sm" 
                outline 
                color="primary" 
                icon="settings_input_antenna" 
                label="Request Location" 
            />
        </div>

        <!-- Response -->
        <q-separator class="q-mb-sm w-100" />
        <div class="w-100">
            <div class="w-100  _overflow-elipsis">
                <span class="text-caption"><b>Place - ID:</b> {{ location.place_id ? location.place_id : 'No location available.' }}</span>
            </div>
            <span class="text-caption"><b>Coordinates:</b> {{ location.lat ? location.lat : '-' }}, {{ location.lng ? location.lng : '-' }}<br></span>
            <span class="text-caption"><b>Address:</b> {{ location.address ? location.address : '-' }}</span><br>
            <span class="text-caption"><b>Location:</b> {{ location.area ? location.area : '-' }}, {{ location.country ? location.country : '-' }}</span>
        </div>
    </div>

</template>

<script>
import { ref } from 'vue';

export default {
    name: 'GoogleLocation',
    components: {
        
    },

    props: {
        location: Object,
        disable: Boolean,
    },

    emits: [
        'update',
        'fetched'
    ],

    setup(props) {
        return {
            requestLimits: 5,
            currentRequests: ref(0),
            dialogEvent: ref(false),
            fetching: ref(false),
            googleMapsAPI: 'https://maps.googleapis.com/maps/api/geocode/json?address=',
            google_maps_coordinates: ref(props.location.address ?? '')
        };
    },

    data() {
        return {
            place_id: '-',
            lat: 0,
            lng: 0,
            address: 'No location assigned.',
            area: '-',
            area_short: '-',
            country: '-',
            country_short: '-',
            zip_code: '-',
        }
    },

    methods: {

        async requestGoogleAPI(google_maps_address) {
            try {
                if(this.currentRequests >= this.requestLimits) throw 'Limit of ' + this.requestLimits + ' requests.'
                if(!process.env.APP_GOOGLE_API_KEY) throw 'To use geolocation, please add a Google_API_KEY, by creating an account for Developers.'
                if(!google_maps_address) return;
                if(this.fetching) return;

                // https://maps.googleapis.com/maps/api/geocode/json?address="some address"&key=YOUR_API_KEY&attribute=
                this.currentRequests++;
                const api_address = this.googleMapsAPI 
                    + encodeURIComponent(google_maps_address) 
                    + '&key=' + process.env.APP_GOOGLE_API_KEY
                    + '&language=en';

                this.fetching = true;
                const response = await fetch(api_address);
                const jsonData = await response.json();
                const googleData = jsonData.results[0];

                // Set Data
                this.place_id = googleData.place_id;
                this.lng = googleData.geometry.location.lng.toFixed(6);
                this.lat = googleData.geometry.location.lat.toFixed(6);
                this.address = googleData.formatted_address;
                googleData.address_components.forEach(component => {
                    component.types.forEach(type => {
                        if (type === 'administrative_area_level_1') {
                            this.area = component.long_name
                            this.area_short = component.short_name
                        }

                        else if (type === 'country') {
                            this.country = component.long_name;
                            this.country_short = component.short_name;
                        }

                        else if (type === 'postal_code') this.zip_code = component.long_name;
                    })
                });
                
                this.$emit('fetched', this.place_id, this.lng, this.lat, this.address, this.area, this.area_short, this.country, this.country_short, this.zip_code)
                this.$toast.success('Location set.');
            } catch ( error ) {
                this.$toast.error(error)
            } finally {
                this.fetching = false;
            }
        },
    }
}
</script>
