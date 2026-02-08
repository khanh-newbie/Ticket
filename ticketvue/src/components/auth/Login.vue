<template>
    <HeaderComponent />

    <div class="min-h-screen bg-gray-100 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="bg-white p-10 rounded-2xl shadow-xl w-full max-w-md">

            <h2 class="text-3xl font-bold text-green-700 mb-8 text-center">
                Đăng nhập
            </h2>

            <form @submit.prevent="handleLogin">
                <div class="mb-5">
                    <input v-model="email" type="email" placeholder="Email" required
                        class="text-black border border-gray-300 p-4 w-full rounded-lg focus:outline-none focus:border-green-600 focus:ring-2 focus:ring-green-200" />
                </div>

                <div class="mb-2">
                    <input v-model="password" type="password" placeholder="Mật khẩu" required
                        class="text-black border border-gray-300 p-4 w-full rounded-lg focus:outline-none focus:border-green-600 focus:ring-2 focus:ring-green-200" />
                </div>

                <div class="flex justify-end mb-6">
                    <router-link to="/forgot-password" class="text-sm text-green-600 hover:text-green-800 hover:underline">
                        Quên mật khẩu?
                    </router-link>
                </div>

                <button type="submit"
                    class="bg-green-600 text-white w-full py-3.5 rounded-lg text-lg font-semibold hover:bg-green-700 active:scale-95 transition shadow-md">
                    Đăng nhập
                </button>
            </form>

            <div class="relative my-6">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-2 bg-white text-gray-500">Hoặc</span>
                </div>
            </div>

            <button @click="loginWithGoogle"
                class="bg-white border border-gray-300 text-gray-700 w-full py-3.5 rounded-lg text-lg font-medium hover:bg-gray-50 active:scale-95 transition flex justify-center items-center gap-3">
                <img src="https://www.svgrepo.com/show/475656/google-color.svg" class="w-6 h-6" />
                Đăng nhập với Google
            </button>

            <p class="mt-6 text-center text-gray-600 text-sm">
                Chưa có tài khoản?
                <router-link 
                    :to="{ path: '/register', query: { redirect: route.query.redirect } }" 
                    class="text-green-700 font-bold hover:underline">
                    Đăng ký ngay
                </router-link>
            </p>

        </div>
    </div>
</template>

<script setup>
import { ref } from "vue";
import axios from "axios";
import { googleTokenLogin } from "vue3-google-login";
import { useRouter, useRoute } from "vue-router";
import { useCartStore } from "@/stores/cart";
import HeaderComponent from '@/components/customers/layouts/HeaderComponent.vue';

const cartStore = useCartStore();
const router = useRouter();
const route = useRoute(); // Lấy query params

const email = ref("");
const password = ref("");

// --- XỬ LÝ SAU KHI LOGIN THÀNH CÔNG ---
const onLoginSuccess = async (data) => {
    // 1. Lưu session
    sessionStorage.setItem("access_token", data.accessToken);
    sessionStorage.setItem("user_info", JSON.stringify(data.user_info));
    
    // 2. Update Store
    cartStore.updateAuth(); 
    await cartStore.getListCart();
    
    //alert("Đăng nhập thành công!");

    // 3. Redirect
    if (data.user_info.role == 3) {
        window.location.href = "/admin/dashboard"; 
    } else {
        // Kiểm tra xem có cần quay lại trang cũ không
        const redirectPath = route.query.redirect;
        
        // Nếu redirect là chính trang login thì về home để tránh loop
        if (redirectPath === '/login' || !redirectPath) {
             router.push("/");
        } else {
             router.push(redirectPath);
        }
    }
};

const handleLogin = async () => {
    if (!email.value || !password.value) {
        alert("Vui lòng điền đầy đủ thông tin");
        return;
    }
    try {
        const res = await axios.post("http://127.0.0.1:8000/api/login", {
            email: email.value,
            password: password.value
        });

        if (res.data.code === 200) {
            await onLoginSuccess(res.data.data);
        } else {
            alert(res.data.message || "Đăng nhập thất bại");
        }
    } catch (err) {
        console.error(err);
        alert(err.response?.data?.message || "Lỗi kết nối server");
    }
};

const loginWithGoogle = async () => {
    try {
        const googleUser = await googleTokenLogin();
        const res = await axios.post("http://127.0.0.1:8000/api/loginwithgoogle", {
            access_token: googleUser.access_token
        });

        if (res.data.code === 200) {
            await onLoginSuccess(res.data.data);
        } else {
            alert(res.data.message || "Đăng nhập thất bại");
        }
    } catch (err) {
        console.error(err);
        alert(err.response?.data?.message || "Lỗi đăng nhập Google");
    }
};
</script>