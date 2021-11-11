<script type="text/ecmascript-6">

export default {
  /**
   * The component's data.
   */
  data() {
    return {
      ready: false,
      strings: [],
      headers: {}
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

    console.log(this.$route);

    document.title = "Polyglot - " +
        this.$route.params.locale + '/' +
        this.$route.params.category + '/' +
        this.$route.params.filename;

    this.loadFile(this.$route.params);
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
      this.loadFile(this.$route.params);
    }
  },


  methods: {
    /**
     * Load the jobs of the given tag.
     */
    loadFile(params) {

      this.$http.get(Polyglot.basePath + '/api/L10n/' +
          params.locale + '/' +
          params.category + '/' +
          params.filename)
          .then(response => {

            this.strings = response.data.messages;
            this.headers = response.data.headers;

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
        <h5>lang/{{ this.$route.params.locale }}/{{ this.$route.params.category }}/{{ this.$route.params.filename }}</h5>
      </div>

      <div v-if="!ready"
           class="d-flex align-items-center justify-content-center card-bg-secondary p-5 bottom-radius">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" class="icon spin mr-2 fill-text-color">
          <path
              d="M12 10a2 2 0 0 1-3.41 1.41A2 2 0 0 1 10 8V0a9.97 9.97 0 0 1 10 10h-8zm7.9 1.41A10 10 0 1 1 8.59.1v2.03a8 8 0 1 0 9.29 9.29h2.02zm-4.07 0a6 6 0 1 1-7.25-7.25v2.1a3.99 3.99 0 0 0-1.4 6.57 4 4 0 0 0 6.56-1.42h2.1z"></path>
        </svg>

        <span>Loading...</span>
      </div>

      <div v-if="ready && strings.length === 0"
           class="d-flex flex-column align-items-center justify-content-center card-bg-secondary p-5 bottom-radius">
        <span>There aren't any files.</span>
      </div>

      <table v-if="ready && strings.length > 0" class="table table-hover table-sm mb-0">
        <thead>
        <tr>
          <th>String</th>
          <th>Translation</th>
          <th style="width: 175px">Flags</th>
        </tr>
        </thead>

        <tbody>
        <tr v-for="string in strings">
          <td>
            <router-link :title="string.msgid">
              {{ string.msgid }}
            </router-link>
            <span class="msg-id">{{ string.msgid }}</span><br>

            <small class="msg-context text-muted" v-if="string.context">
              Context: {{string.context}}
            </small>
          </td>
          <td>
            {{ string.msgstr }}
          </td>
          <td>

            <small class="badge badge-secondary badge-sm"
                   v-if="string.flags.includes('fuzzy')">
              Fuzzy
            </small>

            <small class="badge badge-secondary badge-sm"
                   v-if="string.translator_comments.length">
              Comments
            </small>

          </td>
        </tr>
        </tbody>
      </table>

    </div>
  </div>
</template>