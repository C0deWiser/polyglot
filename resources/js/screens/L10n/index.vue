<script type="text/ecmascript-6">

export default {
  /**
   * The component's data.
   */
  data() {
    return {
      ready: false,
      files: {}
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

      this.$http.get(Polyglot.basePath + '/api/L10n')
          .then(response => {

            this.files = response.data;

            this.ready = true;
          });
    },

  }
}
</script>

<template>
  <div>
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5>L10n</h5>
      </div>

      <div v-if="!ready"
           class="d-flex align-items-center justify-content-center card-bg-secondary p-5 bottom-radius">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" class="icon spin mr-2 fill-text-color">
          <path
              d="M12 10a2 2 0 0 1-3.41 1.41A2 2 0 0 1 10 8V0a9.97 9.97 0 0 1 10 10h-8zm7.9 1.41A10 10 0 1 1 8.59.1v2.03a8 8 0 1 0 9.29 9.29h2.02zm-4.07 0a6 6 0 1 1-7.25-7.25v2.1a3.99 3.99 0 0 0-1.4 6.57 4 4 0 0 0 6.56-1.42h2.1z"></path>
        </svg>

        <span>Loading...</span>
      </div>


      <div v-if="ready && files.length == 0"
           class="d-flex flex-column align-items-center justify-content-center card-bg-secondary p-5 bottom-radius">
        <span>There aren't any files.</span>
      </div>

      <table v-if="ready && files.length > 0" class="table table-hover table-sm mb-0">
        <thead>
        <tr>
          <th colspan="4">Files</th>
          <th>Strings</th>
        </tr>
        </thead>

        <tbody>
        <tr v-for="file in files">
          <td v-if="file.depth <= 1"></td>
          <td v-if="file.depth <= 2"></td>
          <td v-if="file.depth <= 3"></td>
          <td :colspan="file.depth">
            <a v-if="file.route" :href="file.route">{{ file.filename }}</a>
            <span v-if="file.dir">{{ file.filename + '/' }}</span>
          </td>
          <td v-if="!file.strings"></td>
          <td v-if="file.strings">{{
              file.strings +
              (file.empty ? ' / untranslated ' + file.empty : '') +
              (file.fuzzy ? ' / fuzzy ' + file.fuzzy : '')
            }}</td>
        </tr>
        </tbody>
      </table>

    </div>

  </div>
</template>
