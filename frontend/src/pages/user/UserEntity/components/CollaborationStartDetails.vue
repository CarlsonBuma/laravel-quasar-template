<template>

    <div class="row w-100 justify-center">
        
        <!-- Duration -->
        <CardSimple class="w-100" title="Expected duration of reward">
            <q-card-section>
                <q-input v-model="content.duration" label="Duration" hint="e.g. individual, 2 hours, 3 months, etc." />
            </q-card-section>
        </CardSimple>

        <!-- Details -->
        <CardSimple class="w-100" title="Details about collaboration">
            <q-editor 
                v-model="content.details"
                min-height="10rem"
                ref="editorCreateCollaboration"
                @paste="onPaste"
                :toolbar="[ ['bold', 'italic', 'underline'], ['unordered', 'ordered', 'outdent', 'indent'] ]"
            />

            <q-card-section class="text-right">
                Limit: {{ stringCharacterCount }} / {{ limitDetails }}
            </q-card-section>
        </CardSimple>

        <!-- Navigation -->
        <div class="row w-100 q-ma-md flex justify-end">
            <slot name="navigation" />
        </div>
    </div>
    
</template>

<script>
import { ref } from 'vue'

export default {
    name: 'CollaborationStartDetails',

    props: {
        limitDetails: Number,
        modelValue: {
            type: Object,
            required: true
        }
    },

    computed: {
        content: {
            get() {
                return this.modelValue
            },
            set(value) {
                this.$emit('update:modelValue', value)
            }
        },

        stringCharacterCount() { 
            return this.content.details.length;
        }
    },

    setup() {
        const editorCreateCollaboration = ref(null)
        const latestWordsEntry = ref('');
        const stringCharacterCount = ref(0);

        const onPaste = (evt) => {
            // Let inputs do their thing, so we don't break pasting of links.
            stringCharacterCount.value = 0;
            if (evt.target.nodeName === 'INPUT') return
            let text, onPasteStripFormattingIEPaste
            evt.preventDefault()
            evt.stopPropagation()
            if (evt.originalEvent && evt.originalEvent.clipboardData.getData) {
                text = evt.originalEvent.clipboardData.getData('text/plain')
                editorCreateCollaboration.value.runCmd('insertText', text)
            }
            else if (evt.clipboardData && evt.clipboardData.getData) {
                text = evt.clipboardData.getData('text/plain')
                editorCreateCollaboration.value.runCmd('insertText', text)
            }
            else if (window.clipboardData && window.clipboardData.getData) {
                if (!onPasteStripFormattingIEPaste) {
                    onPasteStripFormattingIEPaste = true
                    editorCreateCollaboration.value.runCmd('ms-pasteTextOnly', text)
                }
                onPasteStripFormattingIEPaste = false
            }
        }

        return {
            editorCreateCollaboration,
            latestWordsEntry,
            onPaste
        }
    },

    methods: {
        // 
    }
}
</script>
