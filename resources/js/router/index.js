import { createRouter, createWebHistory } from "vue-router";
import customer_home from "../front-end/customer/home.vue";

import cashier_login from "../front-end/cashier/login.vue";
import cashier_dashboard from "../front-end/cashier/dashboard.vue";
import cashier_transaction_record from "../front-end/cashier/transaction_records.vue";
import cashier_register_member from "../front-end/cashier/member_register.vue";
import transaction_checkout from "../front-end/cashier/checkout.vue";

import admin_login from "../front-end/admin/login.vue";
import admin_dashboard from "../front-end/admin/dashboard.vue";
import admin_inventory_management from "../front-end/admin/inventory_management.vue";
import admin_add_inventory from "../front-end/admin/add_inventory.vue";

import notFound from "../front-end/notFound.vue";

const routes = [
    {
        path: "/",
        name: "customer_home",
        component: customer_home,
    },
    /* Cashier */
    {
        path: "/cashier/login",
        name: "cashier_login",
        component: cashier_login,
    },
    {
        path: "/cashier/dashboard",
        name: "cashier_dashboard",
        component: cashier_dashboard,
        beforeEnter: requireCashierRole
    },
    {
        path: "/cashier/transaction_record",
        name: "cashier_transaction_record",
        component: cashier_transaction_record,
        beforeEnter: requireCashierRole
    },
    {
        path: "/transaction/checkout",
        name: "transaction_checkout",
        component: transaction_checkout,
        beforeEnter: requireCashierRole
    },
    {
        path: "/cashier/transaction_record",
        name: "cashier_transaction_record",
        component: cashier_transaction_record,
        beforeEnter: requireCashierRole
    },
    {
        path: "/cashier/register_member",
        name: "cashier_register_member",
        component: cashier_register_member,
        beforeEnter: requireCashierRole
    },
    /* Admin */
    {
        path: "/admin/login",
        name: "admin_login",
        component: admin_login,
    },
    {
        path: "/admin/dashboard",
        name: "admin_dashboard",
        component: admin_dashboard,
        beforeEnter: requireAdminRole
    },
    {
        path: "/admin/inventory_management",
        name: "admin_inventory_management",
        component: admin_inventory_management,
        beforeEnter: requireAdminRole
    },
    {
        path: "/admin/add_inventory",
        name: "admin_add_inventory",
        component: admin_add_inventory,
        beforeEnter: requireAdminRole
    },
    /* Not Found Handler */
    {
        path: "/:anyPath(.*)*",
        component: notFound,
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

function requireCashierRole(to, from, next) {
    const user = JSON.parse(sessionStorage.getItem('user_data')); // get the authenticated user
    if (!user) {
        alert("please login first");
        next('/cashier/login'); // redirect to login page if the user is not authenticated (no data in local storage)
    } else if (user.data.role !== 'Cashier') {
        alert('Access Denied, Sorry!'); // redirect to access denied page if the user does not have the correct role
    } else {
        next(); // allow access to the route
    }
}

function requireAdminRole(to, from, next) {
    const user = JSON.parse(sessionStorage.getItem('user_data')); // get the authenticated user
    if (!user) {
        alert("please login first");
        next('/admin/login'); // redirect to login page if the user is not authenticated (no data in local storage)
    } else if (user.data.role !== 'Admin') {
        alert('Access Denied, Sorry!'); // redirect to access denied page if the user does not have the correct role
    } else {
        next(); // allow access to the route
    }
}


export default router;
