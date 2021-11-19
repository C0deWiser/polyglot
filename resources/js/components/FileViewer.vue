<script>
import Editor from './Editor';
import Vue from "vue";

export default {
    name: "FileViewer",
    props: ['strings', 'info'],

    data() {
        return {
            poeditor: false,
            shadow: null,
            selectedRow: null,
            locale: null,
            submitting: false,
            error: undefined
        };
    },

    components: {
        Editor
    },

    mounted() {
        //document.title = "Polyglot - " + this.title();

        $('body')
            .on('hidden.bs.modal', '#editor', event => {
                this.selectedRow = null;
            })
            .on('shown.bs.modal', '#editor', event => {
                setTimeout(() => {
                    $('#editor .msg_str').first().focus();
                    $("#editor textarea").each(function () {
                        this.setAttribute("style", "height:" + (this.scrollHeight) + "px;overflow-y:hidden;");
                    });
                }, 25);
            })
            .on("input", '#editor textarea', function () {
                this.style.height = "auto";
                this.style.height = (this.scrollHeight) + "px";
            });
    },
    methods: {

        /**
         * Open the modal for editing a row.
         */
        openEditor(row, key) {
            this.shadow = {...row};
            this.selectedRow = key;

            $('#editor').modal();
        },

        /**
         * Close editor.
         */
        closeEditor() {
            $('#editor').modal('hide');
            this.error = undefined;
        },

        submitEditor(row) {

            this.submitting = true;

            this.$http.post(Polyglot.basePath + '/api/L10n/' + this.info.relative, row)
                .then(response => {
                    Vue.set(this.strings, this.selectedRow, row);

                    this.closeEditor();
                })
                .catch(error => {
                    this.error = error;
                })
                .finally(() => {
                    this.submitting = false;
                });
        },

        msgId(entry) {
            if (entry.msgid_plural) {
                return [entry.msgid, entry.msgid_plural];
            }

            return entry.msgid;
        },

        msgStr(entry)
        {
            if (entry.msgid_plural) {
                let translated = true;
                entry.msgstr.forEach(msgstr => {
                    if (!msgstr) translated = false;
                })
                return translated ? entry.msgstr : '';
            }

            return entry.msgstr;
        }
    },
}
</script>

<template>
    <div>
        <div v-if="strings.length === 0"
             class="d-flex flex-column align-items-center justify-content-center card-bg-secondary p-5 bottom-radius">
            <span>There aren't any strings.</span>
        </div>

        <table v-if="strings.length > 0" class="table table-hover table-sm mb-0">
            <thead>
            <tr>
                <th>String</th>
                <th>Translation</th>
            </tr>
            </thead>

            <tbody>
            <tr v-for="(string, key) in strings" @click="openEditor(string, key)"
                :class="{'table-active':selectedRow===key, 'table-dark':string.obsolete===true, 'table-warning':string.fuzzy===true}">
                <td>
                    <div class="msg-id">
                        {{ msgId(string) }}
                    </div>

                    <small class="msg-context text-muted" v-if="string.context">
                        Context: {{ string.context }}
                    </small>
                </td>
                <td>
                    <div class="msg-str">{{ msgStr(string) }}</div>

                    <small class="translator_comments text-muted"
                           v-if="string.comment">
                        Translator: {{ string.comment }}
                    </small>
                </td>
            </tr>
            </tbody>
        </table>

        <Editor :row="shadow" :poeditor="info.type === 'po'" :error="error"
                @close="closeEditor()" @submit="submitEditor"
            :class="{'submitting':submitting===true}"></Editor>

    </div>
</template>


<style scoped>

</style>