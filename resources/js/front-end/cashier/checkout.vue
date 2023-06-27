<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import { ArrowLeftIcon } from "@heroicons/vue/24/solid";
import store from "../../util/store.js";
import api from "../../api.js";

const imagePath = ref("/logo/logo-v1.png");
const route = useRouter();
const user_data = JSON.parse(sessionStorage.getItem("user_data"));
const productList = JSON.parse(localStorage.getItem('productList'));
const transactionData = JSON.parse(localStorage.getItem('transactionData'));

/* token credentials */
const headers = {
    Authorization: `Bearer ${user_data.authorization.token}`,
};
const signature = {
    headers,
    withCredentials: true,
};
const tokenExpiry = store.getters.getLoginTime + 6 * 60 * 60 * 1000; //expiration time of token is 6 hours from login

onMounted(() => {
    // console.log(transactionProductData);
})

let plasticBagCount = ref(0);
let plasticBagPrice = ref(0);

let isShowingPaymentModal = ref(false);
let isPaymentSelected = ref(false);
let selectedPayment = ref('');
let selectedPaymentChannel = ref('');
let cashAmount = ref(0);
let changes = ref(-1);
const paymentOptions = [
    { name: "Cash", value: "Cash" },
    { name: "Kartu Debit", value: "Kartu Debit" },
    { name: "Kartu Kredit", value: "Kartu Kredit" },
    { name: "E-wallet", value: "E-wallet" },
];
const eWallet = [
    { name: "Ovo", value: "Ovo" },
    { name: "Gopay", value: "Gopay" },
    { name: "Dana", value: "Dana" },
];
const bank = [
    { name: "BCA", value: "BCA" },
    { name: "Mandiri", value: "Mandiri" },
    { name: "BNI", value: "BNI" },
];
const selectPaymentMethod = (method) => {
    if (selectedPayment.value == method) selectedPayment.value = '';
    else selectedPayment.value = method;

    console.log(selectedPayment.value)
}
const calculateChanges = () => {
    changes.value = cashAmount.value - transactionData.total;
}

const plasticPrice = () => {
    plasticBagPrice.value = plasticBagCount.value * 200;
}

/* object to store data that will be send into database through API */
let transactionProductData = [];

/* API Calls */
const submitTransaction = async () => {
    try {
        if (Date.now() < tokenExpiry) {
            /* storing into transaction table */
            transactionData.total += plasticBagPrice.value;
            transactionData.payment_method = `${selectedPayment.value} ${selectedPaymentChannel.value}`;
            transactionData.plastic_bag = plasticBagCount.value;

            console.log(transactionData);

            const response_to_transaction = await api.post(
                "api/transaction/add_transaction",
                transactionData,
                signature
            );

            /* storing into transaction_products */
            /* copy selected columns (id and quantity) of selectedProducts into transactionProductData */
            transactionProductData = productList.map(
                (product) => ({
                    quantity: product.quantity,
                    transaction_id: response_to_transaction.data.data.id,
                    inventory_id: product.id,
                })
            );

            console.table(transactionProductData);
            for (const data of transactionProductData) {
                await api.post("api/transaction/add_transaction_product", data, signature);
                
                await api.post(`api/inventory/deduct_stock/${data.inventory_id}`, {quantity: data.quantity}, signature);
            }

            alert("Transaksi sukses!");
            backButton();
        } else {
            route.push({ path: "/cashier/login" });
            alert("Token expired, please login again.");
        }
    } catch (error) {
        alert(`Error terjadi ketika menambah transaksi ${error}`);
        console.error(error);
    }
}

/* view formatting */
const formatQuantity = (quantity) => {
    if (quantity > 1) return `${quantity} pcs`;

    return `${quantity} pc`;
}

const formatPrice = (price) => {
    return "Rp " + price.toLocaleString("id-ID");
};

/* button */
const backButton = () => {
    try {
        route.back();
        localStorage.removeItem('productList');
        localStorage.removeItem('transactionData');
    } catch (error) {
        alert(error);
    }
}

const showPaymentModal = () => {
    isShowingPaymentModal.value = true;
}

const closePaymentModal = () => {
    isShowingPaymentModal.value = false;
}
</script>

