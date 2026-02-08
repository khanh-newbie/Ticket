<template>
  <div v-show="modelValue" class="fixed inset-0  bg-opacity-50 flex items-center justify-center z-50">
    <!-- Click nền để đóng -->
    <!-- <div class="absolute inset-0" @click="close"></div> -->

    <!-- Nội dung modal -->
    <div class="bg-white p-6 rounded-xl shadow-lg w-80 relative">
      <!-- Nút đóng -->
      <button @click="close" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700 text-xl font-bold">
        &times;
      </button>

      <h2 class="text-2xl font-semibold mb-4 text-center text-black">Xác nhận tài khoản</h2>

      <input
        v-model="email"
        type="email"
        placeholder="Nhập email đã đăng ký"
        class="border p-2 w-full mb-3 rounded text-black"
      />
      <input
        v-model="code"
        type="text"
        placeholder="Nhập mã xác thực"
        class="border p-2 w-full mb-4 rounded text-center text-black"
      />

      <button
        @click="handleConfirm"
        class="bg-blue-600 text-white w-full py-2 rounded hover:bg-blue-700 transition"
      >
        Xác nhận
      </button>

      <p class="mt-4 text-center text-sm text-gray-600">
        Chưa nhận được mã?
        <span @click="resendCode" class="text-blue-600 cursor-pointer font-medium">
          Gửi lại
        </span>
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, defineProps, defineEmits } from "vue";
import axios from "axios";

const props = defineProps({
  modelValue: Boolean
});
const emits = defineEmits(['update:modelValue']);

const email = ref("");
const code = ref("");

const close = () => {
  emits('update:modelValue', false);
};

const handleConfirm = async () => {
  if (!email.value || !code.value) {
    alert("Vui lòng nhập đầy đủ email và mã xác nhận!");
    return;
  }

  try {
    const res = await axios.post("http://127.0.0.1:8000/api/verifyAccount", {
      'email': email.value,
      'code': code.value,
    });

    if (res.data.code === 200) {
      alert("Xác nhận thành công! Bạn có thể đăng nhập ngay.");
      close(); // đóng popup sau khi xác nhận
    } else {
      alert(res.data.message);
    }
  } catch (err) {
    alert(err.response?.data?.message || err.message);
  }
};

const resendCode = async () => {
  try {
    const res = await axios.post("http://127.0.0.1:8000/api/resend-code", {
      email: email.value,
    });
    alert(res.data.message || "Mã mới đã được gửi!");
  } catch (err) {
    alert(err.response?.data?.message || err.message);
  }
};
</script>
