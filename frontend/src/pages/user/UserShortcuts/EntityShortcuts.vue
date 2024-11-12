<template>

    <PageWrapper title="Dashboard"  :rendering="loading" >
        <NoData v-if="userShortcuts.length === 0" text="No shortcuts added." />
        <div 
            class="avatar-width"
            v-else 
            v-for="(entity, index) in userShortcuts" 
            :key="index" 
        >
            <EntityIndex :entity="entity" allowActions>
                <template #actions>
                    <q-btn flat round color="primary" icon="business" @click="this.$router.push(redirectRoute + entity.entity_id)" />
                    <q-btn flat round color="grey" icon="settings">
                        <q-menu>
                            <q-list>
                                <q-item @click="removeEntityConfirm(entity)" clickable v-ripple>
                                    <q-item-section side>
                                        <q-icon color="red" size="17px" name="delete" />
                                    </q-item-section>
                                    <q-item-section>Remove shortcut</q-item-section>
                                </q-item>
                            </q-list>
                        </q-menu>
                    </q-btn>
                </template>
            </EntityIndex>
        </div>
    </PageWrapper>

</template>

<script>
import { ref } from 'vue';
import EntityIndex from 'pages/community/components/EntityIndex.vue';

export default {
    name: 'EntityShortcuts',
    components: {
        EntityIndex
    },

    setup() {
        return {
            redirectRoute: '/community/entities/',
            loading: ref(true),
        };
    },

    data() {
        return {
            userShortcuts: []
        }
    },

    mounted() {
        this.loadShortcuts();
    },

    methods: {
        async loadShortcuts() {
            try {
                this.loading = true;
                const response = await this.$axios.get('/get-user-entities-shortcuts');
                this.userShortcuts = response.data.entities;
            } catch (error) {
                this.$toast.error(error.response ? error.response : error)
            } finally {
                this.loading = false;
            }
        },

        async removeShortcut(entity) {
            try {
                this.$toast.load()
                console.log('asdfasdf')
                const response = await this.$axios.delete('/remove-user-entity-shortcut/' + entity.entity_id);
                this.userShortcuts.forEach((bookmark, index) => {
                    if(bookmark.entity_id === entity.entity_id) this.userShortcuts.splice(index, 1)
                });
                this.$toast.success(response.data.message)
            } catch (error) {
                this.$toast.error(error.response ? error.response : error)
            }
        },

        removeEntityConfirm(entity) {
            this.$q.dialog({
                title: 'Confirm',
                message: 'Would you like to remove your collaboration with ' + entity.name + '? All corresponding data will be deleted permanently.',
                cancel: true,
            }).onOk(() => {
                this.removeShortcut(entity)
            })
        }
    },
};
</script>
