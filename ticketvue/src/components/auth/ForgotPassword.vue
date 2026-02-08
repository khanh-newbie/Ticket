<template>
    <HeaderComponent />

    <div class="min-h-screen bg-gray-100 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="bg-white p-10 rounded-2xl shadow-xl w-full max-w-md">
            
            <h2 class="text-2xl font-bold text-center text-gray-900 mb-2">
                {{ step === 1 ? 'Quên mật khẩu?' : 'Đặt lại mật khẩu' }}
            </h2>
            
            <p class="text-center text-gray-500 mb-8 text-sm">
                {{ step === 1 
                    ? 'Nhập email của bạn để nhận mã xác nhận.' 
                    : 'Vui lòng nhập mã xác nhận và mật khẩu mới.' }}
            </p>

            <form v-if="step === 1" @submit.prevent="handleSendCode">
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email đăng ký</label>
                    <input v-model="email" type="email" placeholder="example@gmail.com" required
                        class="text-black border border-gray-300 p-3 w-full rounded-lg focus:outline-none focus:border-green-600 focus:ring-2 focus:ring-green-200" />
                </div>

                <button type="submit" :disabled="loading"
                    class="bg-green-600 text-white w-full py-3 rounded-lg text-lg font-semibold hover:bg-green-700 transition disabled:bg-gray-400">
                    {{ loading ? 'Đang gửi...' : 'Gửi mã xác nhận' }}
                </button>
            </form>

            <form v-else @submit.prevent="handleResetPassword">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input v-model="email" type="email" readonly
                        class="text-gray-500 bg-gray-100 border border-gray-300 p-3 w-full rounded-lg cursor-not-allowed" />
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Mã xác nhận</label>
                    <input v-model="code" type="text" placeholder="Nhập mã 6 số" required
                        class="text-black text-center text-xl tracking-widest border border-gray-300 p-3 w-full rounded-lg focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-200" />
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Mật khẩu mới</label>
                    <input v-model="newPassword" type="password" placeholder="********" required
                        class="text-black border border-gray-300 p-3 w-full rounded-lg focus:outline-none focus:border-green-600 focus:ring-2 focus:ring-green-200" />
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Xác nhận mật khẩu</label>
                    <input v-model="confirmPassword" type="password" placeholder="********" required
                        class="text-black border border-gray-300 p-3 w-full rounded-lg focus:outline-none focus:border-green-600 focus:ring-2 focus:ring-green-200" 
                        :class="{'border-red-500 ring-red-200': confirmPassword && newPassword !== confirmPassword}"
                    />
                    <p v-if="confirmPassword && newPassword !== confirmPassword" class="text-red-500 text-xs mt-1">
                        Mật khẩu không khớp
                    </p>
                </div>

                <button type="submit" :disabled="loading"
                    class="bg-blue-600 text-white w-full py-3 rounded-lg text-lg font-semibold hover:bg-blue-700 transition disabled:bg-gray-400">
                    {{ loading ? 'Đang xử lý...' : 'Đổi mật khẩu' }}
                </button>
            </form>

            <div class="mt-6 text-center">
                <router-link to="/login" class="text-sm text-gray-600 hover:text-green-600 flex items-center justify-center gap-2">
                    <i class="fa-solid fa-arrow-left"></i> Quay lại đăng nhập
                </router-link>
            </div>

        </div>
    </div>
</template>

<script setup>
import { ref } from "vue";
import axios from "axios";
import { useRouter } from "vue-router";
import HeaderComponent from '@/components/customers/layouts/HeaderComponent.vue';

const router = useRouter();
const email = ref("");
const code = ref("");
const newPassword = ref("");
const confirmPassword = ref("");
const loading = ref(false);
const step = ref(1); 

// B1: Gửi mã về email
const handleSendCode = async () => {
    if (!email.value) return;
    loading.value = true;
    try {
        const res = await axios.post("http://127.0.0.1:8000/api/forgot-password", { email: email.value });
        
        if (res.data.code === 200) {
            alert("Mã xác nhận đã được gửi! Vui lòng kiểm tra email.");
            step.value = 2; 
        } else {
            alert(res.data.message || "Lỗi gửi mã.");
        }
    } catch (err) {
        alert(err.response?.data?.message || "Lỗi kết nối server");
    } finally {
        loading.value = false;
    }
};

// B2: Đặt lại mật khẩu
const handleResetPassword = async () => {
    if (!code.value || !newPassword.value || !confirmPassword.value) {
        alert("Vui lòng điền đầy đủ thông tin.");
        return;
    }

    if (newPassword.value !== confirmPassword.value) {
        alert("Mật khẩu xác nhận không khớp!");
        return;
    }

    loading.value = true;
    try {
        const res = await axios.post("http://127.0.0.1:8000/api/reset-password", {
            email: email.value,
            code: code.value,
            password: newPassword.value
        });

        if (res.data.code === 200) {
            alert("Đổi mật khẩu thành công! Vui lòng đăng nhập lại.");
            router.push('/login');
        } else {
            alert(res.data.message || "Mã xác nhận không đúng.");
        }
    } catch (err) {
        alert(err.response?.data?.message || "Lỗi kết nối server");
    } finally {
        loading.value = false;
    }
};
</script>