export default [
    { path: '/', redirect: '/i18n' },

    {
        path: '/i18n',
        name: 'i18n',
        component: require('./screens/i18n/index').default,
    },

    {
        path: '/L10n',
        name: 'L10n',
        component: require('./screens/L10n/index').default,
        props: (route) => ({hash: route.hash.slice(1)})
    },

];
