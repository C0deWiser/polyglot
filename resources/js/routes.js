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
    //
    // {
    //     path: '/L10n/:locale',
    //     component: require('./screens/L10n/locale').default,
    //     children: [
    //         {
    //             path: ':namespace',
    //             name: 'L10n-namespace',
    //             component: require('./screens/L10n/namespace').default,
    //             children: [
    //                 {
    //                     path: ':domain',
    //                     name:'L10n-domain',
    //                     component:require('./screens/L10n/domain').default
    //                 }
    //             ]
    //         }
    //     ],
    // },
];
