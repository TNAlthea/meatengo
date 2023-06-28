<script setup>
import { onMounted, ref, computed, watchEffect } from 'vue';
import { useRouter } from 'vue-router';
import store from '../../util/store.js';
import api from '../../api.js';

import { logout } from '../../util/authUtils.js';
import { getAllInventory } from '../../util/getData.js';
/* Hero Icons */
import { HomeIcon, PlusCircleIcon, ArrowRightOnRectangleIcon, RectangleStackIcon, XCircleIcon, ArrowsUpDownIcon } from '@heroicons/vue/24/outline';
import { HomeIcon as SolidHomeIcon, PlusCircleIcon as SolidPlusCircleIcon, ArrowRightOnRectangleIcon as SolidArrowRightOnRectangleIcon, RectangleStackIcon as SolidRectangleStackIcon } from '@heroicons/vue/24/solid';

const imagePath = ref('/logo/logo-v1.png');
const route = useRouter();
const user_data = JSON.parse(sessionStorage.getItem("user_data"));
const tokenExpiry = store.getters.getLoginTime + 6 * 60 * 60 * 1000;

let inventory = ref([]);
let filteredInventory = ref([]);
let sizeOfFilteredInventory = ref(0);
let categories = new ref([]);
let sortOrder = ref('ascending'); // set ascending as the default sort 
let sortCol = ref('index'); // set index as default sort
let selectedItem = ref([])
let showDetailModal = ref(false);
let isEditing = ref(false);

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

const headers = {
    Authorization: `Bearer ${user_data.authorization.token}`,
};
const signature = {
    headers,
    withCredentials: true,
};

onMounted(async () => {
    const response = await getAllInventory('admin', signature, route);
    if (response) {
        try {
            inventory.value = response;

            inventory.value.forEach((item) => {
                if (!categories.value.includes(item.category)) {
                    categories.value.push(item.category);
                }
            });
        } catch (error) {
            alert(`Error terjadi ketika memuat inventori produk, alasan: ${error}`);
            console.error(error);
        }
    }
    clearFilterByCategory();
})

/* API calls */
const deleteProduct = async (product) => {
    try {
        if (Date.now() < tokenExpiry) {
            const confirmation = confirm(`Apakah anda yakin untuk menghapus produk ini (${product.name})?`);

            if (confirmation) {
                const response = await api.post(`api/inventory/delete/${product.id}`, null, signature);
                alert(`${product.name} sukses dihapus dari database!`);
                location.reload()
            }
        } else {
            route.push({ path: '/admin/login' });
            alert("Token expired, please login again.");
        }
    } catch (error) {
        if (error.response) {
            const errorMessage = error.response.data;
            alert(`Error terjadi ketika menghapus ${product.name}. ${errorMessage.message}, alasan: ${errorMessage.reason}`);
        } else {
            alert(error)
        }
    }
}

const updateProduct = async (product) => {
    try {
        if (Date.now() < tokenExpiry) {
            const uploadData = ref([]);

            uploadData.value = JSON.parse(JSON.stringify(product));

            delete uploadData.value.image;

            const response = await api.post(`api/inventory/update/${uploadData.value.id}`, uploadData.value, signature);
            alert(`${uploadData.value.name} berhasil diupdate!`);

            //update the selectedItem after updates
            const updatedProduct = await api.get(`api/inventory/get/${uploadData.value.id}`, signature);
            selectedItem.value = updatedProduct.data.data;

            isEditing.value = false;
        } else {
            route.push({ path: '/admin/login' });
            alert("Token expired, please login again.");
        }
    } catch (error) {
        if (error.response) {
            const errorMessage = error.response.data;
            alert(`Error terjadi ketika mengupdate ${product.name}. ${errorMessage.message}, alasan: ${errorMessage.reason}`);
        } else {
            alert(error)
        }
    }
}

/* views formatting */
const formatPrice = (price) => {
    return "Rp " + price.toLocaleString('id-ID');
};

const editProduct = () => {
    isEditing.value = true
}

const cancelEdit = () => {
    isEditing.value = false
}


const seeDetail = (product) => {
    showDetailModal.value = true;
    selectedItem.value = product;
}

const closeDetail = () => {
    showDetailModal.value = false;
    isEditing.value = false;
}

const filterByCategory = (selectedCategory) => {
    filterInventory(selectedCategory);
    // sizeOfFilteredInventory.value = selectedCategory.length;
}

const clearFilterByCategory = () => {
    filterInventory('');
}

const filterInventory = (selectedCategory) => {
    if (selectedCategory === '') {
        filteredInventory.value = inventory.value
    } else {
        filteredInventory.value = inventory.value.filter((inventory) => inventory.category === selectedCategory);
    }
    sizeOfFilteredInventory = filteredInventory.value.length;
}

