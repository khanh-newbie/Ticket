import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'

export const useCartStore = defineStore('cart', () => {
    // ================== STATE ==================
    const items = ref([])        
    const countCart = ref(0)    
    const checkoutItems = ref([])
    
    // Ref token (Lưu ý: Ref này chỉ khởi tạo 1 lần lúc load trang)
    const access_token = ref(sessionStorage.getItem('access_token'))
    const user_info = ref(sessionStorage.getItem('user_info'))

    // ================== COMPUTED ==================
    const total = computed(() => {
        let sum = 0
        if (Array.isArray(items.value)) {
             for (const item of items.value) {
                sum += Number(item.price || item.unit_price || 0) * item.quantity
            }
        }
        return sum
    })

    const totalQuantity = computed(() => {
        let sum = 0
        if (Array.isArray(items.value)) {
            for (const item of items.value) {
                sum += item.quantity
            }
        }
        return sum
    })

    // ================== ACTIONS CŨ (Đã chỉnh sửa logic bên trong) ==================

    function setCheckoutItems(list) {
        checkoutItems.value = list
    }

    // Hàm addToCart: Trả về TRUE nếu thành công, FALSE nếu thất bại
    async function addToCart(ticketOrId, quantity = 1) {
        updateAuth(); // Cập nhật token mới nhất
        
        if (!user_info.value || !access_token.value) {
            alert('Vui lòng đăng nhập để mua vé!')
            return false 
        }

        // Xử lý linh hoạt: Nếu truyền object ticket thì lấy .id, nếu truyền số thì lấy luôn
        const ticketId = (typeof ticketOrId === 'object') ? ticketOrId.id : ticketOrId;

        try {
            const res = await axios.post(
                'http://127.0.0.1:8000/api/add-to-cart',
                {
                    ticket_id: ticketId,
                    quantity: quantity
                },
                { headers: { Authorization: 'Bearer ' + access_token.value } }
            )

            if (res.data.code === 200) {
                // Thêm xong thì gọi API lấy lại list để update số lượng trên Header
                await getListCart();
                alert("Đã thêm vào giỏ hàng!");
                return true; 
            } else {
                alert(res.data.message || "Không thể thêm vào giỏ");
                return false;
            }
        } catch (err) {
            console.error(err)
            alert("Lỗi kết nối server");
            return false;
        }
    }

    async function init() {
        try {
            const res = await axios.get(
                'http://127.0.0.1:8000/api/get-cart',
                { headers: { Authorization: 'Bearer ' + access_token.value } }
            )

            if (res.data.code === 200) {
                items.value = res.data.data || []
                countCart.value = items.value.length
                console.log("Cart loaded:", items.value)
            }
        } catch (err) {
            console.error(err)
        }
    }

    async function removeFromCart(ticket_type_id) {
        updateAuth();
        try {
            const res = await axios.post(
                'http://127.0.0.1:8000/api/remove-from-cart',
                { ticket_id: ticket_type_id },
                { headers: { Authorization: 'Bearer ' + access_token.value } }
            );

            if (res.data.code === 200) {
                items.value = items.value.filter(item => item.ticket_type_id !== ticket_type_id);
                countCart.value = items.value.length;
            } else {
                alert(res.data.message);
            }
        } catch (err) {
            console.error(err);
            alert("Có lỗi xảy ra, thử lại sau");
        }
    }

    // ================== ACTIONS MỚI (ADD-ONS) ==================

    // 1. Cập nhật Token từ SessionStorage vào State (Fix lỗi login không F5)
    function updateAuth() {
        access_token.value = sessionStorage.getItem('access_token');
        user_info.value = sessionStorage.getItem('user_info');
    }

    // 2. Hàm gọi giỏ hàng an toàn (Dùng cho Header)
    async function getListCart() {
        updateAuth(); 
        if (access_token.value) {
            await init(); 
        } else {
            items.value = [];
            countCart.value = 0;
        }
    }

    // 3. Xóa giỏ hàng (Dùng khi Logout)
    function clearCart() {
        items.value = [];
        countCart.value = 0;
        checkoutItems.value = [];
        access_token.value = null;
        user_info.value = null;
    }

    // 4. Mua ngay: Thêm vé vào cart + chỉ checkout vé này
    async function buyNow(ticketOrId, quantity = 1) {
        updateAuth()

        if (!user_info.value || !access_token.value) {
            alert('Vui lòng đăng nhập để mua vé!')
            return false
        }

        const ticketId = (typeof ticketOrId === 'object') ? ticketOrId.id : ticketOrId

        try {
            // 1️⃣ Add to cart (backend) – KHÔNG clear cart
            const res = await axios.post(
                'http://127.0.0.1:8000/api/add-to-cart',
                {
                    ticket_id: ticketId,
                    quantity: quantity
                },
                { headers: { Authorization: 'Bearer ' + access_token.value } }
            )

            if (res.data.code === 200) {
                // 2️⃣ Load lại cart
                await getListCart()

                // 3️⃣ Chỉ lấy item vừa mua để checkout
                const itemForCheckout = items.value.find(
                    item => item.ticket_type_id === ticketId
                )

                checkoutItems.value = itemForCheckout ? [itemForCheckout] : []

                return true
            } else {
                alert(res.data.message || 'Không thể mua vé')
                return false
            }
        } catch (err) {
            console.error(err)
            alert('Lỗi kết nối server')
            return false
        }
    }


    return { 
        items, countCart, total, totalQuantity, checkoutItems, 
        addToCart, buyNow, removeFromCart, init, setCheckoutItems,
        getListCart, clearCart, updateAuth 
    }
})