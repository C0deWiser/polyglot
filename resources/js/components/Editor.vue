<script type="text/ecmascript-6">

import Vue from "vue";

export default {
    name: "Editor",

    props: ['row', 'poeditor', 'error'],

    /**
     * The component's data.
     */
    data() {
        return {
            single: undefined,
            plural: undefined
        };
    },

    /**
     * Components
     */
    components: {},

    /**
     * Prepare the component.
     */
    mounted() {

    },

    watch: {
        row() {
            if (this.row.msgid_plural) {
                this.single = this.row.msgstr[0];
                this.plural = this.row.msgstr[1];
            }
        },
    },

    computed: {
        can_copy() {
            let can = true;

            if (this.row.msgid_plural) {
                if (this.single || this.plural) can = false;
                this.row.msgstr.forEach((msgstr) => {
                    if (msgstr) can = false;
                });
            } else {
                if (this.row.msgstr) can = false;
            }

            return can;
        },
        changed() {
            let row = this.row;
            if (row.msgid_plural) {
                row.msgstr[0] = this.single;
                row.msgstr[1] = this.plural;
            }
            return row;
        }
    },

    methods: {
        copy() {
            if (this.row.msgid_plural) {
                this.single = this.row.msgid;
                this.plural = this.row.msgid_plural;
            } else {
                this.row.msgstr = this.row.msgid;
            }
        },
    }
}
</script>

<template>
    <div id="editor" class="modal editor">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editor</h5>
                </div>
                <form @submit.prevent="$emit('submit', changed)" @reset="$emit('close')" v-if="row">
                    <div class="modal-body">

                        <div v-if="error" class="alert alert-danger" role="alert">
                            {{ error }}
                        </div>

                        <div v-if="row.obsolete" class="alert alert-dark" role="alert">
                            This translation string is removed from source code and marked as obsolete.
                            Do not spend you time for it.
                        </div>

                        <div v-if="row.fuzzy" class="alert alert-warning" role="alert">
                            This translation string might not be a correct translation (anymore).
                            Check if the translation requires further modification, or is acceptable as is.
                        </div>

                        <div class="form-group float-right" v-if="poeditor">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="fuzzy" v-model="row.fuzzy"
                                       :disabled="row.obsolete">
                                <label class="custom-control-label" for="fuzzy">Fuzzy</label>
                            </div>
                        </div>

                        <div class="form-group" v-if="row.context">
                            <label>Message Context</label>
                            <strong class="form-text text-primary">{{ row.context }}</strong>
                        </div>

                        <div class="form-group form-group-msgid">
                            <label v-if="row.msgid_plural">Plural Strings</label>
                            <label v-else>String</label>

                            <blockquote v-if="row.msgid_plural" class="form-control bg-secondary user-select-all mb-0 msgid">{{ row.msgid }}</blockquote>
                            <blockquote v-if="row.msgid_plural" class="form-control bg-secondary user-select-all msgid-plural">{{ row.msgid_plural }}</blockquote>
                            <blockquote v-else class="form-control bg-secondary user-select-all">{{ row.msgid }}</blockquote>
                        </div>

                        <div class="form-group form-group-msgstr">
                            <label v-if="row.msgid_plural">Plural Translations</label>
                            <label v-else>Translation</label>
                            <span class="text-primary small copy" v-if="can_copy" @click="copy">
                                copy from above
                            </span>

                            <ul class="list-group" v-if="row.msgid_plural">
                                <li class="list-group-item p-0">
                                    <textarea class="form-control border-0" v-model="single" :disabled="row.obsolete"></textarea>
                                </li>
                                <li class="list-group-item p-0">
                                    <textarea class="form-control border-0" v-model="plural" :disabled="row.obsolete"></textarea>
                                </li>
                                <li class="list-group-item p-0" v-for="(plural, key) in row.msgstr" v-if="key > 1">
                                    <textarea class="form-control border-0"
                                              v-model="row.msgstr[key]" :disabled="row.obsolete"></textarea>
                                </li>
                            </ul>
                            <textarea v-else class="form-control"
                                      v-model="row.msgstr" :disabled="row.obsolete"></textarea>
                        </div>

                        <div class="form-group" v-if="row.developer_comments && row.developer_comments.length">
                            <label>Developer Comments</label>
                            <blockquote class="developer-comments text-muted small">/*<div v-for="comment in row.developer_comments"> * {{ comment }}</div> */</blockquote>
                        </div>

                        <div class="form-group" v-if="poeditor">
                            <label>Your Comment</label>
                            <textarea class="form-control" id="trans_comm"
                                      v-model="row.comment" :disabled="row.obsolete"></textarea>
                        </div>

                        <div class="form-group" v-if="row.reference && row.reference.length">
                            <label>References</label>
                            <small class="form-text text-muted" v-for="reference in row.reference">
                                {{ reference }}
                            </small>
                        </div>

                        <div class="modal-footer">
                            <button type="reset" class="btn btn-secondary">Close</button>
                            <button type="submit" class="btn btn-primary" :disabled="row.obsolete">Save changes</button>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>
</template>