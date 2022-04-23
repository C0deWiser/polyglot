<script type="text/ecmascript-6">

import ProgressBar from '../../components/ProgressBar'

export default {
    /**
     * The component's data.
     */
    data() {
        return {
            ready: false,
            error: undefined,
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

                    this.error = undefined;
                    this.ready = true;
                })
                .catch(error => {
                    this.error = error;
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

            <div v-if="!ready"
                 class="d-flex align-items-center justify-content-center card-bg-secondary p-5 bottom-radius">
                <svg v-if="!error" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" class="icon spin mr-2 fill-text-color">
                    <path
                        d="M12 10a2 2 0 0 1-3.41 1.41A2 2 0 0 1 10 8V0a9.97 9.97 0 0 1 10 10h-8zm7.9 1.41A10 10 0 1 1 8.59.1v2.03a8 8 0 1 0 9.29 9.29h2.02zm-4.07 0a6 6 0 1 1-7.25-7.25v2.1a3.99 3.99 0 0 0-1.4 6.57 4 4 0 0 0 6.56-1.42h2.1z"></path>
                </svg>

                <span v-if="!error">{{ $root.$gettext('Loading...') }}</span>

                <span v-if="error">{{ error }}</span>
            </div>

            <div v-if="ready" class="card-bg-secondary">
                <div class="d-flex">

                    <div class="w-50 border-right border-bottom">
                        <div class="p-4">
                            <small class="text-uppercase">{{ $root.$gettext('Working mode') }}</small>

                            <h4 v-if="info.enabled" class="mt-4 mb-0 text-capitalize">{{ $root.$gettext('Translation Service') }}</h4>
                            <h4 v-if="!info.enabled" class="mt-4 mb-0 text-capitalize">{{ $root.$gettext('Translation Editor') }}</h4>

                            <small class="mb-0" v-if="info.enabled">{{ $root.$gettext('Polyglot provides Translation Service with Gettext support') }}</small>
                            <small class="mb-0" v-if="!info.enabled">{{ $root.$gettext('Polyglot works as online Translation Editor') }}</small>
                        </div>
                    </div>

                    <div class="w-50 border-right border-bottom">
                        <div class="p-4">
                            <small class="text-uppercase">{{ $root.$gettext('Application Locales') }}</small>

                            <h4 class="mt-4 mb-0">
                                {{ info.locales.join(', ') }}
                            </h4>
                        </div>
                    </div>

                </div>

            </div>
        </div>

        <div v-if="ready" class="card card-bg-secondary">
            <div class="d-flex">

                <div class="w-50 border-right border-bottom">
                    <div class="p-4">
                        <small class="text-uppercase">{{ $root.$gettext('Last translated') }}</small>

                        <h4 class="mt-4 mb-0">
                            {{ info.lastTranslated }}
                        </h4>

                        <small class="mb-0">{{ $root.$gettext('The last time the translation files were modified') }}</small>

                    </div>
                </div>
                <div class="w-50 border-right border-bottom">
                    <div class="p-4">
                        <small class="text-uppercase">{{ $root.$gettext('Translation progress') }}</small>

                        <ProgressBar class="mt-5 mb-0" :stat="info.stat"></ProgressBar>

                    </div>
                </div>

            </div>
        </div>

        <div v-if="ready" class="card card-bg-secondary">
            <div class="d-flex">

                <div class="w-50 border-right border-bottom">
                    <div class="p-4">
                        <small class="text-uppercase">{{ $root.$gettext('Last collected') }}</small>

                        <h4 class="mt-4 mb-0">
                            {{ info.lastCollected }}
                        </h4>

                        <small class="mb-0">{{ $root.$gettext('The last time the translation strings were collected') }}</small>

                    </div>
                </div>

                <div class="w-50 border-right border-bottom">
                    <div class="p-4">
                        <small class="text-uppercase">{{ $root.$gettext('Last compiled') }}</small>

                        <h4 class="mt-4 mb-0">
                            {{ info.lastCompiled }}
                        </h4>

                        <small class="mb-0">{{ $root.$gettext('The last time the gettext files were compiled') }}</small>

                    </div>
                </div>

            </div>
        </div>


    </div>
</template>
