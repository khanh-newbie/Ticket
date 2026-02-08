<template>
    <div class="min-h-screen flex bg-gray-100">
        <OrganizerSidebar />
        <OrganizerHeader />
        
        <div class="flex-1 ml-84 p-4 space-y-4">
            <main class="bg-[#0f172a] mt-16 z-[7] block h-auto">
                <div class="mx-auto max-w-screen-2xl">
                    <div class="mx-auto rounded-lg max-w-screen-2xl p-6">
                        <div class="flex flex-row items-center gap-2 mb-4">
                            <p class="font-bold text-2xl text-white">Danh sách File</p>
                        </div>

                        <div class="overflow-x-auto border border-gray-600 rounded-lg bg-[#1e293b]">
                            <table class="min-w-full text-sm text-gray-200">
                                <thead class="bg-[#334155] text-left">
                                    <tr>
                                    <th class="p-3 w-8 text-center">
                                        <input type="checkbox" disabled class="accent-gray-400 cursor-not-allowed" />
                                    </th>
                                    <th class="p-3 w-1/3">File</th>
                                    <th class="p-3">Ngày tạo</th>
                                    <th class="p-3">Người tạo</th>
                                    <th class="p-3">Trạng thái xử lý</th>
                                    <th class="p-3 text-center">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>

<script setup>
import OrganizerSidebar from '@/components/organizers/OrganizerSidebar.vue';
import OrganizerHeader from '@/components/organizers/OrganizerHeader.vue';

</script>

<style scoped>
.ant-layout-content {
  background: #0f172a;
}
</style>