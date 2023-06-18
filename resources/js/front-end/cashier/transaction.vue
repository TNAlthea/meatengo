<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router';
import { PlusCircleIcon } from '@heroicons/vue/24/outline';
// import { setTokenExpirationTimer } from '../../auth.js';
import api from '../../api';

const imagePath = ref('/logo/logo-v1.png')
const user_data = JSON.parse(sessionStorage.getItem("user_data"));
const route = useRouter();

let inventory = ref([]);
let categories = ref([]);

onMounted(async () => {
    await getAllInventory();
    setTokenExpirationTimer(logout);    
})

/* API Calls */
const getAllInventory = async () => {
    try {
        const response = await api.get('api/inventory/getAll');
        inventory.value = response.data.data;

        inventory.value.forEach((item) => {
            if (!categories.value.includes(item.category)) {
                categories.value.push(item.category);
            }
        });
    } catch (error) {
        alert(`Error terjadi ketika memuat inventori produk, alasan: ${error}`);
        console.error(error);
    }
};

const logout = async () => {
    try {
        const headers = {
            'Authorization': `Bearer ${user_data.authorization.token}`,
        };
        const options = {
            headers,
            withCredentials: true,
        };

        const response = await api.post('api/cashier/logout', null, options);
        sessionStorage.removeItem("user_data");
        route.push({ path: '/cashier/login' });
    } catch (error) {
        alert(error);
        console.error(error);
    }
}

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