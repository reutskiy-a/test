<script setup lang="js">
import { ref, onMounted } from 'vue'
import api from '../api/api.js'
import { useRoute } from 'vue-router'

const deals = ref([])
const route = useRoute();

onMounted(async () => {
  const id = route.query.id

  try {
    const data = await api.get(`/tz/placement/get-deals?company_id=${id}`)
    console.log(data);

    deals.value = data.data
  } catch (e) {
    console.error('Ошибка загрузки данных', e)
  }

})

</script>

<template>
  <div class="p-10">
    <div class="overflow-hidden rounded-xl border border-gray-300 shadow-sm">
      <table class="min-w-full bg-white">
        <thead class="bg-[#1587FA] text-[#fff]">
        <tr>
          <th class="px-4 py-3 text-left text-sm font-semibold">ID</th>
          <th class="px-4 py-3 text-left text-sm font-semibold">Название сделки</th>
          <th class="px-4 py-3 text-left text-sm font-semibold">Сумма сделки</th>
        </tr>
        </thead>

        <tbody>
        <tr
            v-for="deal in deals"
            :key="deal.id"
            class="border-t hover:bg-gray-50 transition"
        >
          <td class="px-4 py-3 text-gray-900">{{ deal.ID }}</td>
          <td class="px-4 py-3 text-gray-900">{{ deal.TITLE }}</td>
          <td class="px-4 py-3 text-gray-900">{{ deal.OPPORTUNITY.toLocaleString() }} ₽</td>
        </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>


<style scoped>

</style>
