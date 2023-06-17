<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import {
    PlusCircleIcon,
    ArrowRightOnRectangleIcon,
    XCircleIcon,
} from "@heroicons/vue/24/outline";
import { setTokenExpirationTimer } from "../../auth.js";

import { getAllInventory, getAllMember } from "../../util/getData.js";
import { logout } from "../../util/authUtils.js"

const imagePath = ref("/logo/logo-v1.png");
const user_data = JSON.parse(sessionStorage.getItem("user_data"));
const route = useRouter();

let inventory = ref([]);
let categories = ref([]);
let selectedProducts = ref([]);

let members = [];
let selectedMember = ref('');
let filteredMembers = ref([]);
let searchQuery = ref('');

let subTotal = ref(0);
let total = ref(0);
let discount = ref(0);

let isShowingMemberModal = ref(false);

let transactionData = [];

/* token credentials */
const headers = {
    Authorization: `Bearer ${user_data.authorization.token}`,
};
const signature = {
    headers,
    withCredentials: true,
};

/* on mount */
onMounted(async () => {
    inventory.value = await getAllInventory(signature);
    members = await getAllMember('cashier', signature);
    detectCategories();
    memberSearch(); //store all members into filteredMembers array
    setTokenExpirationTimer(logout);
});

/* API Calls */
const submitTransaction = async () => {
    try {
        const headers = {
            Authorization: `Bearer ${user_data.authorization.token}`,
        };
        const options = {
            headers,
            withCredentials: true,
        };

        const response_to_transaction = await api.post("api/transaction/add_transaction", options);

    } catch (error) {
        alert(`Error terjadi ketika menambah transaksi, alasan: ${error}`);
        console.error(error);
    }
}

const detectCategories = () => {
    inventory.value.forEach((item) => {
        if (!categories.value.includes(item.category)) {
            categories.value.push(item.category);
        }
    });
}
/* views formatting */
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
    if (
        !selectedProducts.value.some(
            (selectedProducts) => selectedProducts.id === item.id
        )
    ) {
        item.Qty = 1;
        selectedProducts.value.push(item);
    }
};

const calculateItemsSubTotal = (item) => {
    item.subTotal = item.price * item.Qty;
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

const selectMember = (memberName) => {
    selectedMember.value = memberName;
    console.log(selectedMember.value);
}
</script>

<template>
    <div class="flex flex-col min-h-screen">
        <header class="border border-b">
            <div class="flex items-center text-lg ml-5 mr-10 justify-between">
                <span class="flex flex-row gap-5 items-center">
                    <a href="#" class="my-2 border-r border-black"><img :src="imagePath" style="width: 200px; height: 65px"
                            class="object-cover object-[50%_48%]" /></a>
                    <p>CASHIER</p>
                </span>

                <div class="flex flex-cols gap-5 items-center justify-end">
                    <a>Home</a>
                    <a>Products</a>
                    <a>Stores</a>
                    <button @click="logout(route, 'cashier', signature)"
                        class="border-2 border-red-500 text-red-500 rounded-lg flex flex-row gap-1 items-center justify-center px-3 py-2 group hover:bg-red-500 hover:text-white hover:font-semibold">
                        <ArrowRightOnRectangleIcon class="w-6 h-6" />
                        <p>Logout</p>
                    </button>
                </div>
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
                                    <th class="text-start pb-2 min-w-[35%] max-w-[35%]">
                                        Nama Produk
                                    </th>
                                    <th class="text-center pb-2 min-w-[20%] max-w-[20%]">
                                        Harga Satuan
                                    </th>
                                    <th class="text-center pb-2 min-w-[20%] max-w-[20%]">
                                        QTY
                                    </th>
                                    <th class="text-end pb-2 min-w-[25%] max-w-[25%]">
                                        Total
                                    </th>
                                </tr>
                            </thead>
                            <tbody v-for="product in selectedProducts" :key="selectedProducts.id">
                                <tr>
                                    <td class="text-start pb-2">
                                        {{ product.name }}
                                    </td>
                                    <td class="text-center pb-2">
                                        {{ formatPrice(product.price) }}
                                    </td>
                                    <td class="text-center pb-2">
                                        <input type="number" v-model="product.Qty" :min="0"
                                            class="border focus:border-blue-500 text-center p-1 min-w-[40%] max-w-[40%]"
                                            @input="
                                                calculateItemsSubTotal(product);
                                            calculateAllSubTotal(product);
                                            calculateTotal(
                                                subTotal,
                                                discount
                                            );
                                            " />
                                    </td>
                                    <td class="text-end pb-2">
                                        {{ formatPrice(product.subTotal || 0) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </section>
                </div>
                <div class="flex flex-row justify-between items-center py-2">
                    <span
                        class="flex justify-around items-center border w-full h-full me-10 bg-black text-white text-2xl cursor-pointer"
                        @click="showMemberModal()">
                        <p>Add Member</p>
                        <PlusCircleIcon class="w-10 h-10" />
                    </span>
                    <!-- MEMBER MODAL -->
                    <span :style="{
                        '--from-x': '50rem',
                        '--target-x': '-3rem',
                    }" :class="{
    'opacity-90 fixed animate-slideIn':
        isShowingMemberModal,
    'hidden':
        !isShowingMemberModal,
}" class="p-5 border-2 border-black rounded-tr-2xl rounded-br-2xl bg-white top-0 w-[47rem] h-screen">
                        <div class="w-full h-full flex gap-5 flex-col">
                            <span id="close" class="flex group justify-end bg-purple-300 cursor-pointer"
                                @click="closeMemberModal()">
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
                                <span @click="selectMember(member.name)" class="bg-blue-500 px-5 py-2 cursor-pointer">
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
                    @click="submitTransaction()">PLACE ORDER</span>
            </section>
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
                            <p class="text-xl text-center">
                                {{ product.name }}
                            </p>
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
