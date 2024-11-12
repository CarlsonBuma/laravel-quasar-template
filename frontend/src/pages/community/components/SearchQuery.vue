<template>
    
    <div class="row q-px-sm">
        <div class="col-12">
            <q-select 
                v-model="selectedAward"  
                :loading="loading"
                :disable="loading"
                :options="awards" 
                label="Collaboration Type"
                clearable
            />
        </div>

        <div class="col-12 q-mb-sm">
            <q-select 
                label="Enter Keywords (Title, Skill, etc.)"
                v-model="searchQuery"
                clearable
                use-input
                use-chips
                multiple
                hide-dropdown-icon
                input-debounce="0"
                new-value-mode="add-unique"
                @clear="searchQuery = []"
            />
        </div>

        <div class="col-12 flex justify-end q-pa-sm">
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
                @click="searchRequest(searchQuery, selectedAward)"
            />
        </div>
    </div>

</template>

<script>
import { ref } from 'vue';
import { useRouter, useRoute } from 'vue-router'

export default {
    name: 'CollaborationAvatar',
    components: {
        // 
    },

    props: {
        loading: Boolean,
        awards: Array,
    },

    emits: [
        'search',
        'reset'
    ],

    setup() {
        
        // Setup URL Search Query
        const router = useRouter();
        const route = useRoute();
        const selectedAward = ref(null);
        const awardID = route.query?.award_id
            ? parseInt(route.query.award_id)
            : null;
        const searchQuery = ref(route.query?.search_query 
            ? JSON.parse(route.query.search_query) 
            : []
        )

        // Search
        const searchRequest = (searchQuery, selectedAward) => {
            router.push({
                path: route.path,
                query: {
                    search_query: JSON.stringify(searchQuery),
                    award_id: selectedAward?.id || ''
                }
            });
        }

        const resetSearch = () => {
            searchQuery.value = [];
            selectedAward.value = null;
            router.push({
                path: route.path,
            });
        }

        return {
            awardID,
            searchQuery,
            selectedAward,
            searchRequest,
            resetSearch,
        }
    },

    mounted() {
        // Start Search in Parent
        this.$emit('search', this.searchQuery, this.awardID);
    },

    created() {
        
        // Set selected Award
        this.$watch(() => this.awards,
        (awards) => {
            awards.forEach(award => {
                if(award.id === this.awardID) {
                    this.selectedAward = award;
                }
            })
        });

        // Check URL Changes
        this.$watch(() => this.$route.query,
        (query) => {
            this.awardID = query.award_id
                ? parseInt(query.award_id)
                : null;
            this.searchQuery = query.search_query
                ? JSON.parse(query.search_query) 
                : []

            this.selectedAward = null;
            this.awards.forEach(award => {
                if(award.id === this.awardID) {
                    this.selectedAward = award;
                }
            })

            console.log(this.searchQuery, this.awardID)
            this.$emit('search', this.searchQuery, this.awardID)
        });
    }
};
</script>
