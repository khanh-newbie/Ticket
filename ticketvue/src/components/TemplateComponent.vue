<template>

</template>

<style></style>

<script setup>
import { ref, reactive } from 'vue'
import axios from 'axios'

// reactive state data
const data = ref({})
const access_token = ref(localStorage.getItem('access_token'))
// const loading = ref(false)
const state = reactive({
    user_info: sessionStorage.getItem('user_info') ? JSON.parse(sessionStorage.getItem('user_info')) : null,
    access_token: sessionStorage.getItem('access_token') ? sessionStorage.getItem('access_token') : null
})
// methods 
// async function getData() {
//     try {
//         const callAPI = await axios.get('https://jsonplaceholder.typicode.com/posts/1')
//         data.value = callAPI.data
//     } catch (error) {
//         console.error(error)
//     }
// }
// // call api 
// async function getDataa() {
//     try{
//         const formData = new FormData()
//         formData.append('title', 'foo')
//         formData.append('body', 'bar')
//         formData.append('userId', '1')
//         const callAPI = await axios.post('http://localhost/ticket/ticketphp/public/api/getdata', 
//             formData,
//             {
//                 headers: {
//                     'Content-Type': 'multipart/form-data',
//                     'Authorization': 'Bearer '+ this.access_token
//                 }
//             }
//         );
//         console.log(callAPI.data)
//     } catch (error) {
//         console.error(error)
//     }

// }
</script>