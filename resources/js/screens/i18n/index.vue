<script type="text/ecmascript-6">

import ProgressBar from '../../components/ProgressBar'

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
    ProgressBar: ProgressBar
  },

  /**
   * Prepare the component.
   */
  mounted() {
    document.title = "Polyglot - i18n";

    this.loadData();
  },

  watch: {
    '$route'() {
      this.loadData();
    },
  },


  methods: {
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
    <div v-if="ready" class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5>i18n</h5>
      </div>

      <div class="card-bg-secondary">
        <div class="d-flex">

          <div class="w-50 border-right border-bottom">
            <div class="p-4">
              <small class="text-uppercase">Application Locales</small>

              <h4 class="mt-4 mb-0">
                {{ info.locales.join(', ') }}
              </h4>
            </div>
          </div>

          <div class="w-50 border-right border-bottom">
            <div class="p-4">
              <small class="text-uppercase">Working mode</small>

              <h4 v-if="info.enabled" class="mt-4 mb-0 text-capitalize">Translation Service</h4>
              <h4 v-if="!info.enabled" class="mt-4 mb-0 text-capitalize">Translation Editor</h4>

              <small class="mb-0" v-if="info.enabled">Polyglot works as Translation Service Provider with Gettext
                support</small>
              <small class="mb-0" v-if="!info.enabled">Polyglot works as online Translation Editor</small>
            </div>
          </div>
        </div>

      </div>
    </div>

    <div v-if="ready" class="card card-bg-secondary">
      <div class="d-flex">

        <div class="w-50 border-right border-bottom">
          <div class="p-4">
            <small class="text-uppercase">Last translated</small>

            <h4 class="mt-4 mb-0">
              {{ info.lastTranslated }}
            </h4>

            <small class="mb-0">The last time the translation files were modified</small>

          </div>
        </div>
        <div class="w-50 border-right border-bottom">
          <div class="p-4">
            <small class="text-uppercase">Translation progress</small>

            <ProgressBar class="mt-5 mb-0" :stat="info.stat"></ProgressBar>

          </div>
        </div>

      </div>
    </div>

    <div v-if="ready" class="card card-bg-secondary">
      <div class="d-flex">

        <div class="w-50 border-right border-bottom">
          <div class="p-4">
            <small class="text-uppercase">Last collected</small>

            <h4 class="mt-4 mb-0">
              {{ info.lastCollected }}
            </h4>

            <small class="mb-0">The last time the translation strings were collected</small>

          </div>
        </div>

        <div class="w-50 border-right border-bottom">
          <div class="p-4">
            <small class="text-uppercase">Last compiled</small>

            <h4 class="mt-4 mb-0">
              {{ info.lastCompiled }}
            </h4>

            <small class="mb-0">The last time the gettext files were compiled</small>

          </div>
        </div>

      </div>
    </div>


  </div>
  </div>
</template>
