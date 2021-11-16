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
    },

    submitEditor(row) {



      this.$http.post(Polyglot.basePath + '/api/L10n/' + this.info.relative, row)
          .then(response => {
            Vue.set(this.strings, this.selectedRow, row);
            row = this.strings[this.selectedRow];
            row.saved = true;
            this.coolDown(this.selectedRow);
          })
          .catch(error => {
            row = this.strings[this.selectedRow];
            row.failed = error;
            this.coolDown(this.selectedRow);
          })
          .finally(() => {
            this.closeEditor();
          });
    },

    coolDown(key) {
      setTimeout(() => {
        let row = this.strings[key];
        row.saved = false;
        row.failed = false;
        Vue.set(this.strings, key, row);
      }, 1000);
    },
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
          :class="{'table-active':selectedRow==key, 'table-success':string.saved==true, 'table-danger':string.failed==true}">
        <td>
          <div class="msg-id">
            <svg v-if="string.fuzzy" class="icon msg-flag msg-flag-fuzzy" title="Fuzzy"
                 xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
              <path
                  d="M15.3 14.89l2.77 2.77a1 1 0 0 1 0 1.41 1 1 0 0 1-1.41 0l-2.59-2.58A5.99 5.99 0 0 1 11 18V9.04a1 1 0 0 0-2 0V18a5.98 5.98 0 0 1-3.07-1.51l-2.59 2.58a1 1 0 0 1-1.41 0 1 1 0 0 1 0-1.41l2.77-2.77A5.95 5.95 0 0 1 4.07 13H1a1 1 0 1 1 0-2h3V8.41L.93 5.34a1 1 0 0 1 0-1.41 1 1 0 0 1 1.41 0l2.1 2.1h11.12l2.1-2.1a1 1 0 0 1 1.41 0 1 1 0 0 1 0 1.41L16 8.41V11h3a1 1 0 1 1 0 2h-3.07c-.1.67-.32 1.31-.63 1.89zM15 5H5a5 5 0 1 1 10 0z"></path>
            </svg>

            {{ string.msgid }}
          </div>

          <small class="msg-context text-muted" v-if="string.context">
            Context: {{ string.context }}
          </small>
        </td>
        <td>
          <div class="msg-str">{{ string.msgstr }}</div>

          <small class="translator_comments text-muted"
                 v-if="string.comment">
            Translator: {{ string.comment }}
          </small>
        </td>
      </tr>
      </tbody>
    </table>

    <Editor :row="shadow" :poeditor="info.type === 'po'" @close="closeEditor()" @submit="submitEditor"></Editor>

  </div>
</template>


<style scoped>

</style>