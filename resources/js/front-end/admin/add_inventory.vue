<script setup>
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import store from '../../util/store.js';
import api from '../../api.js';
import { logout } from '../../util/authUtils';

/* Hero Icons */
import { HomeIcon, PlusCircleIcon, ArrowRightOnRectangleIcon, RectangleStackIcon } from '@heroicons/vue/24/outline';
import { HomeIcon as SolidHomeIcon, PlusCircleIcon as SolidPlusCircleIcon, ArrowRightOnRectangleIcon as SolidArrowRightOnRectangleIcon, RectangleStackIcon as SolidRectangleStackIcon } from '@heroicons/vue/24/solid';

const imagePath = ref('/logo/logo-v1.png');
const route = useRouter();
const previewImageUpload = ref('');
const user_data = JSON.parse(sessionStorage.getItem("user_data"));
const tokenExpiry = store.getters.getLoginTime + 6 * 60 * 60 * 1000; //expiration time of token is 6 hours from login

const categoriesOption = [
    { name: 'Buah dan Sayur', value: 'buah dan sayur' },
    { name: 'Daging dan Seafood', value: 'daging dan seafood' },
    { name: 'Dairy', value: 'dairy' },
    { name: 'Frozen Food', value: 'frozen food' },
    { name: 'Makanan Instant', value: 'makanan instant' },
    { name: 'Makanan Ringan', value: 'makanan ringan' },
    { name: 'Minuman', value: 'minuman' },
    { name: 'Lainnya', value: 'lainnya' },
]
const form = {
    name: '',
    price: 0,
    stock: 0,
    category: '',
    image: null
};

/* token credentials */
const headers = {
    Authorization: `Bearer ${user_data.authorization.token}`,
};
const signature = {
    headers,
    withCredentials: true,
};

/* API calls */
const submitInventory = async () => {
    try {
        if (Date.now() < tokenExpiry) {
            const formData = new FormData();
            formData.append('name', form.name);
            formData.append('price', form.price);
            formData.append('stock', form.stock);
            formData.append('category', form.category);
            formData.append('image', form.image);

            const response = await api.post('/api/inventory/add_inventory', formData, signature);
            console.log(response.data.data.id)
            route.push({ path: '/admin/dashboard' });

            alert(`Produk ${form.name} sukses ditambahkan ke dalam database!`);
            location.reload();
        } else {
            sessionStorage.removeItem("user_data");
            store.dispatch("clearTokenExpirationTimer");
            alert("Token expired, please login again.");
            route.push({ path: '/admin/login' });
        }
    } catch (error) {
        alert(`gagal menambahkan produk ke dalam inventory, alasan: ${error}`);
    }
};

/* Views Formatting */
function previewImage(event) {
    form.image = event.target.files[0];
    const reader = new FileReader();

    reader.onload = () => {
        previewImageUpload.value = reader.result;
    };

    if (form.image) {
        reader.readAsDataURL(form.image);
    }
}
</script>

<template>
    <body class="flex">
        <section id="sideNavBar" class="shrink h-screen w-1/5 text-lg py-10 flex flex-col justify-between">
            <div class="flex flex-col gap-10 items-center  py-10 text-xl h-full">
                <span class="flex justify-center items-center border-b border-slate-500 mx-5">
                    <img :src="imagePath" class="object-cover h-28 w-96">
                </span>
                <nav class="flex flex-col gap-8 justify-start items-start ">
                    <span
                        class="flex flex-row gap-5 py-3 px-5 items-center group hover:bg-blue-500 hover:rounded-full hover:!text-white">
                        <HomeIcon class="h-6 w-6 text-blue-500 group-hover:text-white" />
                        <router-link to="/admin/dashboard" class="pb-1 group-hover:text-white">Dashboard</router-link>
                    </span>

                    <span
                        class="flex flex-row gap-5 py-3 px-5 items-center group hover:bg-blue-500 hover:rounded-full hover:!text-white">
                        <RectangleStackIcon class="h-8 w-8 text-blue-500 group-hover:text-white" />
                        <router-link to="/admin/inventory_management" class="pb-1 group-hover:text-white">Inventory
                            Management</router-link>
                    </span>
                    <span
                        class="flex flex-row gap-5 py-3 px-5 items-center font-semibold group hover:bg-blue-500 hover:rounded-full hover:!text-white">
                        <PlusCircleIcon class="h-6 w-6 text-blue-500 group-hover:text-white" />
                        <router-link to="/admin/add_inventory" class="pb-1 group-hover:text-white">Add
                            Inventory</router-link>
                    </span>
                    <span
                        class="flex flex-row gap-5 py-3 px-5 items-center group hover:bg-red-500 hover:rounded-full hover:!text-white">
                        <ArrowRightOnRectangleIcon class="h-6 w-6 text-red-500 group-hover:text-white" />
                        <a href="#" @click="logout('admin', signature, route)"
                            class="pb-1 text-red-500 group-hover:text-white">Logout</a>
                    </span>
                </nav>
            </div>
        </section>
        <section class="grow justify-start items-start p-10 bg-slate-100">
            <div class="bg-white rounded-lg h-full p-10">
                <span class="text-3xl py-5 w-full">
                    TAMBAH PRODUK KE DALAM INVENTORI/GUDANG
                </span>
                <div class="py-10  w-1/2">
                    <form @submit.prevent="submitInventory">
                        <span class="flex flex-row gap-5 py-5 text-2xl">
                            <label class="w-40">Nama Produk</label>
                            <input type="name" v-model="form.name" name="nama_produk"
                                class="border rounded-lg grow py-1 px-3" required />
                        </span>
                        <span class="flex flex-row gap-5 py-5 text-2xl">
                            <label class="w-40">Harga</label>
                            <span class="flex gap-3 grow">
                                <p>Rp.</p>
                                <input type="number" name="price" v-model="form.price"
                                    class="border rounded-lg grow py-1 px-3" required />
                            </span>
                        </span>
                        <span class="flex flex-row gap-5 py-5 text-2xl">
                            <label class="w-40">Stok</label>
                            <input type="number" name="stock" v-model="form.stock" class="border rounded-lg grow py-1 px-3"
                                required />
                        </span>
                        <span class="flex flex-row gap-5 py-5 text-2xl">
                            <label class="w-40">Kategori</label>
                            <select class="border rounded-lg bg-white grow py-1 px-3" v-model="form.category" required>
                                <option class="bg-white" v-for="category in categoriesOption" :value="category.name">
                                    {{ category.name }}
                                </option>
                            </select>
                        </span>
                        <span class="grid grid-cols-2 gap-5 py-5 text-xl">
                            <span class="flex flex-col gap-3">
                                <label class="w-40">Foto Produk</label>
                                <input type="file" ref="fileInput" @change="previewImage" required />
                            </span>
                            <img v-if="previewImageUpload" class="bg-red-500 w-72 h-72 object-cover"
                                :src="previewImageUpload" alt="Preview" />
                        </span>
                        <span>
                            <button type="submit"
                                class="bg-slate-500 rounded-lg text-xl text-white font-semibold py-3 w-full mx-auto">Tambah
                                Produk!</button>
                        </span>
                    </form>
                </div>
            </div>
        </section>
    </body>
</template>