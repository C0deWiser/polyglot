<script type="text/ecmascript-6">

export default {

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

            <div class="form-group float-right" v-if="poeditor">
              <div class="custom-control custom-switch">
                <input type="checkbox" class="custom-control-input" id="fuzzy" v-model="row.fuzzy">
                <label class="custom-control-label" for="fuzzy">Fuzzy</label>
              </div>
            </div>

            <div class="form-group" v-if="poeditor && row.context">
              <label>Message Context</label>
              <small class="form-text text-muted">{{ row.context }}</small>
            </div>

            <div class="form-group">
              <label>{{ poeditor && row.msgid_plural ? 'Single' : 'String' }}</label>
              <blockquote class="form-control bg-secondary">{{ row.msgid }}</blockquote>
            </div>

            <div class="form-group" v-if="poeditor && row.msgid_plural">
              <label>Plural</label>
              <blockquote class="form-control bg-secondary">{{ row.msgid_plural }}</blockquote>
            </div>

            <div class="form-group" v-if="!row.msgid_plural">
              <label for="msg_str">Translation</label>
              <textarea class="form-control msg_str" id="msg_str"
                     v-model="row.msgstr"></textarea>
            </div>

            <div class="form-group" v-if="poeditor && row.msgid_plural" v-for="(plural, key) in row.msgstr">
              <label>Translation[{{ key }}]</label>
              <textarea class="form-control msg_str"
                     v-model="row.msgstr[key]"></textarea>
            </div>


            <div class="form-group" v-if="poeditor">
              <label for="trans_comm">Translator comment</label>
              <textarea class="form-control" id="trans_comm"
                        v-model="row.comment">
                </textarea>
            </div>

            <div class="form-group" v-if="poeditor && row.developer_comments.length">
              <label>Developer Comments</label>
              <small class="form-text text-muted" v-for="comment in row.developer_comments">{{ comment }}</small>
            </div>

            <div class="form-group" v-if="poeditor && row.reference.length">
              <label>References</label>
              <small class="form-text text-muted" v-for="reference in row.reference">{{ reference }}</small>
            </div>

            <div class="modal-footer">
              <button type="reset" class="btn btn-secondary">Close</button>
              <button type="submit" class="btn btn-primary">Save changes</button>
            </div>

          </div>
        </form>
      </div>
    </div>
  </div>
</template>