<script type="text/ecmascript-6">

export default {
  name: "Editor",

  props: ['row', 'poeditor'],

  /**
   * The component's data.
   */
  data() {
    return {
      // string: null
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

  /**
   * Clean after the component is destroyed.
   */
  destroyed() {

  },


  /**
   * Watch these properties for changes.
   */
  watch: {
    // row(newValue, oldValue) {
    //   this.string = newValue;
    // }
  },


  methods: {}
}
</script>

<template>
  <div id="editor" class="modal editor">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Editor</h5>
        </div>
        <form @submit.prevent="$emit('submit', row)" @reset="$emit('close')" v-if="row">
          <div class="modal-body">

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
              <small class="form-text text-muted">{{ row.context }}</small>
            </div>

            <div class="form-group" v-if="!row.msgid_plural">
              <label>String</label>
              <blockquote class="form-control bg-secondary msg-id">{{ row.msgid }}</blockquote>
            </div>

            <div class="form-group msg-id-plural" v-if="row.msgid_plural">
              <label>Plural Strings</label>
              <blockquote class="form-control bg-secondary mb-0 msg-id">{{ row.msgid }}</blockquote>
              <blockquote class="form-control bg-secondary msg-id-plural">{{ row.msgid_plural }}</blockquote>
            </div>

            <div class="form-group" v-if="!row.msgid_plural">
              <label for="msg_str">Translation</label>
              <textarea class="form-control msg-str" id="msg_str"
                        v-model="row.msgstr" :disabled="row.obsolete"></textarea>
            </div>

            <div class="form-group" v-if="row.msgid_plural">
              <label>Plural Translations</label>
              <div class="list-group msgid-str-plural">
                <li class="list-group-item p-0" v-for="(plural, key) in row.msgstr">
                <textarea class="form-control msg_str border-0"
                          v-model="row.msgstr[key]" :disabled="row.obsolete"></textarea>
                </li>
              </div>
            </div>


            <div class="form-group" v-if="poeditor">
              <label for="trans_comm">Translator comment</label>
              <textarea class="form-control" id="trans_comm"
                        v-model="row.comment" :disabled="row.obsolete">
                </textarea>
            </div>

            <div class="form-group" v-if="poeditor && row.developer_comments.length">
              <label>Developer Comment</label>
              <small class="form-text text-muted" v-for="comment in row.developer_comments">{{ comment }}</small>
            </div>

            <div class="form-group" v-if="poeditor && row.reference.length">
              <label>References</label>
              <small class="form-text text-muted" v-for="reference in row.reference">{{ reference }}</small>
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