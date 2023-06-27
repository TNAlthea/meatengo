<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import {
    PlusCircleIcon,
    ArrowRightOnRectangleIcon,
    ArrowRightIcon,
    ChevronRightIcon,
    XCircleIcon,
    PencilSquareIcon,

} from "@heroicons/vue/24/outline";
import dayjs from 'dayjs';
import 'dayjs/locale/id'; // Import Indonesian locale for day.js

import { getAllTransaction } from "../../util/getData.js";
import store from "../../util/store.js";
import { logout } from "../../util/authUtils.js";

const imagePath = ref("/logo/logo-v1.png");
const user_data = JSON.parse(sessionStorage.getItem("user_data"));
const route = useRouter();
dayjs.locale('id');

/* token credentials */
const headers = {
    Authorization: `Bearer ${user_data.authorization.token}`,
};
const signature = {
    headers,
    withCredentials: true,
};
const tokenExpiry = store.getters.getLoginTime + 6 * 60 * 60 * 1000; //expiration time of token is 6 hours from login

const allTransactionData = ref();
/* On mounted */
onMounted(async () => {
    // if (Date.now() > tokenExpiry) {
    //     route.push('/cashier/login');
    //     alert("token expired, please login again.");
    // }
    allTransactionData.value = await getAllTransaction('cashier', signature, route);
});

/* views formatting */
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
const formatDate = (date) => {
    const formattedDate = dayjs(date).format('HH:mm, DD MMMM YYYY');

    return formattedDate;
}
const formatPrice = (price) => {
    return "Rp " + price.toLocaleString("id-ID");
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
                        class="hover:rounded-full hover:bg-slate-200 p-3">Home</router-link>
                    <router-link to="/cashier/transaction_record"
                        class="font-semibold hover:rounded-full hover:bg-slate-200 p-3">Histori Transaksi</router-link>
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

        <body>
            <div class="bg-indigo-100 w-[100vw] h-[91.2vh] flex justify-center items-center">
                <div class="bg-white h-[85vh] w-[95vw] p-10 rounded-2xl drop-shadow-md">
                    <div class="shadow-slate-300 shadow-md rounded-2xl w-full h-fit">
                        <div>
                            WOW
                        </div>
                        <table class="w-full rounded-full">
                            <thead class="bg-blue-100 text-gray-600 table-auto font-semibold ">
                                <tr>
                                    <th class="px-5 py-2 text-start">No.</th>
                                    <th class="px-5 py-2 text-start">ID</th>
                                    <th class="px-5 py-2 text-start">Tanggal</th>
                                    <th class="px-5 py-2 text-start">Customer</th>
                                    <th class="px-5 py-2 text-start">Kasir</th>
                                    <th class="px-5 py-2 text-start">Metode pembayaran</th>
                                    <th class="px-5 py-2 text-start">Total Barang</th>
                                    <th class="px-5 py-2 text-start">Total sebelum diskon</th>
                                    <th class="px-5 py-2 text-start">Diskon</th>
                                    <th class="px-5 py-2 text-start">Total</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="text-start" :class="{'border-b-2': index !== (allTransactionData.length - 1)}" v-for="(item, index) in allTransactionData"
                                    :key="item.id">
                                    <td class="px-5 py-3"> {{ index+1 }} </td>
                                    <td class="px-5 py-3"> {{ item.id }} </td>
                                    <td class="px-5 py-3"> {{ formatDate(item.sold_at) }} </td>
                                    <td class="px-5 py-3"> {{ item.customer_name }} </td>
                                    <td class="px-5 py-3"> {{ item.cashier_name }} </td>
                                    <td class="px-5 py-3"> {{ item.payment_method }} </td>
                                    <td class="px-5 py-3"> {{ item.products.length }} </td>
                                    <td class="px-5 py-3"> {{ formatPrice(item.total_before_discount) }} </td>
                                    <td class="px-5 py-3"> {{ item.discount }} </td>
                                    <td class="px-5 py-3"> {{ formatPrice(item.total) }} </td>
                                    <td class="px-5 py-3">
                                        <ChevronRightIcon class="w-6 h-6 text-gray-500 cursor-pointer" />
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </body>
    </div>
</template>