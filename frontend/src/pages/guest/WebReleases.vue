<style lang="sass">
.timeline-release > .q-timeline__content
    padding: 0px
</style>

<template>

    <PageWrapper>
        <SectionDesignDefault >
            <div class="w-content-sm w-100 flex justify-center">
                <PageTitle 
                    class="text-center"
                    header="Our latest news, announcements and releases."
                    title="Newsfeed."
                />

                <q-separator class="w-100 q-mb-lg" />
                <InfiniteScroll 
                    class="w-card-lg"
                    :disable="isLastEntry"
                    :loading="rendering"
                    @load="getReleases(releases.length)" 
                >
                    <NoData v-if="releases.length === 0 && !rendering" text="No news announced." />
                    <div
                        v-else 
                        v-for="(entry, index) in releases"
                        :key="index"
                        class="flex w-100"
                        :class="entry.type !== 'Good News' ? 'justify-end' : 'justify-start'"
                    >
                        <CardSimple class="w-card-lg">
                            <q-card-section class="q-py-none">
                                    <!-- All Releases -->
                                    <q-timeline 
                                        :color="entry.type === 'Good News' 
                                            ? 'primary' 
                                            : entry.type === 'Notification'
                                                ? 'orange'
                                                : 'purple'" 
                                        :side="entry.type !== 'Good News' ? 'left' : 'right'"
                                    >
                                        <q-timeline-entry
                                            class="q-pa-none timeline-release"
                                            :title="entry.title"
                                            :subtitle="entry.type + ', ' + date.formatDate(entry.created_at, dateFormat)"
                                            :icon="entry.type === 'Good News' 
                                                ? 'grade' 
                                                : entry.type === 'Notification'
                                                    ? 'notifications'
                                                    : 'new_releases'"
                                        >
                                            <span class="_text-break q-pb-none">{{ entry.description }}</span>
                                        </q-timeline-entry>
                                        <q-timeline-entry class="" />
                                    </q-timeline>
                            </q-card-section>
                        </CardSimple>
                    </div>
                </InfiniteScroll>
            </div>
        </SectionDesignDefault>
    </PageWrapper>

</template>

<script>
import { ref } from 'vue';
import { date } from 'quasar';
import { globalMasks } from 'src/boot/globals.js';
import SectionDesignDefault from 'components/SectionDesignDefault.vue';
import InfiniteScroll from 'components/InfiniteScroll.vue';
import PageTitle from 'components/PageTitle.vue';

export default {
    name: 'VisitorReleases',
    components: {
        SectionDesignDefault,  PageTitle,  InfiniteScroll
    },

    setup() {
        return {
            dateFormat: globalMasks.date.switzerland,
            date,
            rendering: ref(false),
            isLastEntry: ref(false),
        }
    },

    data() {
        return {
            releases: []
        }
    },

    mounted() {
        // Code
    },

    methods: {
        async getReleases(index) {
            try {
                if(this.rendering === true || this.isLastEntry) return;
                this.rendering = true;
                const response = await this.$axios.get("/app-get-releases", { params: { 
                    index: index 
                }});
                this.releases.push(...response.data.releases);
                this.isLastEntry = response.data.is_last_entry;
                this.rendering = false;
            } catch (error) {
                console.log('AppReleases', error.response ? error.response.data : error)
                this.rendering = false;
                this.isLastEntry = true;
            }
        },
    }
};
</script>
