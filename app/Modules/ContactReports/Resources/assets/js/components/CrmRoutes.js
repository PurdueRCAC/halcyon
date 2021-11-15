import CrmTable from './components/CrmTable.vue';
import CrmTableServerSide from './components/CrmTableServerSide.vue';
import CrmForm from './components/CrmForm.vue';

const locales = window.Halcyon.locales;

export default [
    {
        path: '/admin/contactreports',
        name: 'admin.contactreports.index',
        component: CrmTableServerSide,
    },
    {
        path: '/admin/contactreports/create',
        name: 'admin.contactreports.create',
        component: CrmForm,
        props: {
            locales,
            pageTitle: 'create page',
        },
    },
    {
        path: '/admin/contactreports/edit/:pageId',
        name: 'admin.contactreports.edit',
        component: CrmForm,
        props: {
            locales,
            pageTitle: 'edit page',
        },
    },
];
