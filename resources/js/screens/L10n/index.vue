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
            loading: false,
            path: {},
            files: [],
            strings: [],
            headers: {},
            error: undefined,
            search: '',
            filtered: [],
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

    computed: {},

    /**
     * Watch these properties for changes.
     */
    watch: {
        '$route'() {
            this.loadFiles();
        },
        search() {
            let filtered = [];

            if (this.search) {
                this.strings.forEach((row => {
                    console.info(row.msgid, row.msgid.search(this.search));
                    let regexp = new RegExp(this.search, 'i');
                    if (row.msgid.search(regexp) > -1) {
                        filtered.push(row);
                    }
                }));
                this.filtered = filtered;
            } else {
                this.filtered = this.strings;
            }
        }
    },

    methods: {
        /**
         * Load the jobs of the given tag.
         */
        loadFiles() {

            this.loading = true;

            this.$http.get(Polyglot.basePath + '/api/L10n/' + this.hash)
                .then(response => {

                    this.path = response.data.path;
                    this.files = response.data.files;
                    this.strings = response.data.strings;
                    this.headers = response.data.headers;
                    this.filtered = this.strings;

                    this.error = undefined;
                    this.ready = true;
                })
                .catch(error => {
                    this.error = error;
                })
                .finally(() => {
                    this.loading = false;
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

                <input type="text" class="form-control" placeholder="Search" style="width:200px"
                       v-model="search" v-if="ready && strings && strings.length > 0">
            </div>

            <div v-if="!ready"
                 class="d-flex align-items-center justify-content-center card-bg-secondary p-5 bottom-radius">
                <svg v-if="!error" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                     class="icon spin mr-2 fill-text-color">
                    <path
                        d="M12 10a2 2 0 0 1-3.41 1.41A2 2 0 0 1 10 8V0a9.97 9.97 0 0 1 10 10h-8zm7.9 1.41A10 10 0 1 1 8.59.1v2.03a8 8 0 1 0 9.29 9.29h2.02zm-4.07 0a6 6 0 1 1-7.25-7.25v2.1a3.99 3.99 0 0 0-1.4 6.57 4 4 0 0 0 6.56-1.42h2.1z"></path>
                </svg>

                <span v-if="!error">Loading...</span>

                <span v-if="error">{{ error }}</span>
            </div>

            <div v-if="ready && path" class="card-body pb-0 pt-0">
                <AddressLine :path="path"></AddressLine>

                <div v-if="ready && files && files.length === 0"
                     class="d-flex flex-column align-items-center justify-content-center card-bg-secondary p-5 bottom-radius">
                    <span>There aren't any files.</span>
                </div>

                <FileBrowser v-if="ready && files && files.length > 0" :files="files"
                             :class="{'loading':loading===true}"></FileBrowser>

                <FileViewer v-if="ready && strings && strings.length > 0"
                            :strings="filtered" :info="path" :headers="headers"
                            :class="{'loading':loading===true}"></FileViewer>

            </div>
        </div>

    </div>
</template>
