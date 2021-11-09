import Vue from 'vue';
import Base from './base';
import axios from 'axios';
import Routes from './routes';
import VueRouter from 'vue-router';
import VueJsonPretty from 'vue-json-pretty';

window.Popper = require('popper.js').default;

try {
    window.$ = window.jQuery = require('jquery');

    require('bootstrap');
} catch (e) {}

let token = document.head.querySelector('meta[name="csrf-token"]');

axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}

Vue.use(VueRouter);

Vue.prototype.$http = axios.create();

window.Polyglot.basePath = '/' + window.Polyglot.path;

let routerBasePath = window.Polyglot.basePath + '/';

if (window.Polyglot.path === '' || window.Polyglot.path === '/') {
    routerBasePath = '/';
    window.Polyglot.basePath = '';
}

const router = new VueRouter({
    routes: Routes,
    mode: 'history',
    base: routerBasePath,
});

Vue.component('vue-json-pretty', VueJsonPretty);
Vue.component('alert', require('./components/Alert.vue').default);

Vue.mixin(Base);

Vue.directive('tooltip', function (el, binding) {
    $(el).tooltip({
        title: binding.value,
        placement: binding.arg,
        trigger: 'hover',
    });
});

new Vue({
    el: '#polyglot',

    router,

    data() {
        return {
            alert: {
                type: null,
                autoClose: 0,
                message: '',
                confirmationProceed: null,
                confirmationCancel: null,
            },
        };
    },
});
