<template>

     <PageWrapper noMargin>
        <SectionDesignDefault>
            <SectionSplit>
                <template #left>
                    <PageTitle 
                        class="text-start w-title"
                        header="Start collaborating within our community."
                        title="Create something amazing! Find collaborators according your needs."
                        subtitle="
                            Establish your services, connect to new providers or acquire unique collaborators. 
                            Whatever you need to to achieve your goals!
                        "
                    >
                        Some Content
                    </PageTitle>
                </template>
                <template #right>
                    <!-- Content -->
                    <CardSimple title="Our community" class="w-card-sm text-center">
                        <q-card-section>
                            <q-icon name="diversity_1" size="72px" color="primary" />
                            <p class="q-mt-sm">Collabris provides a network of unique collaborators. Start establish your amazing network.</p>
                        </q-card-section>
                    </CardSimple>
                    <CardSimple title="Our Services" class="w-card-sm text-center">
                        <q-card-section>
                            <q-icon name="engineering" size="72px" color="primary" />
                            <p class="q-mt-sm">Our community provies you knowhow and skills, according your needs. Your first step to success.</p>
                        </q-card-section>
                    </CardSimple>
                    <CardSimple title="Your Gigs &amp; Support" class="w-card-sm text-center">
                        <q-card-section>
                            <q-icon name="loyalty" size="72px" color="primary" />
                            <p class="q-mt-sm">Feel free to ask. Post your own gigs or request support. We are here to help.</p>
                        </q-card-section>
                    </CardSimple>
                </template>
            </SectionSplit>
        </SectionDesignDefault>

        <!-- Our Entities -->
        <SectionDesignColored>
            <PageTitle 
                class="text-center text-white w-title"
                title="Network with our unique colalborators."
                header="Start your journey by collaborating within our community!"
                separator="white"
            >
                <q-btn 
                    class="q-mt-lg"
                    @click="$router.push('/pricing')"  
                    outline  
                    text-color="white" 
                    label="Join our community"
                    icon="diversity_1"
                />
            </PageTitle>

            <q-separator color="white" class="w-100 q-mt-sm q-mb-md" />
            <div class="col-12 flex justify-center">
                <LoadingData
                    v-if="loadingLatestEntities"
                    text="Loading data..." 
                    colorIcon="white" 
                    colorText="text-white"
                />
                <NoData 
                    v-else-if="entitiesCollection.length === 0" 
                    text="No active collaborators." 
                    color="white" 
                />
                <EntityIndex
                    v-else
                    class="w-card-md"
                    v-for="(entity, index) in entitiesCollection" 
                    :key="index"  
                    :entity="entity"
                    defaultOpen
                    allowActions
                >
                    <template #actions>
                        <q-btn flat round color="primary" icon="business" @click="this.$router.push(redirectRoute + entity.entity_id)" />
                    </template>
                </EntityIndex>
            </div>
        </SectionDesignColored>
     </PageWrapper>

</template>

<script>
import { ref } from 'vue';
import SectionSplit from 'components/SectionSplit.vue';
import SectionDesignDefault from 'components/SectionDesignDefault.vue';
import SectionDesignColored from 'components/SectionDesignColored.vue';
import PageTitle from 'components/PageTitle.vue';
import LoadingData from 'components/LoadingData.vue';
import EntityIndex from 'pages/community/components/EntityIndex.vue';

export default {
    name: 'LandingPage',
    components: {
        SectionSplit, PageTitle, SectionDesignDefault, SectionDesignColored, LoadingData,  EntityIndex
    },

    setup() {
        return {
            redirectRoute: 'community/entities/',
            loadingLatestEntities: ref(true)
        }
    },

    data() {
        return {
            entitiesCollection: [],
        }
    },

    mounted() {
        this.loadLatestEntities();
    },

    methods: {
        async loadLatestEntities() {
            try {
                const response = await this.$axios.get('/get-latest-public-entities');
                this.entitiesCollection = response.data.entities
            } catch (error) {
                console.log('CommunityEntities', error.response ? error.response.data : error)
            } finally {
                this.loadingLatestEntities = false;
            }
        }
    }
};
</script>
