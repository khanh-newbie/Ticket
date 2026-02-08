<template>
    <HeaderComponent />

    <div class="min-h-screen bg-gray-100 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full bg-white p-8 rounded-xl shadow-lg transition-all duration-300">
            
            <div v-if="!isVerifying">
                <h2 class="text-2xl font-bold text-center text-gray-900 mb-6">Đăng ký tài khoản</h2>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Họ và tên</label>
                        <input v-model="fullName" type="text" placeholder="Nguyễn Văn A" 
                            class="w-full border border-gray-300 p-2 rounded-lg focus:ring-2 focus:ring-green-500 text-black" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input v-model="email" type="email" placeholder="example@gmail.com" 
                            class="w-full border border-gray-300 p-2 rounded-lg focus:ring-2 focus:ring-green-500 text-black" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mật khẩu</label>
                        <input v-model="password" type="password" placeholder="••••••••" 
                            class="w-full border border-gray-300 p-2 rounded-lg focus:ring-2 focus:ring-green-500 text-black" />
                    </div>

                    <button @click="handleRegister" 
                        class="w-full bg-green-600 text-white py-2.5 rounded-lg hover:bg-green-700 transition font-semibold">
                        Đăng ký
                    </button>

                    <div class="relative my-4">
                        <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-gray-300"></div></div>
                        <div class="relative flex justify-center text-sm"><span class="px-2 bg-white text-gray-500">Hoặc</span></div>
                    </div>

                    <button @click="registerWithGoogle"
                        class="w-full border border-gray-300 bg-white text-gray-700 py-2.5 rounded-lg hover:bg-gray-50 transition flex justify-center items-center gap-2 font-medium">
                        <img src="https://www.svgrepo.com/show/475656/google-color.svg" alt="Google" class="w-5 h-5" />
                        Đăng ký với Google
                    </button>
                </div>

                <p class="mt-6 text-center text-sm text-gray-600">
                    Đã có tài khoản?
                    <router-link 
                        :to="{ path: '/login', query: { redirect: route.query.redirect } }" 
                        class="text-green-600 font-semibold hover:underline">
                        Đăng nhập
                    </router-link>
                </p>
            </div>

            <div v-else>
                <button @click="isVerifying = false" class="text-gray-400 hover:text-gray-600 mb-4 flex items-center gap-1 text-sm">
                    <i class="fa-solid fa-arrow-left"></i> Quay lại
                </button>

                <h2 class="text-2xl font-bold text-center text-gray-900 mb-2">Xác thực tài khoản</h2>
                <p class="text-center text-sm text-gray-500 mb-6">Mã đã gửi đến <b>{{ email }}</b></p>

                <div class="space-y-4">
                    <input v-model="code" type="text" placeholder="Nhập mã xác thực" 
                        class="w-full border border-gray-300 p-2 rounded-lg text-center text-xl tracking-widest text-black" />

                    <button @click="handleConfirm" 
                        class="w-full bg-blue-600 text-white py-2.5 rounded-lg hover:bg-blue-700 transition font-semibold">
                        Xác nhận
                    </button>
                </div>

                <p class="mt-6 text-center text-sm text-gray-600">
                    Chưa nhận được mã?
                    <button @click="resendCode" class="text-blue-600 font-semibold hover:underline bg-transparent border-none cursor-pointer">
                        Gửi lại
                    </button>
                </p>
            </div>

        </div>
    </div>
</template>

<script setup>
import { ref } from "vue";
import axios from "axios";
import { useRouter, useRoute } from "vue-router";
import { googleTokenLogin } from 'vue3-google-login';
import HeaderComponent from '@/components/customers/layouts/HeaderComponent.vue';

const router = useRouter();
const route = useRoute();

const isVerifying = ref(false);
const fullName = ref("");
const email = ref("");
const password = ref("");
const code = ref("");

const handleRegister = async () => {
    if (!fullName.value || !email.value || !password.value) {
        alert("Vui lòng điền đầy đủ thông tin");
        return;
    }
    try {
        const res = await axios.post("http://127.0.0.1:8000/api/register", {
            name: fullName.value,
            email: email.value,
            password: password.value
        });

        if (res.data.code === 200) {
            alert("Đăng ký thành công! Vui lòng kiểm tra email.");
            isVerifying.value = true;
        } else {
            alert(res.data.message || "Đăng ký thất bại");
        }
    } catch (err) {
        alert(err.response?.data?.message || "Có lỗi xảy ra");
    }
};

const handleConfirm = async () => {
    if (!code.value) { alert("Vui lòng nhập mã!"); return; }
    try {
        const res = await axios.post("http://127.0.0.1:8000/api/verifyAccount", {
            'email': email.value,
            'code': code.value,
        });

        if (res.data.code === 200) {
            alert("Xác nhận thành công!");
            // Chuyển sang trang Login, KÈM THEO REDIRECT PARAM để user không bị mất dấu
            router.push({ 
                path: '/login', 
                query: { redirect: route.query.redirect } 
            });
        } else {
            alert(res.data.message);
        }
    } catch (err) {
        alert(err.response?.data?.message || err.message);
    }
};

const registerWithGoogle = async () => {
    try {
        const googleUser = await googleTokenLogin();
        const res = await axios.post("http://127.0.0.1:8000/api/loginwithgoogle", {
            'access_token': googleUser.access_token,
        });

        if (res.data.code === 200) {
            // Google Login xong là coi như xác thực xong -> Redirect luôn
            sessionStorage.setItem("access_token", res.data.data.accessToken);
            sessionStorage.setItem("user_info", JSON.stringify(res.data.data.user_info));
            alert("Đăng nhập thành công!");
            
            const redirectPath = route.query.redirect;
            if (res.data.data.user_info.role == 3) {
                window.location.href = '/admin/index';
            } else {
                router.push(redirectPath || '/');
            }
        } else {
            alert(res.data.message || "Đăng nhập thất bại");
        }
    } catch (err) {
        alert(err.response?.data?.message || err.message);
    }
};

const resendCode = async () => {
    try {
        const res = await axios.post("http://127.0.0.1:8000/api/resend-code", { email: email.value });
        alert(res.data.message || "Đã gửi lại mã!");
    } catch (err) {
        alert(err.response?.data?.message || err.message);
    }
};
</script>