<template>

    <q-list separator>
        <slot name="items-top">
            <q-item v-if="!noHead">
                <q-item-section>
                    <q-item-label class="text-caption">
                        <b>Start:</b><br>
                        {{ datetime ?? 'Individual'}}<br>
                    </q-item-label>
                </q-item-section>
                <q-item-section avatar>
                    <q-item-label caption class="text-right">
                        <b>Duration:</b><br>
                        {{ duration ?? 'Individual' }}
                    </q-item-label>
                </q-item-section>
            </q-item>
        </slot>

        <q-item class="q-py-md" clickable separator bordered >
            <q-item-section avatar top>
                <slot name="icon">
                    <q-icon :name="icon ?? 'diversity_1'" color="primary" size="42px" />
                </slot>
            </q-item-section>

            <q-item-section @click="$emit('showPreview')" top>
                <!-- Details -->
                <q-item-label lines="1">
                    <span class="text-weight-medium">{{ title ?? 'No title assigned.' }}</span>
                    <span class="text-grey-8" v-if="caption"> - {{ caption }}</span>
                </q-item-label>
                <q-item-label caption lines="3">
                    {{ about ?? 'No details given.' }}
                </q-item-label>
            </q-item-section>

            <q-item-section class="col-auto gt-xs" v-if="showContact && contact" top>
                <q-item-label lines="4" caption class="q-px-sm _text-break text-right">
                    <span><b>Contact:</b><br>{{ contact }}</span>
                </q-item-label>
            </q-item-section>

            <q-item-section v-if="!noActions || allowShortcuts" top side>
                <div class="text-grey-8 q-gutter-xs">
                    <!-- Shortcuts -->
                    <slot name="shortcuts" />
                    
                    <!-- Settings -->
                    <q-btn-dropdown v-if="!noActions" dropdown-icon="more_vert" size="12px" flat dense round>
                        <slot name="dropdown-actions" />
                        <q-list separator>
                            <slot name="actions"/>
                            <q-item clickable v-close-popup>
                                <q-item-section avatar>
                                    <q-icon color="red" name="delete" />
                                </q-item-section>
                                <q-item-section>
                                    <q-item-label class="text-caption" @click="confirmRemove()">Remove collaboration</q-item-label>
                                </q-item-section>
                            </q-item>
                        </q-list>
                    </q-btn-dropdown>
                </div>
            </q-item-section>
        </q-item>
        <slot name="items" />
    </q-list>

</template>

<script>
export default {
    name: 'CollaborationList',
    props: {
        datetime: String,
        duration: String,
        title: String,
        caption: String,
        about: String,
        contact: String,
        icon: String,
        noActions: Boolean,
        allowShortcuts: Boolean,
        showContact: Boolean,
        noHead: Boolean,
    },

    emits: [
        'showPreview',
        'remove',
    ],

    methods: {
        confirmRemove() {
            this.$q.dialog({
                title: 'Remove collaboration',
                message: 'Please confirm removal of your collaboration. Your participation and award will be removed from your avatar!',
                cancel: true,
            }).onOk(() => {
                this.$emit('remove')
            })
        },
    },
};
</script>