const sortTable = (key) => {
    sortCol.value = key;
    switch (sortOrder.value) {
        // if current sort order is asc, sort to desc
        case 'ascending':
            filteredInventory.value.sort((a, b) => {
                if (a[key] < b[key]) return -1;
                if (a[key] > b[key]) return 1;
                return 0;
            });
            sortOrder.value = 'descending'
            break;
        default:
            filteredInventory.value.sort((a, b) => {
                if (a[key] < b[key]) return 1;
                if (a[key] > b[key]) return -1;
                return 0;
            });
            sortOrder.value = 'ascending'
            break;
    }
}

const getSortIconClass = (key) => {
    if ((sortOrder.value === 'ascending') && (sortCol.value === key)) return 'text-black';
    else if ((sortOrder.value === 'descending') && (sortCol.value === key)) return 'text-black -scale-x-100';
    else return 'text-gray-500';
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
                        class="flex flex-row gap-5 py-3 px-5 items-center font-semibold group hover:bg-blue-500 hover:rounded-full hover:!text-white">
                        <RectangleStackIcon class="h-8 w-8 text-blue-500 group-hover:text-white" />
                        <router-link to="/admin/inventory_management" class="pb-1 group-hover:text-white">Inventory
                            Management</router-link>
                    </span>
                    <span
                        class="flex flex-row gap-5 py-3 px-5 items-center group hover:bg-blue-500 hover:rounded-full hover:!text-white">
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
                <div class="grid grid-flow-row gap-5">
                    <span id="categories" class="grid grid-flow-col gap-5 items-center">
                        <span>Kategori</span>
                        <button class="border border-black rounded-lg p-2" @click="clearFilterByCategory();">Semua</button>
                        <button v-for="category in categories" :key="category" @click="filterByCategory(category)"
                            class="border border-black rounded-lg p-2">
                            {{ category }}
                        </button>
                    </span>

                    <table v-if="filteredInventory.length > 0"
                        class="border table-auto border-separate border-spacing-x-1 border-spacing-y-2 border-black justify-center items-center text-left text-xl w-full">
                        <thead class="bg-green-100">
                            <tr>
                                <th class="font-semibold pl-2">No</th>
                                <th>
                                    <span class="font-semibold p-2 flex flex-row justify-between">
                                        Nama
                                        <ArrowsUpDownIcon class="h-7 w-7 cursor-pointer" :class="getSortIconClass('name')"
                                            @click="sortTable('name')" aria-label="sort-by-name" />
                                    </span>
                                </th>
                                <th>
                                    <span class="font-semibold p-2 flex flex-row justify-between">
                                        Harga
                                        <ArrowsUpDownIcon class="h-7 w-7 cursor-pointer" :class="getSortIconClass('price')"
                                            @click="sortTable('price')" aria-label="sort-by-price" />
                                    </span>
                                </th>
                                <th>
                                    <span class="font-semibold pl-2 flex flex-row justify-between">
                                        Stok
                                        <ArrowsUpDownIcon class="h-7 w-7 cursor-pointer" :class="getSortIconClass('stock')"
                                            @click="sortTable('stock')" aria-label="sort-by-stock" />
                                    </span>
                                </th>
                                <th>
                                    <span class="font-semibold pl-2 flex flex-row justify-between">
                                        Terjual
                                        <ArrowsUpDownIcon class="h-7 w-7 cursor-pointer" :class="getSortIconClass('sold')"
                                            @click="sortTable('sold')" aria-label="sort-by-sold" />
                                    </span>
                                </th>
                                <th class="font-semibold pl-2">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(item, index) in filteredInventory" :key="filteredInventory.id">
                                <td v-if="index % 2 === 1" class="border pl-2">{{ index + 1 }}</td>
                                <td v-else class="border bg-slate-200 pl-2">{{ index + 1 }}</td>

                                <td v-if="index % 2 === 1" class="border pl-2">{{ item.name }}</td>
                                <td v-else class="border bg-slate-200 pl-2">{{ item.name }}</td>

                                <td v-if="index % 2 === 1" class="border pl-2">{{ formatPrice(item.price) }}</td>
                                <td v-else class="border bg-slate-200 pl-2">{{ formatPrice(item.price) }}</td>

                                <td v-if="index % 2 === 1" class="border pl-2">{{ item.stock }}</td>
                                <td v-else class="border bg-slate-200 pl-2">{{ item.stock }}</td>

                                <td v-if="index % 2 === 1" class="border pl-2">{{ item.sold }}</td>
                                <td v-else class="border bg-slate-200 pl-2">{{ item.sold }}</td>

                                <td>
                                    <p v-if="index % 2 === 1"
                                        class="border p-2 text-center rounded-lg bg-green-100 cursor-pointer"
                                        @click="seeDetail(item)">Lihat Detail</p>
                                    <p v-else class="border p-2 text-center rounded-lg bg-green-300 cursor-pointer"
                                        @click="seeDetail(item)">Lihat Detail</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <span v-else
                        class="text-5xl flex justify-center items-center absolute top-1/2 left-1/2 -translate-x-1/3">
                        <p>TIDAK ADA PRODUK DALAM INVENTORY</p>
                    </span>
                </div>
                <div v-if="showDetailModal" id="detailModal"
                    class="absolute top-0 right-0 bg-white border-2 border-slate-400 min-h-screen w-1/2 lg:w-1/3">
                    <span id="close" class="inline-block group cursor-pointer relative top-5 left-3" @click="closeDetail()">
                        <span class="flex flex-row items-center hover:text-red-500">
                            <XCircleIcon class="w-12 h-12" />
                            <p
                                class="text-2xl transition ease-in delay-50 opacity-0 -translate-x-0 group-hover:translate-x-1 group-hover:opacity-100">
                                Tutup</p>
                        </span>
                    </span>
                    <div class="relative p-5">
                        <h1 class="border-b border-black pt-10 pb-3 text-2xl">Detail Produk</h1>
                        <section class="py-5">
                            <span class="flex justify-center pt-5 pb-10">
                                <img :src="`/storage/images/inventory/${selectedItem.image}`"
                                    class="w-72 h-72 object-cover" />
                            </span>
                            <span class="flex flex-row justify-between border-b border-slate-300 mx-10 mb-5 text-xl">
                                <p>ID Produk</p>
                                <p>{{ selectedItem.id }}</p>
                            </span>
                            <span class="flex flex-row justify-between border-b border-slate-300 mx-10 mb-5 text-xl">
                                <p>Nama Produk</p>
                                <input v-if="isEditing" type="text" v-model="selectedItem.name"
                                    class="px-2 py-1 text-right" />
                                <p v-else>{{ selectedItem.name }}</p>
                            </span>
                            <span class="flex flex-row justify-between border-b border-slate-300 mx-10 mb-5 text-xl">
                                <p>Kategori</p>
                                <select v-if="isEditing" v-model="selectedItem.category" class="bg-white text-right">
                                    <option v-for="item in categoriesOption" :value="item.name"
                                        :selected="item.valuename === selectedItem.category">{{ item.name }}</option>
                                </select>
                                <p v-else>{{ selectedItem.category }}</p>
                            </span>
                            <span class="flex flex-row justify-between border-b border-slate-300 mx-10 mb-5 text-xl">
                                <p>Harga</p>
                                <input v-if="isEditing" type="number" v-model="selectedItem.price"
                                    class="px-2 py-1 text-right" />
                                <p v-else>{{ formatPrice(selectedItem.price) }}</p>
                            </span>
                            <span class="flex flex-row justify-between border-b border-slate-300 mx-10 mb-5 text-xl">
                                <p>Stok</p>
                                <input v-if="isEditing" type="number" v-model="selectedItem.stock"
                                    class="px-2 py-1 text-right" />
                                <p v-else>{{ selectedItem.stock }}</p>
                            </span>
                            <span class="flex flex-row justify-between border-b border-slate-300 mx-10 mb-5 text-xl">
                                <p>Terjual</p>
                                <input v-if="isEditing" type="number" v-model="selectedItem.sold" class="px-2 text-right" />
                                <p v-else>{{ selectedItem.sold }}</p>
                            </span>
                            <div>
                                <div v-if="isEditing" class="flex justify-between px-10">
                                    <span
                                        class="w-60 h-16 bg-red-500 rounded-lg flex items-center justify-center text-2xl text-white hover:cursor-pointer"
                                        @click="cancelEdit()">Cancel</span>
                                    <span
                                        class="w-60 h-16 bg-green-500 rounded-lg flex items-center justify-center text-2xl text-white hover:cursor-pointer"
                                        @click="updateProduct(selectedItem)">Update!</span>
                                </div>
                                <div v-else class="flex justify-between px-10">
                                    <span
                                        class="w-60 h-16 bg-red-500 rounded-lg flex items-center justify-center text-2xl text-white hover:cursor-pointer"
                                        @click="deleteProduct(selectedItem)">Hapus</span>
                                    <span
                                        class="w-60 h-16 bg-blue-500 rounded-lg flex items-center justify-center text-2xl text-white hover:cursor-pointer"
                                        @click="editProduct(selectedItem)">Edit Data</span>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </section>
    </body>
</template>