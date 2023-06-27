<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import {
    PlusCircleIcon,
    ArrowRightOnRectangleIcon,
    XCircleIcon,
    XMarkIcon,
    UserCircleIcon
} from "@heroicons/vue/24/outline";

import { getAllInventory, getAllMember } from "../../util/getData.js";
import store from "../../util/store.js";
import { logout } from "../../util/authUtils.js";
import api from "../../api";

const imagePath = ref("/logo/logo-v1.png");
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

/* on mount */
onMounted(async () => {
    inventory.value = await getAllInventory('cashier', signature, route);
    members = await getAllMember("cashier", signature, route);
    detectCategories();
    memberSearch(); //store all members into filteredMembers array
});

let inventory = ref([]);
let categories = ref([]);
let selectedProducts = ref([]);

let members = [];
let selectedMember = ref({
    id: "ed90502c-001c-4c70-bbf9-830c421184e7",
    name: "guest",
    points: 0,
    telephone: "08123123123"
});
let filteredMembers = ref([]);
let searchQuery = ref("");

let subTotal = ref(0);
let total = ref(0);
let discount = ref(0);

let isShowingMemberModal = ref(false);

/* object to store data that will be send into database through API */
let transactionData = {
    // set default value
    total_before_discount: 0,
    discount: 0,
    total: 0,
    user_id: "ed90502c-001c-4c70-bbf9-830c421184e7",
    cashier_id: user_data.data.id,
};

/* redirect */
const goCheckout = () => {
    if (Date.now() < tokenExpiry) {
        if (selectedProducts.value.length > 0) {
            if (selectedMember) transactionData.user_id = selectedMember.value.id;
            transactionData.total_before_discount = subTotal.value;
            transactionData.discount = discount.value;
            transactionData.total = total.value;

            localStorage.setItem('transactionData', JSON.stringify(transactionData));
            localStorage.setItem('productList', JSON.stringify(selectedProducts.value));

            route.push({ path: "/transaction/checkout" });
        } else {
            alert(
                "Keranjang kosong! Harap isi keranjang terlebih dahulu sebelum melakukan transaksi."
            );
        }
    } else {
        route.push({ path: "/cashier/login" });
        alert("Token expired, please login again.");
    }
}

/* views formatting */
const detectCategories = () => {
    inventory.value.forEach((item) => {
        if (!categories.value.includes(item.category)) {
            categories.value.push(item.category);
        }
    });
};

const formatPrice = (price) => {
    return "Rp " + price.toLocaleString("id-ID");
};

const getDate = () => {
    const date = new Date();
    const days = [
        "Minggu",
        "Senin",
        "Selasa",
        "Rabu",
        "Kamis",
        "Jumat",
        "Sabtu",
    ];
    const dayIndex = date.getDay();
    const day = days[dayIndex];
    const formattedDate = `${day}, ${date.getDate()}/${date.getMonth() + 1
        }/${date.getFullYear()}`;

    return formattedDate;
};

const addItem = (item) => {
    if (item.stock > 0) {
        if (!selectedProducts.value.some((selectedProducts) => selectedProducts.id === item.id)) {
            item.quantity = 1;
            selectedProducts.value.push(item);
        }
    } else {
        alert(`stok produk ${item.name} kosong!`);
    }
};

const deleteItem = (item) => {
    total.value -= item.subTotal;
    selectedProducts.value = selectedProducts.value.filter((products) => products.id !== item.id);
}

const calculateItemsSubTotal = (item) => {
    item.subTotal = item.price * item.quantity;
};

const calculateAllSubTotal = () => {
    subTotal.value = 0;
    selectedProducts.value.forEach((item) => {
        subTotal.value += item.subTotal || 0;
    });

    return subTotal.value;
};

const calculateTotal = (subTotal, discount) => {
    total.value = subTotal - subTotal * ((discount || 0) / 100);
    return total.value;
};

const showMemberModal = () => {
    isShowingMemberModal.value = true;
};

const closeMemberModal = () => {
    isShowingMemberModal.value = false;
};

const memberSearch = () => {
    const query = searchQuery.value.toLowerCase();

    filteredMembers.value = members.filter((member) => {
        return member.name.toLowerCase().includes(query);
    });
};

