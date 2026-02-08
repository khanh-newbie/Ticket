<template>
  <div class="min-w-[400px]">
    <div class="min-h-screen text-gray-800 font-sans bg-gray-50">
      <HeaderComponent @search="onSearch" @reset="resetSearch" />

      <main class="flex-1 p-6">
        <div class="text-sm text-gray-700 mb-6 flex items-center gap-2 container mx-auto max-w-xl">
          <span>Trang chủ</span>
          <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 14 14">
            <path d="M4.53 2.53l4 4-4 4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
          <span class="text-green-600 font-semibold">Thông tin tài khoản</span>
        </div>

        <div class="bg-white shadow-lg rounded-xl p-8 max-w-xl mx-auto">
          <div class="flex flex-col items-center mb-6">
            <div class="relative group cursor-pointer" @click="triggerFileInput">
              <img 
                :src="avatarPreview || user_info?.avatar || 'https://ui-avatars.com/api/?name=' + form.name"
                class="w-24 h-24 rounded-full border-4 border-white shadow-md object-cover group-hover:opacity-80 transition" 
              />
              
              <button
                type="button"
                class="absolute bottom-0 right-0 bg-white p-1.5 rounded-full shadow border text-gray-600 group-hover:text-green-600 transition">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                  stroke="currentColor" class="w-4 h-4">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                </svg>
              </button>

              <input 
                type="file" 
                ref="fileInput" 
                class="hidden" 
                accept="image/*" 
                @change="onFileChange"
              />
            </div>

            <h2 class="mt-3 text-xl font-bold text-gray-800">{{ form.name }}</h2>
            <p class="text-sm text-gray-500">{{ form.email }}</p>
          </div>

          <div class="flex border-b border-gray-200 mb-6">
            <button @click="currentTab = 'info'"
              :class="currentTab === 'info' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
              class="w-1/2 py-3 text-sm font-medium border-b-2 transition duration-300">
              Thông tin cá nhân
            </button>
            <button @click="currentTab = 'password'"
              :class="currentTab === 'password' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
              class="w-1/2 py-3 text-sm font-medium border-b-2 transition duration-300">
              Đổi mật khẩu
            </button>
          </div>

          <div v-if="currentTab === 'info'" class="animate-fade-in-up">
            <div class="mb-4">
              <label class="block mb-1.5 text-sm font-medium text-gray-700">Họ và tên</label>
              <input v-model="form.name" type="text"
                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-green-400/50 focus:border-green-400 transition text-sm" />
            </div>

            <div class="mb-4">
              <label class="block mb-1.5 text-sm font-medium text-gray-700">Số điện thoại</label>
              <input v-model="form.phone" type="tel"
                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-green-400/50 focus:border-green-400 transition text-sm" />
            </div>

            <div class="mb-6">
              <label class="block mb-1.5 text-sm font-medium text-gray-700">Email</label>
              <input v-model="form.email" type="email" disabled
                class="w-full border border-gray-200 rounded-lg px-4 py-2.5 bg-gray-50 text-gray-500 text-sm cursor-not-allowed" />
            </div>

            <button @click="updateProfile"
              class="w-full py-2.5 rounded-lg text-white font-semibold bg-green-600 hover:bg-green-700 shadow-md hover:shadow-lg transition duration-200 flex justify-center items-center gap-2">
              <span>Lưu thay đổi</span>
            </button>
          </div>

          <div v-else class="animate-fade-in-up">
            <div class="mb-4">
              <label class="block mb-1.5 text-sm font-medium text-gray-700">Mật khẩu hiện tại</label>
              <input v-model="passForm.current_password" type="password" placeholder="••••••"
                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-green-400/50 focus:border-green-400 transition text-sm" />
            </div>
            <div class="mb-4">
              <label class="block mb-1.5 text-sm font-medium text-gray-700">Mật khẩu mới</label>
              <input v-model="passForm.new_password" type="password" placeholder="Tối thiểu 6 ký tự"
                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-green-400/50 focus:border-green-400 transition text-sm" />
            </div>
            <div class="mb-6">
              <label class="block mb-1.5 text-sm font-medium text-gray-700">Xác nhận mật khẩu mới</label>
              <input v-model="passForm.new_password_confirmation" type="password" placeholder="Nhập lại mật khẩu mới"
                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-green-400/50 focus:border-green-400 transition text-sm" />
            </div>
            <button @click="changePassword"
              class="w-full py-2.5 rounded-lg text-white font-semibold bg-gray-800 hover:bg-gray-900 shadow-md hover:shadow-lg transition duration-200">
              Cập nhật mật khẩu
            </button>
          </div>

        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import HeaderComponent from "@/components/customers/layouts/HeaderComponent.vue";
