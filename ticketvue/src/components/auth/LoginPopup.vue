<template>
    <!-- Popup login chỉ nổi lên, không che full màn hình -->
    <div v-if="auth.showLoginPopup" class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 z-50">
        <div class="bg-white p-6 rounded-lg w-80 shadow-lg">
            <!-- Nút đóng -->
            <button @click="auth.closeLogin" class="absolute top-2 right-2 text-xl">&times;</button>

            <h2 class="text-xl font-bold mb-4 text-center">Đăng nhập</h2>

            <!-- Email/Password -->
            <input v-model="email" type="email" placeholder="Email" class="border p-2 w-full mb-3 rounded" />
            <input v-model="password" type="password" placeholder="Mật khẩu" class="border p-2 w-full mb-4 rounded" />
            <button @click="handleLogin" class="bg-blue-600 text-white w-full py-2 rounded hover:bg-blue-700 mb-2">
                Đăng nhập
            </button>

            <!-- Login Google -->
            <button @click="loginWithGoogle"
                class="bg-red-600 text-white w-full py-2 rounded hover:bg-red-700 flex items-center justify-center gap-2">
                <img src="https://www.svgrepo.com/show/475656/google-color.svg" alt="Google" class="w-5 h-5" />
                Đăng nhập với Google
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref } from "vue";
import { useAuthStore } from "@/stores/auth";
import { useRouter } from "vue-router";
import axios from "axios";
import { googleTokenLogin } from 'vue3-google-login';

const auth = useAuthStore();
const router = useRouter();

const email = ref("");
const password = ref("");

// Login bằng email/password
const handleLogin = async () => {
    if (!email.value || !password.value) return alert("Điền đầy đủ thông tin");

    try {
        const res = await axios.post("http://127.0.0.1:8000/api/login", {
            email: email.value,
            password: password.value
        });

        if (res.data.code === 200) {
            auth.setUser(res.data.data.user_info);
            sessionStorage.setItem("user_info", JSON.stringify(res.data.data.user_info));
            sessionStorage.setItem("access_token", res.data.data.accessToken);
            auth.closeLogin();

            if (auth.redirectAfterLogin) {
                router.push(auth.redirectAfterLogin);
                auth.redirectAfterLogin = null;
            } else {
                router.push("/");
            }
        } else {
            alert(res.data.message || "Đăng nhập thất bại");
        }

    } catch (err) {
        alert(err.response?.data?.message || err.message);
    }
};

// Login bằng Google
const loginWithGoogle = async () => {
    try {
        const googleUser = await googleTokenLogin();
        const res = await axios.post("http://127.0.0.1:8000/api/loginwithgoogle", {
            access_token: googleUser.access_token
        });

        if (res.data.code === 200) {
            auth.setUser(res.data.data.user_info);
            sessionStorage.setItem("user_info", JSON.stringify(res.data.data.user_info));
            sessionStorage.setItem("access_token", res.data.data.accessToken);
            auth.closeLogin();

            if (auth.redirectAfterLogin) {
                router.push(auth.redirectAfterLogin);
                auth.redirectAfterLogin = null;
            } else {
                router.push("/");
            }
        } else {
            alert(res.data.message || "Đăng nhập thất bại");
        }

    } catch (err) {
        alert(err.response?.data?.message || err.message);
    }
};
</script>
