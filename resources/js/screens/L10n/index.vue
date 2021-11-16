<script type="text/ecmascript-6">

import AddressLine from '../../components/AddressLine';
import FileBrowser from '../../components/FileBrowser';
import FileViewer from "../../components/FileViewer";

export default {
  props: ["hash"],

  /**
   * The component's data.
   */
  data() {
    return {
      ready: false,
      path: {},
      files: [],
      strings: [],
    };
  },

  /**
   * Components
   */
  components: {
    AddressLine,
    FileBrowser,
    FileViewer
  },

  /**
   * Prepare the component.
   */
  mounted() {
    document.title = "Polyglot - L10n";

    this.loadFiles();
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
    '$route'() {
      this.loadFiles();
    }
  },


  methods: {
    /**
     * Load the jobs of the given tag.
     */
    loadFiles() {

      //this.ready = false;

      this.$http.get(Polyglot.basePath + '/api/L10n/' + this.hash)
          .then(response => {

            this.path = response.data.path;
            this.files = response.data.files;
            this.strings = response.data.strings;

            //this.prepareProgressBar();

            this.ready = true;
          });
    },

    prepareProgressBar() {
      this.files.forEach(file => {
        if (file.dir === false) {
          let sum = 0;
          file.progress = {
            waiting: 0,
            fuzzy: 0,
            translated: 100
          };
          if (file.empty > 0) {
            file.progress.waiting = (file.empty / file.strings) * 100;
            sum += file.progress.waiting;
          }
          if (file.fuzzy && file.fuzzy > 0) {
            file.progress.fuzzy = (file.fuzzy / file.strings) * 100
            sum += file.progress.fuzzy;
          }
          file.progress.translated = 100 - sum;
        }
      });

      $('[data-toggle="tooltip"]').tooltip();
    },

  }
}
</script>

<template>
  <div>
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5>Translations</h5>
      </div>

      <div v-if="!ready"
           class="d-flex align-items-center justify-content-center card-bg-secondary p-5 bottom-radius">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" class="icon spin mr-2 fill-text-color">
          <path
              d="M12 10a2 2 0 0 1-3.41 1.41A2 2 0 0 1 10 8V0a9.97 9.97 0 0 1 10 10h-8zm7.9 1.41A10 10 0 1 1 8.59.1v2.03a8 8 0 1 0 9.29 9.29h2.02zm-4.07 0a6 6 0 1 1-7.25-7.25v2.1a3.99 3.99 0 0 0-1.4 6.57 4 4 0 0 0 6.56-1.42h2.1z"></path>
        </svg>

        <span>Loading...</span>
      </div>

      <div v-if="ready && path" class="card-body pb-0 pt-0">
        <AddressLine :path="path"></AddressLine>
      </div>

      <div v-if="ready && files && files.length === 0"
           class="d-flex flex-column align-items-center justify-content-center card-bg-secondary p-5 bottom-radius">
        <span>There aren't any files.</span>
      </div>

      <FileBrowser v-if="ready && files && files.length > 0" :files="files"></FileBrowser>
      <FileViewer v-if="ready && strings && strings.length > 0" :strings="strings" :info="path"></FileViewer>

    </div>

  </div>
</template>
