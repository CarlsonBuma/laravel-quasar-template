<template>

    <PageWrapper noMargin>
        <SectionDesignDefault>
            <SectionSplit>
                <template #left>
                    <SectionTitle 
                        header="Join our community! Get access to the proivded services."
                        title="Start collaboring within our unique community."
                        subtitle="
                            Join our community, and provide your skills and services to our collaborators.
                            Get access to our provided services and start collaborating within our network.
                            We provide a plattform for personal skill development, experience exchange and collaboration.
                        "
                    />
                </template>
                <template #right>
                    <CardService
                        class="w-card-lg"
                        iconName="badge"
                        iconSize='220px'
                        price='Free'
                        priceTag='forever'
                        title='Collaborator'
                        description="
                            Join our network and start collaborating within our community.
                            Establish your experience and network with other unique collaborators.
                        "
                        :items="[{
                            icon: 'done_all',
                            title: 'Personal Avatar',
                            description: 'Get your own unique avatar and provide your experience to our community.',
                        }, {
                            icon: 'done_all',
                            title: 'Skill development',
                            description: 'Develop your skills by continiuous learning according your goals.',
                        }, {
                            icon: 'done_all',
                            title: 'Access to Network',
                            description: 'Get in touch with our unique serviceproviders. Connect your avatar with our community.',
                        }]"
                    >
                        <template #actions>
                            <q-btn 
                                flat 
                                :disable="$user.access.user" 
                                color="primary" 
                                icon="auto_awesome" 
                                @click="$emit('authorize')" 
                                :label="$user.access.user ? 'Access granted' : 'Login'" 
                            />
                        </template>
                    </CardService>
                </template>
            </SectionSplit>
        </SectionDesignDefault>

        <SectionDesignColored>
            <SectionTitle 
                class="text-center q-pt-lg text-white"
                title="Our services."
                subtitle="Join our community! Get access to the proivded services."
            />
            <div class="row w-100 justify-center">

                <!-- Price 1 -->
                <CardService
                    class="w-card-lg"
                    iconName="business"
                    iconSize='220px'
                    price='249.00 CHF'
                    priceTag='+12 month'
                    title='My Business Cockpit'
                    description="
                        Welcome to your Business Cockpit. Join our community and start collaborating with our members, partners and investors.
                        Collabris provides the best experience for a sustainable market establishment for your business.
                    "
                    :items="[{
                        icon: 'done_all',
                        title: 'Network & Establishment',
                        description: 'Let our community find your services, with our algorithms and integrated AI (upcomming). Establish your collaborations and enrich your network with our unique collaborators.',
                    }]"
                >
                    <template #actions>
                        <q-btn 
                            v-if="$user.access.user"
                            @click="$router.push('/account/access')" 
                            label="See access" 
                            flat 
                            color="primary" 
                            icon="auto_awesome" 
                        />
                    </template>
                </CardService>

                <!-- Support -->
                <div class="w-card-lg"> 
                    <CardSimple>
                        <div class="flex justify-center">
                            <q-icon name="support_agent" color="primary" size="220px" />
                        </div>
                        <q-card-section>
                            <div class="row no-wrap items-center">
                                <div class="col text-h6 ellipsis">
                                    Support
                                </div>
                            </div>
                        </q-card-section>
                        <q-separator class="q-mb-md"/>
                        <q-card-section class="row align-top justify-center text-center">
                            <p>We are here to support you. Please do not hesitate to contact us via
                                <span><q-icon name="email" class="q-mr-xs" /><a href="mailto:hello@gigup.ch">hello@collabris.ch</a></span>.
                            </p>
                        </q-card-section>
                    </CardSimple>
                </div>
            </div>
        </SectionDesignColored>
    </PageWrapper>

</template>

<script>
import CardService from 'components/CardService.vue';

export default {
    name: 'WebPricing',
    components: {
        CardService
    },

    emits: [
        'authorize'
    ],

    setup() {
        // Code
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
    },
};
</script>