import { ref, onMounted } from "vue";
import axios from "axios";

const user_info = ref(JSON.parse(sessionStorage.getItem('user_info') || '{}'));
const access_token = ref(sessionStorage.getItem('access_token'));
const currentTab = ref('info');

// 1. Khai báo biến xử lý ảnh
const fileInput = ref(null);
const avatarFile = ref(null);
const avatarPreview = ref(null);

const form = ref({
  name: "Nhất Khánh",
  phone: "",
  email: "nguyenduykhanh121204@gmail.com",
});

const passForm = ref({
  current_password: '',
  new_password: '',
  new_password_confirmation: ''
});

onMounted(() => {
  if (user_info.value) {
    form.value.name = user_info.value.name || form.value.name;
    form.value.email = user_info.value.email || form.value.email;
    form.value.phone = user_info.value.phone || '';
  }
});

// 2. Hàm kích hoạt input file khi click vào ảnh
const triggerFileInput = () => {
  fileInput.value.click();
};

// 3. Hàm xử lý khi chọn file xong
const onFileChange = (event) => {
  const file = event.target.files[0];
  if (file) {
    avatarFile.value = file;
    // Tạo URL để xem trước ảnh ngay lập tức
    avatarPreview.value = URL.createObjectURL(file);
  }
};

// 4. Cập nhật profile (bao gồm cả ảnh)
async function updateProfile() {
  try {
    // Dùng FormData để gửi file
    const formData = new FormData();
    formData.append('name', form.value.name);
    formData.append('phone', form.value.phone);
    
    // Nếu có chọn ảnh mới thì mới gửi lên
    if (avatarFile.value) {
      formData.append('avatar', avatarFile.value);
    }

    const response = await axios.post(
      "http://localhost:8000/api/update-profile",
      formData,
      {
        headers: {
          Authorization: `Bearer ${access_token.value}`,
          'Content-Type': 'multipart/form-data' // Bắt buộc khi gửi file
        },
      }
    );

    if (response.data.code === 200) {
      alert("Cập nhật thông tin thành công!");
      console.log("Response:", response.data);
      
     

      sessionStorage.setItem('user_info', JSON.stringify(response.data.data.user));
      // Cập nhật lại user_info để reactive UI
      user_info.value = response.data.data.user;
      // Reset file input
      avatarFile.value = null;
      
    } else {
      alert("Thất bại: " + (response.data.message || 'Lỗi không xác định'));
    }
  } catch (error) {
    console.error("Lỗi:", error);
    alert("Cập nhật thông tin thất bại.");
  }
}

async function changePassword() {
  if (!passForm.value.current_password || !passForm.value.new_password) {
    alert("Vui lòng nhập đầy đủ thông tin!");
    return;
  }
  if (passForm.value.new_password !== passForm.value.new_password_confirmation) {
    alert("Mật khẩu xác nhận không khớp!");
    return;
  }

  try {
    const response = await axios.post(
      "http://localhost:8000/api/change-password",
      {
        current_password: passForm.value.current_password,
        new_password: passForm.value.new_password,
        new_password_confirmation: passForm.value.new_password_confirmation
      },
      {
        headers: { Authorization: `Bearer ${access_token.value}` },
      }
    );

    if (response.data.code === 200) {
      alert("Đổi mật khẩu thành công!");
      passForm.value = { current_password: '', new_password: '', new_password_confirmation: '' };
      currentTab.value = 'info';
    } else {
      alert(response.data.message);
    }
  } catch (error) {
    const msg = error.response?.data?.message || "Đổi mật khẩu thất bại.";
    alert(msg);
  }
}
</script>

<style scoped>
.animate-fade-in-up {
  animation: fadeInUp 0.3s ease-out forwards;
}

@keyframes fadeInUp {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>