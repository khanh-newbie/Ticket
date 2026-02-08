<template>
    <header class="bg-green-600 text-white">
        <div class="container mx-auto flex items-center justify-between p-3">
            <div class="flex items-center space-x-2 justify-content-center">
                <router-link to="/">
                    <span class="font-bold text-3xl cursor-pointer">ticketbox</span>
                </router-link>
            </div>

            <div class="flex-1 max-w-md mx-6 relative">
                <input v-model="keyword" @keyup.enter="search" type="text" placeholder="Bạn tìm gì hôm nay?"
                    class="w-full bg-white p-2 pl-4 pr-10 rounded-md text-gray-800" />
                <button @click="search" class="absolute right-3 top-2.5 text-green-500">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
            </div>

            <div class="flex items-center space-x-4">
                <router-link to="/organizer/create-event"
                    class="bg-white text-green-600 px-4 py-2 rounded-lg font-semibold hover:bg-gray-100 transition inline-block text-center">
                    Tạo sự kiện
                </router-link>

                <div class="relative cursor-pointer group" @click="toCart">
                    <button class="relative p-2 rounded-lg hover:bg-green-700/20 transition flex items-center">
                        <i class="fa-solid fa-cart-shopping text-2xl"></i>
                        <span
                            class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold px-1.5 py-0.5 rounded-full">
                            {{ cartStore.countCart }}
                        </span>
                    </button>
                </div>

                <a href="my-order" class="hover:underline">Vé của tôi</a>

                <div v-if="state.user_info" class="relative group">
                    <button class="flex items-center space-x-2 focus:outline-none">
                        <img :src="state.user_info.avatar || 'https://via.placeholder.com/150'" alt="Avatar" class="rounded-full w-8 h-8 object-cover">
                        <span class="text-white font-medium">{{ state.user_info.name }}</span>
                        <i class="fas fa-caret-down text-white ml-1"></i>
                    </button>

                    <div
                        class="absolute right-0 mt-2 w-48 bg-white text-gray-800 rounded-lg shadow-lg border border-gray-200 z-50 opacity-0 invisible group-hover:visible group-hover:opacity-100 transition-all duration-150">
                        <router-link to="/my-order" class="flex items-center px-4 py-2 hover:bg-gray-100">
                            <i class="fas fa-ticket-alt mr-2"></i> Vé của tôi
                        </router-link>
                        <router-link to="/my-profile" class="flex items-center px-4 py-2 hover:bg-gray-100">
                            <i class="fas fa-user mr-2"></i> Tài khoản của tôi
                        </router-link>
                        <router-link to="/organizer/events" class="flex items-center px-4 py-2 hover:bg-gray-100">
                            <i class="fas fa-calendar-alt mr-2"></i> Sự kiện của tôi
                        </router-link>
                        <button @click="logout"
                            class="w-full text-left flex items-center px-4 py-2 hover:bg-gray-100 text-red-500">
                            <i class="fas fa-sign-out-alt mr-2"></i> Đăng xuất
                        </button>
                    </div>
                </div>

                <div v-else class="flex items-center space-x-4">
                    <router-link 
                        :to="{ path: '/login', query: { redirect: route.fullPath } }"
                        class="bg-white text-green-600 px-4 py-2 rounded-lg font-semibold hover:bg-gray-100 transition">
                        Đăng nhập
                    </router-link>

                    <router-link 
                        :to="{ path: '/register', query: { redirect: route.fullPath } }"
                        class="bg-white text-green-600 px-4 py-2 rounded-lg font-semibold hover:bg-gray-100 transition">
                        Đăng kí
                    </router-link>
                </div>
            </div>
        </div>
    </header>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useCartStore } from '@/stores/cart.js'
import { useRouter, useRoute } from "vue-router";

const router = useRouter()
const route = useRoute() // Dùng để lấy đường dẫn hiện tại
const cartStore = useCartStore()

const state = reactive({
    user_info: sessionStorage.getItem('user_info') ? JSON.parse(sessionStorage.getItem('user_info')) : null,
    access_token: sessionStorage.getItem('access_token')
})

const keyword = ref('')

onMounted(async () => {
    if (state.access_token) {
        await cartStore.getListCart();
    }
})

function toCart() {
    router.push('/cart');
}

const search = () => {
    if (!keyword.value.trim()) return;
    router.push({
        path: '/search',
        query: { q: keyword.value }
    });
}

async function logout() {
    sessionStorage.removeItem('access_token')
    sessionStorage.removeItem('user_info')
    state.user_info = null
    state.access_token = null
    cartStore.clearCart();
    router.push('/')
}
</script>