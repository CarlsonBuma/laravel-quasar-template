<template>

     <PageWrapper noMargin>
        <SectionDesignDefault>
            <SectionSplit>
                <template #left>
                    <SectionTitle 
                        class="text-start w-title"
                        header="Template header."
                        title="Template title."
                        subtitle="Template subtitle"
                    >
                        Some Content
                    </SectionTitle>
                </template>
                <template #right>
                    <CardSimple title="Our community" class="w-card-sm text-center">
                        <q-card-section>
                            <q-icon name="diversity_1" size="72px" color="primary" />
                            <p class="q-mt-sm">Some text.</p>
                        </q-card-section>
                    </CardSimple>
                    <CardSimple title="Our Services" class="w-card-sm text-center">
                        <q-card-section>
                            <q-icon name="engineering" size="72px" color="primary" />
                            <p class="q-mt-sm">Some text.</p>
                        </q-card-section>
                    </CardSimple>
                    <CardSimple title="Your Gigs &amp; Support" class="w-card-sm text-center">
                        <q-card-section>
                            <q-icon name="loyalty" size="72px" color="primary" />
                            <p class="q-mt-sm">Some text.</p>
                        </q-card-section>
                    </CardSimple>
                </template>
            </SectionSplit>
        </SectionDesignDefault>
     </PageWrapper>

</template>

<script>
export default {
    name: 'LandingPage',
    components: {
        // Components
    },

    setup() {
        return {
            // 
        }
    },

    data() {
        return {
            // Code
        }
    },

    mounted() {
        // Code
    },

    methods: {
        // Code
    }
};
</script>
