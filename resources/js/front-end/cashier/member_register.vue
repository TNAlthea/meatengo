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
    ChevronDownIcon,
    ChevronUpIcon,

} from "@heroicons/vue/24/outline";
import dayjs from 'dayjs';
import 'dayjs/locale/id'; // Import Indonesian locale for day.js
import { getAllMember } from "../../util/getData.js";
import store from "../../util/store.js";
import { logout } from "../../util/authUtils.js";
import api from "../../api";

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

const members = ref([]);
const registData = {
    name: '',
    telephone: '',
    email: '', 
    password: '', 
};

/* On mounted */
onMounted(async () => {
    members.value = await getAllMember('cashier', signature, route);
    console.log(members.value)
});

/* API Calls */
const addMember = async () => {
    if (Date.now() < tokenExpiry) {
        try {
            if (registData.email === '') registData.email = Math.random().toString(36).slice(2) + '@meatengo.com'; //default value since cashier is the one who input the data, can be changed later by user
            if (registData.password === '') registData.password = 'meatengodefaultpass'; //default value since cashier is the one who input the data, can be changed later by user

            const response = await api.post(`api/register`, registData);

            alert(`berhasil menambahkan member baru!`);
            
            members.value = await getAllMember('cashier', signature, route); //repopulate members array
        } catch (error) {
            if (error.response) {
                const errorMessage = error.response.data;
                alert(
                    `Error terjadi ketika mendaftar member baru. ${errorMessage.message}.`
                );
                console.log(errorMessage.message)
            } else {
                alert(error);
            }
        }
    } else {
        alert(`token expired, please login again.`);
        sessionStorage.removeItem("user_data");
        store.dispatch("clearTokenExpirationTimer");
        route.push(`/${entity}/login`);
    }
}

/* views formatting */
const formatDate = (date) => {
    const formattedDate = dayjs(date).format('HH:mm, DD MMMM YYYY');

    return formattedDate;
}
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
                    <router-link to="/cashier/transaction_record" class="hover:rounded-full hover:bg-slate-200 p-3">Histori
                        Transaksi</router-link>
                    <router-link to="/cashier/register_member"
                        class="font-semibold hover:rounded-full hover:bg-slate-200 p-3">Daftar
                        Member</router-link>
                </div>

                <button @click="logout('cashier', signature, route)"
                    class="border-2 border-red-500 text-red-500 rounded-lg flex flex-row gap-1 items-center px-3 py-2 group hover:bg-red-500 hover:text-white hover:font-semibold">
                    <ArrowRightOnRectangleIcon class="w-6 h-6" />
                    <p>Logout</p>
                </button>
            </div>
        </header>

        <body class="bg-indigo-100">
            <div class="flex flex-row h-[91.2vh]">
                <section class="flex flex-col justify-around bg-sky-200 shadow-xl p-10">
                    <p class="text-4xl text-center text-gray-700 font-semibold">Daftar Member Baru</p>
                    <form class="flex flex-row gap-5 py-10 text-xl">
                        <span class="flex flex-col gap-5">
                            <label class="py-1">Nama</label>
                            <label class="py-1">No. Telepon</label>
                            <label class="py-1">Email</label>
                            <label class="py-1">Password</label>
                        </span>
                        <span class="flex flex-col gap-5">
                            <input type="text" v-model="registData.name" class="px-5 py-1 rounded-md bg-white shadow-sm"  required />
                            <input type="text" v-model="registData.telephone" class="px-5 py-1 rounded-md bg-white shadow-sm" required />
                            <input type="email" v-model="registData.email" class="px-5 py-1 rounded-md bg-white shadow-sm" />
                            <input type="password" v-model="registData.password" class="px-5 py-1 rounded-md bg-white shadow-sm" />
                        </span>
                    </form>
                    <span class="flex justify-center items-center bg-gray-800 shadow-md rounded-2xl cursor-pointer" @click="addMember()">
                        <p class="text-4xl text-white font-semibold p-5">TAMBAH MEMBER</p>
                    </span>
                </section>
                <section class="grow p-10">
                    <div class="bg-white shadow-lg rounded-lg p-10 h-[82vh]">
                        <table class="table-fixed rounded-xl shadow-md w-[65vw]">
                            <thead class="bg-blue-100 text-gray-600 table-auto font-semibold ">
                                <tr>
                                    <th class="text-start px-5 p-2 w-[5%]">No</th>
                                    <th class="text-start px-5 p-2">Nama</th>
                                    <th class="text-start px-5 p-2">Email</th>
                                    <th class="text-start px-5 p-2">No. Telepon</th>
                                    <th class="text-start px-5 p-2 w-[10%]">Poin</th>
                                    <th class="text-start px-5 p-2">Menjadi member pada</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="text-start" :class="{ 'border-b-2': index !== (members.length - 1) }"
                                    v-for="(member, index) in members" :key="member.id">
                                    <td class="px-5 p-2">{{ index+1 }}</td>
                                    <td class="px-5 p-2">{{ member.name }}</td>
                                    <td class="px-5 p-2">{{ member.email }}</td>
                                    <td class="px-5 p-2">{{ member.telephone }}</td>
                                    <td class="px-5 p-2">{{ member.points }}</td>
                                    <td class="px-5 p-2">{{ formatDate(member.created_at) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </body>
    </div>
</template>