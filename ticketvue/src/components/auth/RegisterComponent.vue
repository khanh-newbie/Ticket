<template>
  <div v-show="modelValue" class="fixed inset-0  bg-opacity-50 flex items-center justify-center z-50">
    <!-- Click nền để đóng -->
    <!-- <div class="absolute inset-0" @click="close"></div> -->

    <!-- Nội dung modal -->
    <div class="bg-white p-6 rounded-xl shadow-lg w-80 relative">
      <button @click="close" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700 text-xl font-bold">
        &times;
      </button>

      <h2 class="text-black text-2xl font-semibold mb-4 text-center">Đăng ký</h2>

      <input v-model="fullName" type="text" placeholder="Họ và tên" class="text-black border p-2 w-full mb-3 rounded" />
      <input v-model="email" type="email" placeholder="Email" class="border p-2 w-full text-black mb-3 rounded" />
      <input v-model="password" type="password" placeholder="Mật khẩu"
        class="border p-2 w-full text-black mb-4 rounded" />

      <button @click="handleRegister" class="bg-green-600 text-white w-full py-2 rounded hover:bg-green-700 transition">
        Đăng ký
      </button>

      <button @click="registerWithGoogle"
        class="mt-3 bg-red-600 text-white w-full py-2 rounded hover:bg-red-700 transition flex justify-center items-center gap-2">
        <img src="https://www.svgrepo.com/show/475656/google-color.svg" alt="Google" class="w-5 h-5" />
        Đăng ký với Google
      </button>

      <p class="mt-4 text-center text-sm text-gray-600">
        Đã có tài khoản?
        <span @click="$emit('switch')" class="text-green-600 cursor-pointer font-medium">Đăng nhập</span>
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, defineProps, defineEmits } from "vue";
import axios from "axios";
import { googleTokenLogin } from 'vue3-google-login';

const props = defineProps({ modelValue: Boolean });
const emits = defineEmits(['update:modelValue', 'switch', 'openConfirm']);

const fullName = ref("");
const email = ref("");
const password = ref("");

const close = () => emits('update:modelValue', false);

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
      alert("Đăng ký thành công! Vui lòng kiểm tra email để xác nhận.");
      close();
      emits('openConfirm'); // báo IndexComponent mở popup confirm

    } else {
      alert(res.data.message || "Đăng ký thất bại");
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
      sessionStorage.setItem("access_token", res.data.data.accessToken);
      sessionStorage.setItem("user_info", JSON.stringify(res.data.data.user_info));
      alert("Đăng nhập thành công!");
      close();
      let user_info = JSON.parse(sessionStorage.getItem('user_info'))
      if (user_info.role == 3) { // 3 là admin
        window.location.href = '/admin/index';
      } else {
        window.location.reload();
      }

    } else {
      alert(res.data.message || "Đăng nhập thất bại");
    }
  } catch (err) {
    console.error(err);
    alert(err.response?.data?.message || err.message);
  }
};
</script>
