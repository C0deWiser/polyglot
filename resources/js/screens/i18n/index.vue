<script type="text/ecmascript-6">
export default {
  /**
   * The component's data.
   */
  data() {
    return {
      ready: false,
      info: {}
    };
  },

  /**
   * Prepare the component.
   */
  mounted() {
    document.title = "Polyglot - i18n";

    this.loadData();
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
      this.loadData();
    },
  },


  methods: {
    /**
     * Load data.
     */
    loadData() {
      this.$http.get(Polyglot.basePath + '/api/i18n')
          .then(response => {
            this.info = response.data;

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
        <h5>i18n</h5>
      </div>

      <div class="card-bg-secondary">
        <div class="d-flex">

          <div class="w-50 border-right border-bottom">
            <div class="p-4">
              <small class="text-uppercase">Working mode</small>

              <h4 class="mt-4 mb-0 text-capitalize">
                {{ info.mode }}
              </h4>
            </div>
          </div>

          <div class="w-50 border-right border-bottom">
            <div class="p-4">
              <small class="text-uppercase">Supported Locales</small>

              <h4 class="mt-4 mb-0">
                {{ info.locales.join(', ') }}
              </h4>
            </div>
          </div>

        </div>
      </div>

      <div class="card-bg-secondary" v-if="info.passthroughs">
        <div class="d-flex">

          <div class="w-100 border-right border-bottom">
            <div class="p-4">
              <small class="text-uppercase">Passthroughs strings</small>

              <h4 class="mt-4 mb-0">
                {{ info.passthroughs.join(' ') }}
              </h4>
            </div>
          </div>

        </div>
      </div>

      <div class="card-bg-secondary">
        <div class="d-flex">

          <div class="w-100 border-right border-bottom">
            <div class="p-4">
              <small class="text-uppercase">Extractor</small>

              <h4 class="mt-4 mb-0">
                Domain: {{ info.domains[0].domain }}<br>
                Sources: {{ info.domains[0].sources }}
              </h4>
            </div>
          </div>

        </div>
      </div>

    </div>
  </div>
</template>
