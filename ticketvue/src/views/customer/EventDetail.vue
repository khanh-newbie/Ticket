<template>
    <div class="min-w-[400px]">
        <div class="min-h-screen bg-gray-700 text-white font-sans">
            <HeaderComponent />

            <NavBarComponent />


            <main class="container mx-auto p-4">
                <Banner 
                    :event="state.ticket" 
                    />

                <div class="bg-white space-y-6 pt-6">
                    <Description
                        :eventDescription="state.ticket.description" 
                        />

                    <TicketInfo
                        :event="state.ticket"
                        />

                    <Oranigazer
                        :event="state.ticket" 
                    />
                </div>
               
               <EventReviews
                 
                />
            </main>

        </div>
    </div>
    

</template>

<style></style>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import axios from 'axios'
import HeaderComponent from '@/components/customers/layouts/HeaderComponent.vue';
import NavBarComponent from '@/components/customers/layouts/NavBarComponent.vue';
import Banner from '@/components/customers/eventDetail/Banner.vue';
import Description from '@/components/customers/eventDetail/Description.vue';
import Oranigazer from '@/components/customers/eventDetail/Oranigazer.vue';
import TicketInfo from '@/components/customers/eventDetail/TicketInfo.vue';
import EventReviews from '@/components/customers/eventDetail/EventReview.vue'


// reactive state data
const open = ref(false)
const data = ref({})
const state = reactive({
    user_info: sessionStorage.getItem('user_info') ? JSON.parse(sessionStorage.getItem('user_info')) : null,
    access_token: sessionStorage.getItem('access_token') ? sessionStorage.getItem('access_token') : null,
    ticket: {}

})
const route = useRoute() 



const access_token = ref(sessionStorage.getItem('access_token'))
//const user_info = ref(sessionStorage.getItem('user_info'))
// const loading = ref(false)
console.log("hehehehee");
// methods 

// const loginpopup = () => {
//     state.popuplogin = true
//     state.popupregister = false
//     state.popupconfirm = false
// }

// const registerpopup = () => {
//     state.popuplogin = false
//     state.popupregister = true
//     state.popupconfirm = false
// }


async function getData() {
    const query = route.query

    // Lấy từng tham số cụ thể
    const step = route.query.step
    const id = route.query.id
    console.log("id = ", id);
    try {
        const res = await axios.get('http://127.0.0.1:8000/api/event/'+ id);
        console.log(res.data);
        if (res.data.code === 200) {
            // Backend đã filter & limit 4 rồi, dùng trực tiếp
            state.ticket = res.data.data;
            console.log('state.', state.ticket);
        } else {
            console.error('API error:', res.data.message);
        }
    } catch (error) {
        console.error(error)
    }
}

onMounted(() => {
  getData();
});


</script>