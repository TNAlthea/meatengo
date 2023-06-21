<script setup>
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import api from '../../api.js';
import store from '../../util/store.js';

const imagePath = ref('/logo/logo-v1.png');
const route = useRouter();

const form = {
    email: '',
    password: ''
};

let wrongCredentials = ref(false);
let errorMessage = ref("");

const login = async () => {
    try {
        wrongCredentials.value = false;

        const response = await api.post('/api/cashier/login', form);
        
        sessionStorage.setItem("user_data", JSON.stringify(response.data));
        const user_data = JSON.parse(sessionStorage.getItem("user_data"));
        const headers = {
            Authorization: `Bearer ${user_data.authorization.token}`,
        };
        const signature = {
            headers,
            withCredentials: true,
        };
        
        store.commit('setSignature', signature);
        store.commit('setEntity', 'cashier');
        store.commit('setRoute', route);
        store.commit('setLoginTime', Date.now());

        // store data into localstorage so store's state can be restored in case of page refresh
        const state = store.state;
        localStorage.setItem('storeState', JSON.stringify(state));

        route.push({ path: '/cashier/dashboard' });
    } catch (error) {
        wrongCredentials.value = true;
        errorMessage.value = 'An error occurred while logging in. Please try again later.';
        if (error.response && error.response.status === 401) {
            errorMessage.value = 'Invalid email or password.';
        }
        console.error(error)
    }
};

</script>

<template>
    <body class="bg-slate-200">
        <section class="h-screen  flex justify-center items-center ">
            <div class="container h-5/6">
                <div class="flex flex-col items-center justify-center h-1/4">
                    <span class="">
                        <img :src="imagePath" class="object-cover h-28 w-96">
                    </span>
                    <p class="text-3xl">Cashier</p>
                </div>
                <div
                    class="mx-auto grid grid-row gap-5 items-center justify-center text-center py-10 bg-white drop-shadow-xl border rounded-3xl w-2/3 lg:w-1/2">
                    <p class="text-4xl">Silakan Login</p>
                    <span class="p-5 bg-red-400 text-center text-white font-semibold" v-if="wrongCredentials">
                        {{ errorMessage }}
                    </span>

                    <form class="" @submit.prevent="login">
                        <span class="flex flex-col text-start gap-1 text-2xl">
                            <label name="email">Email</label>
                            <input type="email" name="email" v-model="form.email"
                                class="border border-black rounded-lg ps-5 py-2" />
                        </span>
                        <span class="flex flex-col text-start gap-1 text-2xl py-5">
                            <label name="password">Password</label>
                            <input type="password" name="password" v-model="form.password"
                                class="border border-black rounded-lg ps-5 py-2" />
                        </span>
                        <button type="submit"
                            class="bg-slate-500 rounded-lg text-xl text-white font-semibold py-3 w-1/2 mx-auto">Login</button>

                    </form>
                </div>
            </div>
        </section>
    </body>
</template>