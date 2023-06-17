<script setup>
import { ref, onMounted } from 'vue'
import api from '../../api';
const imagePath = ref('/logo/logo-v1.png')

onMounted( async () => {
    // let response = await api.get('/api/loggedUser');

    // // if (response.data) console.log(response.data);
} );

</script>

<template>
    <header class="border border-b">
        <div class="container mx-auto items-center grid grid-cols-2 text-lg">
            <span class="w-72 h-24">
                <a href="#"><img :src="imagePath" class="object-none object-[50%_90%]"/></a>
            </span>

            <div class="flex flex-cols gap-5 items-center justify-end">
                <a>Home</a>
                <a>Products</a>
                <a>Stores</a>
                <button class="border border-black rounded-lg p-2">Login</button>
            </div>
        </div>
    </header>
</template>