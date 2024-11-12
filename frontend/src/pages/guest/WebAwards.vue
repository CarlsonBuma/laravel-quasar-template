<template>

    <PageWrapper noMargin>
        <SectionDesignDefault class="w-100">
            <SectionSplit>
                <template #left>
                    <PageTitle 
                        class="w-title-md"
                        header="Recognizing Excellence: Celebrating Our Outstanding Collaborators"
                        title="Spotlight on Success: Honoring our top collaborators."
                        subtitle="
                            Creating attractive awards for your collaborators can be a great way to recognize 
                            their contributions and foster a positive working relationship. These awards not only 
                            celebrate individual achievements but also inspire others to strive for excellence. 
                            Our unique award-system, allows you to reward your collaborators.
                        "
                    />
                </template>
                <template #right>
                    <LoadingData
                        v-if="loading"
                        text="Loading data..." 
                        colorIcon="primary" 
                    />
                    <CardSimple 
                        v-else
                        :title="expertise.label" 
                        class="w-card-sm text-center"
                        v-for="expertise in attributes.levels"
                        :key="expertise.id"
                    >
                        <template #actions>
                            <span class="text-caption">Level: {{ expertise.evaluation}}</span>
                        </template>
                        <q-card-section class="text-left text-caption">
                            <span>{{ expertise.description }}</span>
                        </q-card-section>
                    </CardSimple>
                </template>
            </SectionSplit>
        </SectionDesignDefault>

        <SectionDesignColored>
            <PageTitle 
                class="text-center q-pt-lg text-white"
                title="Award standards."
                subtitle="
                    Defining award standards ensures consistency and fairness in recognizing achievements, 
                    making the evaluation process transparent and credible. Well-defined standards uphold the value 
                    and prestige of the awards, fostering trust and respect within the community.
                "
            />

            <q-separator class="w-100 q-mb-lg" color="white"/>
            <div class="row w-100 justify-center">
                <LoadingData
                    v-if="loading"
                    text="Loading data..." 
                    colorIcon="white" 
                    colorText="text-white"
                />
                <div v-else v-for="type in attributes.types" :key="type.id">
                    <CardSimple :title="type.label" class="w-card-sm">
                        <div class="flex justify-center q-mt-sm">
                            <q-icon 
                                :name="type.icon ? type.icon : 'badge'" 
                                color="primary" 
                                size="120px" 
                            />
                        </div>

                        <q-card-section>
                            <p class="text-caption q-my-none">{{ type.description }}</p>
                        </q-card-section>

                        <q-separator />
                        <q-card-section class="text-right text-caption">
                            <p class="w-100 q-my-none">Evaluation: {{ type.evaluation }}</p>
                            <p class="w-100 q-my-none">Credits: {{ type.credits }}</p>
                        </q-card-section>
                    </CardSimple>
                </div>
            </div>

            <q-separator class="w-100 q-my-xl" color="white"/>
            <div class="row w-title-md text-caption text-white">
                <span>
                    <b>Credits:</b> This refers to the intrinsic value or importance of an award, indicating how much it is respected or esteemed.<br>
                    <b>Evaluation:</b> This is the process of assessing the award's significance or impact, using a specific scale (from 0 to n) to determine its worth or quality.
                </span>
            </div>
        </SectionDesignColored>
    </PageWrapper>

</template>

<script>
import { ref } from 'vue'
import { loadCertificateAttributes } from 'src/apis/public.js';
import PageTitle from 'components/PageTitle.vue';
import SectionSplit from 'components/SectionSplit.vue';
import SectionDesignDefault from 'components/SectionDesignDefault.vue';
import SectionDesignColored from 'components/SectionDesignColored.vue';
import LoadingData from 'components/LoadingData.vue';

export default {
    name: 'WebAwards',
    components: {
        SectionDesignDefault, SectionSplit, SectionDesignColored, 
        PageTitle,  LoadingData
    },

    setup() {
        return {
            loading: ref(false),
        };
    },

    data() {
        return {
            attributes: {
                types: [],
                areas: [],
                levels: []
            }
        }
    },

    mounted() {
        this.loadAttributes();
    },

    methods: {
        async loadAttributes() {
            try {
                this.loading = true;
                const attributesResponse = await loadCertificateAttributes();
                this.attributes = attributesResponse.data;
                console.log('certificate-attributes', this.attributes)
            } catch (error) {
                this.$toast.error('WebAwards', error.response ? error.response : error)
            } finally {
                this.loading = false;
            }
        },

        submitAbout(attributes) {
            console.log(attributes)
        }
    },
};
</script>