const selectMember = (member) => {
    selectedMember.value.id = member.id;
    selectedMember.value.name = member.name;
    selectedMember.value.points = member.points;
    selectedMember.value.telephone = member.telephone;
    console.log(selectedMember.value);
};
</script>

<template>
    <div class="flex flex-col min-h-screen">
        <header class="border border-b">
            <div class="flex items-center mr-5 text-lg justify-between">
                <span class="flex flex-row gap-5 items-center">
                    <a href="#" class="my-2 border-black"><img :src="imagePath" style="width: 200px; height: 65px"
                            class="object-cover object-[50%_48%]" /></a>
                </span>

                <div class="flex flex-cols gap-10 p-2 items-center rounded-full shadow-inner shadow-slate-300">
                    <router-link to="/cashier/dashboard"
                        class="font-semibold hover:rounded-full hover:bg-slate-200 p-3">Home</router-link>
                    <router-link to="/cashier/transaction_record" class="hover:rounded-full hover:bg-slate-200 p-3">Histori
                        Transaksi</router-link>
                    <router-link to="/cashier/dashboard" class="hover:rounded-full hover:bg-slate-200 p-3">Daftar
                        Member</router-link>
                </div>

                <button @click="logout('cashier', signature, route)"
                    class="border-2 border-red-500 text-red-500 rounded-lg flex flex-row gap-1 items-center px-3 py-2 group hover:bg-red-500 hover:text-white hover:font-semibold">
                    <ArrowRightOnRectangleIcon class="w-6 h-6" />
                    <p>Logout</p>
                </button>
            </div>
        </header>

        <div class="flex flex-row flex-grow overflow-y-auto">
            <section class="flex flex-col gap-3 bg-gray-300 w-[40%] p-10 overflow-y-auto shrink">
                <div class="flex flex-row justify-between items-center">
                    <p>Transaksi Nomor #012345</p>
                    <p>{{ getDate() }}</p>
                </div>
                <div class="bg-white rounded px-5 py-2 items-center">
                    <span class="flex flex-row justify-between text-xl pb-1">
                        <p>Total sebelum diskon</p>
                        <p>{{ formatPrice(subTotal) }}</p>
                    </span>
                    <span class="flex flex-row justify-between font-semibold text-4xl">
                        <p>Total</p>
                        <p>{{ formatPrice(total) }}</p>
                    </span>
                </div>
                <div class="flex flex-col justify-between bg-white rounded p-5 min-h-[50vh]">
                    <section>
                        <table class="table-fixed min-w-full max-w-full">
                            <thead>
                                <tr>
                                    <th class="text-start">
                                        Nama Produk
                                    </th>
                                    <th class="text-center">
                                        Harga Satuan
                                    </th>
                                    <th class="text-center">
                                        QTY
                                    </th>
                                    <th class="text-center">
                                        Total Harga Produk
                                    </th>
                                    <th class="text-end">Hapus?</th>
                                </tr>
                            </thead>
                            <tbody v-for="product in selectedProducts" :key="selectedProducts.id">
                                <tr class="border-b">
                                    <td class="text-start">
                                        {{ product.name }}
                                    </td>
                                    <td class="text-center">
                                        {{ formatPrice(product.price) }}
                                    </td>
                                    <td class="text-center">
                                        <input type="number" v-model="product.quantity" :min="0" :max="product.stock"
                                            class="border focus:border-blue-500 text-center p-1 w-20" @input="
                                                calculateItemsSubTotal(product);
                                            calculateAllSubTotal(product);
                                            calculateTotal(
                                                subTotal,
                                                discount
                                            );
                                            " />
                                    </td>
                                    <td class="text-center">
                                        {{ formatPrice(product.subTotal || 0) }}
                                    </td>
                                    <td class="flex justify-center items-center mt-1 text-slate-500 border-2 border-slate-500 hover:border-red-500 hover:text-red-500">
                                        <XMarkIcon class="w-8 h-8 cursor-pointer"
                                            @click="deleteItem(product)" />
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </section>
                </div>
                <div class="flex flex-row justify-between items-center py-2">
                    <span v-if="selectedMember.name=== 'guest'"
                        class="rounded-md flex justify-around items-center border w-full h-full me-10 bg-black text-white text-2xl cursor-pointer"
                        @click="showMemberModal()">
                        <p>Add Member</p>
                        <PlusCircleIcon class="w-10 h-10" />
                    </span>
                    <span v-else
                        class="rounded-md flex gap-5 justify-center items-center border-2 border-black w-full h-full me-10 bg-white text-2xl cursor-pointer"
                        @click="showMemberModal()">
                        <UserCircleIcon class="w-10 h-10"/>
                        <p class="pb-2">{{ `${selectedMember.name} (poin: ${selectedMember.points})` }}</p>
                    </span>
                    <!-- MODAL -->
                    <!-- MEMBER MODAL -->
                    <span :style="{
                        '--from-x': '50rem',
                        '--target-x': '-3rem',
                    }" :class="{
    'opacity-90 fixed animate-slideIn':
        isShowingMemberModal,
    hidden: !isShowingMemberModal,
}" class="p-5 border-2 border-black rounded-tr-2xl rounded-br-2xl bg-white top-0 w-[47rem] h-screen">
                        <div class="w-full h-full flex gap-5 flex-col">
                            <span id="close" class="flex group justify-end cursor-pointer" @click="closeMemberModal()">
                                <span class="flex flex-row items-center hover:text-red-500">
                                    <p
                                        class="text-2xl transition ease-in delay-50 opacity-0 -translate-x-0 group-hover:-translate-x-1 group-hover:opacity-100">
                                        Tutup
                                    </p>
                                    <XCircleIcon class="w-12 h-12" />
                                </span>
                            </span>
                            <span class="w-full flex items-center justify-center">
                                <input type="text" class="w-3/4 border border-black rounded-xl px-5" v-model="searchQuery"
                                    @keyup="memberSearch" />
                            </span>
                            <div v-for="member in filteredMembers" :key="member.id"
                                class="flex flex-row w-full justify-center items-center">
                                <span @click="selectMember(member)" class="bg-blue-500 px-5 py-2 cursor-pointer">
                                    {{ member.name }}
                                </span>
                            </div>
                        </div>
                    </span>

                    <span class="grid grid-cols-2 gap-5 justify-end items-center">
                        <p class="text-xl text-end">
                            Add Discount (dalam persen)
                        </p>
                        <input type="number" class="py-1 w-full text-3xl px-3" v-model="discount" :min="0" :max="100"
                            @input="calculateTotal(subTotal, discount)" />
                    </span>
                </div>
                <span
                    class="h-full flex justify-center items-center p-4 bg-orange-500 text-white text-2xl font-semibold cursor-pointer"
                    @click="goCheckout()">Checkout</span>
            </section>
            <!-- Product Section -->
            <section class="bg-white grow p-5">
                <div class="flex flex-col pb-5">
                    <p class="text-3xl font-bold pb-5">Kategori Barang</p>
                    <section class="flex flex-row gap-5">
                        <div v-for="category in categories" :key="category" class="gap-5 border border-black rounded p-3">
                            <p>{{ category }}</p>
                        </div>
                    </section>
                </div>
                <div class="sm:grid sm:grid-cols-[repeat(4,minmax(100px,240px))] gap-10">
                    <div v-for="product in inventory" :key="product.id"
                        class="border border-black rounded-2xl flex flex-col gap-2 justify-between items-center cursor-pointer"
                        @click="
                            addItem(product);
                        calculateItemsSubTotal(product);
                        calculateAllSubTotal(product);
                        calculateTotal(subTotal, discount);
                        ">
                        <div class="p-3 flex flex-col justify-evenly items-center gap-5">
                            <span>
                                <img :src="`/storage/images/inventory/${product.image}`" class="h-36" />
                            </span>
                            <span class="flex flex-col justify-center items-center">
                                <p class="text-xl text-center">
                                    {{ product.name }}
                                </p>
                                <p :class="{ 'text-red-500 font-semibold': product.stock == 0 }">Stok: {{ product.stock }}</p>
                            </span>
                        </div>
                        <span class="w-full bg-orange-300 flex justify-center rounded-b-2xl py-2">
                            <p class="text-2xl font-semibold">
                                {{ formatPrice(product.price) }}
                            </p>
                        </span>
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>
