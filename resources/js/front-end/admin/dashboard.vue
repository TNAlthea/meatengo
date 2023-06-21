<script setup>
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
// import { setTokenExpirationTimer } from '../../auth.js';
import { logout } from '../../util/authUtils.js';
import store from '../../util/store.js';
import api from '../../api.js';

/* Hero Icons */
import { HomeIcon, PlusCircleIcon, ArrowRightOnRectangleIcon, RectangleStackIcon, ShoppingBagIcon } from '@heroicons/vue/24/outline';
import { HomeIcon as SolidHomeIcon, PlusCircleIcon as SolidPlusCircleIcon, ArrowRightOnRectangleIcon as SolidArrowRightOnRectangleIcon, RectangleStackIcon as SolidRectangleStackIcon } from '@heroicons/vue/24/solid';

const imagePath = ref('/logo/logo-v1.png');
const route = useRouter();
const user_data = JSON.parse(sessionStorage.getItem("user_data"));
const headers = {
    Authorization: `Bearer ${user_data.authorization.token}`,
};
const signature = {
    headers,
    withCredentials: true,
};
const tokenExpiry = store.getters.getLoginTime + 6 * 60 * 60 * 1000; //expiration time of token is 6 hours from login


onMounted(function async (){
})
</script>

<template>
    <body class="flex">
        <section id="sideNavBar" class="shrink h-screen w-1/5 text-lg py-10 flex flex-col justify-between">
            <div class="flex flex-col gap-10 items-center  py-10 text-xl h-full border-r-2">
                <span class="flex justify-center items-center border-b border-slate-500 mx-5">
                    <img :src="imagePath" class="object-cover h-28 w-96">
                </span>
                <nav class="flex flex-col gap-8 justify-start items-start ">
                    <span class="flex flex-row gap-5 py-3 px-5 items-center font-semibold group hover:bg-blue-500 hover:rounded-full hover:!text-white">
                        <HomeIcon class="h-8 w-8 text-blue-500 group-hover:text-white" />
                        <router-link to="/admin/dashboard" class="pb-1 group-hover:text-white">Dashboard</router-link>
                    </span>

                    <span class="flex flex-row gap-5 py-3 px-5 items-center group hover:bg-blue-500 hover:rounded-full hover:!text-white">
                        <RectangleStackIcon class="h-6 w-6 text-blue-500 group-hover:text-white" />
                        <router-link to="/admin/inventory_management" class="pb-1 group-hover:text-white">Inventory Management</router-link>
                    </span>
                    <span class="flex flex-row gap-5 py-3 px-5 items-center group hover:bg-blue-500 hover:rounded-full hover:!text-white">
                        <PlusCircleIcon class="h-6 w-6 text-blue-500 group-hover:text-white" />
                        <router-link to="/admin/add_inventory" class="pb-1 group-hover:text-white">Add Inventory</router-link>
                    </span>
                    <span class="flex flex-row gap-5 py-3 px-5 items-center group hover:bg-red-500 hover:rounded-full hover:!text-white">
                        <ArrowRightOnRectangleIcon class="h-6 w-6 text-red-500 group-hover:text-white" />
                        <a href="#" @click="logout('admin', signature, route)" class="pb-1 text-red-500 group-hover:text-white">Logout</a>
                    </span>
                </nav>
            </div>
        </section>
        <section class="grow flex flex-col justify-start items-start p-10">

        </section>
    </body>
</template>