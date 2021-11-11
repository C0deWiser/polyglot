export default [
    { path: '/', redirect: '/i18n' },

    // {
    //     path: '/dashboard',
    //     name: 'dashboard',
    //     component: require('./screens/dashboard').default,
    // },

    {
        path: '/i18n',
        name: 'i18n',
        component: require('./screens/i18n/index').default,
    },

    {
        path: '/L10n',
        name: 'L10n',
        component: require('./screens/L10n/index').default,
    },
    {
        path: '/L10n/:filename',
        name: 'L10n-json',
        component: require('./screens/L10n/strings').default,
    },
    {
        path: '/L10n/:locale/:filename',
        name: 'L10n-php',
        component: require('./screens/L10n/strings').default,
    },
    {
        path: '/L10n/:locale/:category/:filename',
        name: 'L10n-po',
        component: require('./screens/L10n/strings').default,
    },

];