<template>
    <header class="flex border-b-2 items-center">
        <span class="flex flex-row gap-5 items-center">
            <a href="#" class="my-2 border-black"><img :src="imagePath" style="width: 200px; height: 65px"
                    class="object-cover object-[50%_48%]" /></a>
        </span>
    </header>

    <body class="flex justify-center items-center h-auto min-h-[200px]">
        <div class="px-auto w-3/4 py-10">
            <span class="inline-block pb-3 cursor-pointer" @click="backButton()">
                <div class="flex flex-row items-center gap-2">
                    <ArrowLeftIcon class="text-black w-5 h-5" />
                    <p class="text-lg">BACK</p>
                </div>
            </span>
            <div class="h-full">
                <h1 class="font-semibold text-2xl text-start">Checkout</h1>
                <div class="flex flex-row py-5 gap-10 justify-between">
                    <section class="flex-1">
                        <span class="w-full border-b border-slate-400 flex items-center pb-2">
                            <p class="text-xl font-semibold">Daftar Produk</p>
                        </span>
                        <table class="w-full">
                            <tbody class="table-fixed min-w-full max-w-full py-2">
                                <div v-for="(product, index) in productList"
                                    class="border-b border-slate-400 pb-1 mb-2 flex flex-row gap-5 items-center text-xl"
                                    :key="product.id">
                                    <tr class="min-w-[5%]">
                                        <td> {{ index + 1 }} </td>
                                    </tr>
                                    <tr class="min-w-[15%]">
                                        <td> <img :src="`/storage/images/inventory/${product.image}`" class="h-24" /> </td>
                                    </tr>
                                    <tr class="min-w-[30%]">
                                        <td> {{ product.name }} </td>
                                    </tr>
                                    <tr class="min-w-[10%] flex justify-center">
                                        <td> {{ formatQuantity(product.quantity) }} </td>
                                    </tr>
                                    <tr class="min-w-[30%] flex justify-end">
                                        <td> {{ formatPrice(product.subTotal) }} </td>
                                    </tr>
                                </div>
                            </tbody>
                        </table>
                    </section>
                    <section class="flex flex-col gap-4 border-2 border-slate-300 rounded-2xl p-5 w-1/4"
                        :class="{ 'h-fit': !isPaymentSelected }">
                        <p class="font-semibold text-xl">Ringkasan Belanja</p>
                        <span class="flex flex-row justify-between gap-10">
                            <p>Total Harga ({{ productList.length }} Produk)</p>
                            <p>{{ formatPrice(transactionData.total_before_discount) }}</p>
                        </span>
                        <span class="flex flex-row justify-between gap-10">
                            <p>Diskon</p>
                            <p>{{ transactionData.discount }} %</p>
                        </span>
                        <span class="flex flex-row justify-between items-baseline gap-10 border-b-2 border-slate-300 pb-4">
                            <p>Kantong Plastik</p>
                            <span class="flex flex-col gap-1 w-1/4">
                                <input type="number" :min="0" :max="100" v-model="plasticBagCount" @input="plasticPrice();"
                                    class="border p-1" />
                                <p class="text-end">{{ formatPrice(plasticBagPrice) }}</p>
                            </span>
                        </span>
                        <span class="flex flex-row justify-between gap-10">
                            <p>Total Belanja</p>
                            <p class="font-semibold"> {{ formatPrice(transactionData.total + plasticBagPrice) }}</p>
                        </span>
                        <div v-if="selectedPayment === 'Cash' && changes >= 0" class="flex flex-col gap-4">
                            <span class="flex flex-row items-center justify-between">
                                <p>Uang yang diterima</p>
                                <p class="font-semibold">{{ formatPrice(cashAmount) }}</p>
                            </span>
                            <span class="flex flex-row gap-5 items-center justify-between">
                                <p>Kembalian</p>
                                <p class="font-semibold">{{ formatPrice(changes) }}</p>
                            </span>
                        </div>
                        <div v-else-if="selectedPayment !== 'Cash' && selectedPayment"
                            class="flex flex-row justify-between items-center">
                            <p>Metode pembayaran</p>
                            <p class="font-semibold">{{ selectedPayment }} {{ selectedPaymentChannel }}</p>
                        </div>
                        <div v-if="(selectedPayment === 'Cash' && changes >= 0) || (selectedPayment !== 'Cash' && selectedPayment && selectedPaymentChannel)"
                            class="flex flex-col gap-5">
                            <span
                                class="border rounded-xl bg-green-500 text-white font-semibold flex justify-center py-3 cursor-pointer"
                                @click="submitTransaction()">
                                Checkout
                            </span>
                            <span
                                class="border rounded-xl bg-gray-200 text-black font-semibold flex justify-center py-3 cursor-pointer"
                                @click="showPaymentModal()">
                                Ubah Metode Pembayaran</span>
                        </div>
                        <div v-else
                            class="border rounded-xl bg-orange-500 text-white font-semibold flex justify-center py-3 cursor-pointer"
                            @click="showPaymentModal()">
                            Pilih Metode Pembayaran
                        </div>
                        <p>*Semua harga sudah termasuk pajak 10%</p>
                    </section>
                </div>
            </div>
        </div>

        <!-- MODAL -->
        <section
            class="absolute top-0 left-0 min-h-[80vh] w-1/2 translate-x-1/2 translate-y-[10vh] border rounded-2xl bg-white p-10"
            v-if="isShowingPaymentModal">
            <h1 class="text-center text-2xl font-semibold">Pilih metode pembayaran</h1>
            <div class="flex flex-col gap-5 my-5 w-full">
                <span class=" rounded-xl py-2 px-5 cursor-pointer"
                    :class="['transition-colors', { 'border-2 border-indigo-500': selectedPayment === option.value }, { 'border border-slate-500': selectedPayment !== option.value }]"
                    v-for="(option, index) in paymentOptions" :key="index" @click="selectPaymentMethod(option.value)">
                    <p class="font-semibold text-xl pb-2"> {{ option.name }} </p>
                    <div class="flex flex-col gap-3 w-full text-xl"
                        v-if="selectedPayment === 'Cash' && option.value === 'Cash'">
                        <span class="flex flex-row items-center justify-between">
                            <p>Jumlah yang harus dibayar</p>
                            <p class="font-semibold">{{ formatPrice(transactionData.total) }}</p>
                        </span>
                        <span class="flex flex-row items-center justify-between ">
                            <label>Uang yang diterima</label>
                            <input type="number" class="border rounded-2xl p-2" v-model="cashAmount"
                                style="transition-property: none;" @input="calculateChanges();" @click.stop />
                        </span>
                    </div>
                    <div v-else-if="option.value === 'Kartu Debit' && selectedPayment === 'Kartu Debit'">
                        <select class="p-3 bg-white border rounded-lg" v-model="selectedPaymentChannel" @click.stop>
                            <option selected disabled value="">Pilih channel pembayaran</option>
                            <option v-for="(item, index) in bank" :key="index" :value="item.value">{{ item.name }}</option>
                        </select>
                    </div>
                    <div v-else-if="option.value === 'Kartu Kredit' && selectedPayment === 'Kartu Kredit'">
                        <select class="p-3 bg-white border rounded-lg" v-model="selectedPaymentChannel" @click.stop>
                            <option selected disabled value="">Pilih channel pembayaran</option>
                            <option v-for="(item, index) in bank" :key="index" :value="item.value">{{ item.name }}</option>
                        </select>
                    </div>
                    <div v-else-if="option.value === 'E-wallet' && selectedPayment === 'E-wallet'">
                        <select class="p-3 bg-white border rounded-lg" v-model="selectedPaymentChannel" @click.stop>
                            <option selected disabled value="">Pilih channel pembayaran</option>
                            <option v-for="(item, index) in eWallet" :key="index" :value="item.value">{{ item.name }}
                            </option>
                        </select>
                    </div>
                </span>
            </div>
            <span
                class="cursor-pointer border border-slate-500 rounded-2xl absolute bottom-[5vh] py-3 w-[70vh] translate-x-[10vh]"
                @click="closePaymentModal()">
                <p class="text-2xl font-semibold text-center">OK</p>
            </span>
        </section>
    </body>
</template>