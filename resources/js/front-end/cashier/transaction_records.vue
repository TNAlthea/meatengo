<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router';
import { PlusCircleIcon } from '@heroicons/vue/24/outline';
import api from '../../api';
import store from '../../util/store.js';
import { logout } from '../../util/authUtils.js';

const imagePath = ref('/logo/logo-v1.png')
const user_data = JSON.parse(sessionStorage.getItem("user_data"));
const route = useRouter();

/* token credentials */
const headers = {
    Authorization: `Bearer ${user_data.authorization.token}`,
};
const signature = {
    headers,
    withCredentials: true,
};
const tokenExpiry = store.getters.getLoginTime + 6 * 60 * 60 * 1000; //expiration time of token is 6 hours from login

/* other var */
let inventory = ref([]);
let categories = ref([]);

onMounted(async () => {
    await getAllInventory();
    setTokenExpirationTimer(logout);    
})

/* API Calls */
const getAllInventory = async () => {
    try {
        if (Date.now() < tokenExpiry) {
            const response = await api.get('api/inventory/getAll', signature);
            inventory.value = response.data.data;
    
            inventory.value.forEach((item) => {
                if (!categories.value.includes(item.category)) {
                    categories.value.push(item.category);
                }
            });
        }        
    } catch (error) {
        alert(`Error terjadi ketika memuat inventori produk, alasan: ${error}`);
        console.error(error);
    }
};

/* views formatting */
const formatPrice = (price) => {
    return "Rp " + price.toLocaleString('id-ID');
};

const getDate = () => {
    const date = new Date();
    const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    const dayIndex = date.getDay();
    const day = days[dayIndex];
    const formattedDate = `${day}, ${date.getDate()}/${date.getMonth() + 1}/${date.getFullYear()}`;

    return formattedDate;
}
</script>
<template>
    <head class="border-b border-black">
        CASHIER DASHBOARD
    </head>
    <body>
        <div class="flex flex-rows">
            <section class="h-screen w-2/3">
                AA
            </section>
            <section class="h-screen w-1/3">
                BB
            </section>
        </div>
    </body>
</template>