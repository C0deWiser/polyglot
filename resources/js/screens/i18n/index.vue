<script type="text/ecmascript-6">

import Progress from '../../components/Progress'

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
   * Components
   */
  components: {
    Progress
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

            if (!this.info.lastCollected)
              this.info.lastCollected = 'Unknown';

            if (!this.info.lastCompiled)
              this.info.lastCompiled = 'Unknown';

            if (!this.info.lastTranslated)
              this.info.lastTranslated = 'Unknown';

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

              <small class="text-muted d-block" v-if="info.mode==='editor'">
                Polyglot provides online editor.
              </small>
              <small class="text-muted d-block" v-if="info.mode==='collector'">
                Polyglot provides online editor and collects translation strings from source codes.
              </small>
              <small class="text-muted d-block" v-if="info.mode==='translator'">
                Polyglot provides online editor, collects translation strings from source codes and fully supports
                Gettext.
              </small>
            </div>
          </div>

          <div class="w-50 border-right border-bottom">
            <div class="p-4">
              <small class="text-uppercase">Application Locales</small>

              <h4 class="mt-4 mb-0">
                {{ info.locales.join(', ') }}
              </h4>
            </div>
          </div>

        </div>
      </div>

      <div class="card-bg-secondary">
        <div class="d-flex">

          <div class="w-50 border-right border-bottom">
            <div class="p-4">
              <small class="text-uppercase">Last translated</small>

              <h4 class="mt-4 mb-0">
                {{ info.lastTranslated }}
              </h4>

            </div>
          </div>

          <div class="w-50 border-right border-bottom">
            <div class="p-4">
              <small class="text-uppercase">Translation progress</small>

              <Progress class="mt-5 mb-0" :stat="info.stat"></Progress>

            </div>
          </div>

        </div>
      </div>

      <div class="card-bg-secondary">
        <div class="d-flex">

          <div class="w-50 border-right border-bottom" v-if="info.ability.collect">
            <div class="p-4">
              <small class="text-uppercase">Last collected</small>

              <h4 class="mt-4 mb-0">
                {{ info.lastCollected }}
              </h4>

            </div>
          </div>

          <div class="w-50 border-right border-bottom" v-if="info.ability.compile">
            <div class="p-4">
              <small class="text-uppercase">Last compiled</small>

              <h4 class="mt-4 mb-0">
                {{ info.lastCompiled }}
              </h4>

            </div>
          </div>

        </div>
      </div>

    </div>
  </div>
</template>
