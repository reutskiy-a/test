<script setup lang="js">
import axios from 'axios'
import api from '../api/api.js'
import { ref } from 'vue'

const progress = ref(0)
const isRunning = ref(false)
const count =ref(null)
const timeout = 3000


// Добавить встраиваемое поле в компанию
const buttonAddUserFieldBehavior = ref(false)
const buttonAddUserFieldResult = ref(false)

// Добавить обработчик при изменении сделки
const buttonAddEventBehavior = ref(false)
const buttonEventResult = ref(false)

// Добавить вкладу "Сделки этой компании"
const buttonAddTabToCompanyBehavior = ref(false)
const buttonPlacementResult = ref(false)

async function createTestData() {
  let eventSource = null

  if (isRunning.value) return
  isRunning.value = true
  progress.value = 0

  const data = await api.post('/tz/add-data')
  const taskId = data.data.task_id
  count.value = data.data.count
  if (0 > count.value) {
    count.value = 0
  }

  eventSource = new EventSource(`/api/get-progress?task_id=${taskId}`)

  eventSource.onmessage = (e) => {
    const payload = JSON.parse(e.data)
    progress.value = payload.progress
    console.log('progress:', payload.progress)

    if (payload.progress >= count.value) {
      eventSource.close()
      isRunning.value = false
    }
  }

  eventSource.onerror = (e) => {
    console.warn('SSE error: ', e)

    eventSource.close()
    isRunning.value = false
  }

  progress.value = 0
}

async function addUserField() {
  buttonAddUserFieldBehavior.value = true


  const data = await api.post('/tz/userfield');

  if (data.data.result) {
    buttonAddUserFieldResult.value = 'Поле пользовательского типа добавлено в карточку компаний'
    setTimeout(() => { buttonAddUserFieldResult.value = false }, timeout)

    buttonAddUserFieldBehavior.value = false
    return
  }

  if (data.data.error_description) {
    buttonAddUserFieldResult.value = data.data.error_description
    setTimeout(() => { buttonAddUserFieldResult.value = false }, timeout)

    buttonAddUserFieldBehavior.value = false
    return
  }

  buttonAddUserFieldResult.value = 'что-то пошло не так'
  setTimeout(() => { buttonAddUserFieldResult.value = false }, timeout)

  buttonAddUserFieldBehavior.value = false
}

async function addUpdateEvent() {
  buttonAddEventBehavior.value = true

  const data = await api.post('/tz/event/add-update-event');

  if (data.data.result === true) {
    buttonEventResult.value = 'Обработчик на событие добавлен'
    setTimeout(() => { buttonEventResult.value = false }, timeout)

    buttonAddEventBehavior.value = false
    return
  }

  if (data.data.error_description) {
    buttonEventResult.value = data.data.error_description
    setTimeout(() => { buttonEventResult.value = false }, timeout)

    buttonAddEventBehavior.value = false
    return
  }

  buttonEventResult.value = 'что-то пошло не так'
  setTimeout(() => { buttonEventResult.value = false }, timeout)

  buttonAddEventBehavior.value = false
}

async function addTabToCompany() {
  buttonAddTabToCompanyBehavior.value = true

  const data = await api.post('/tz/placement/add');

  if (data.data.result === true) {
    buttonPlacementResult.value = 'Встрока в краточку компаний добавлена'
    setTimeout(() => { buttonPlacementResult.value = false }, timeout)

    buttonAddTabToCompanyBehavior.value = false
    return
  }

  if (data.data.error_description) {
    buttonPlacementResult.value = data.data.error_description
    setTimeout(() => { buttonPlacementResult.value = false }, timeout)

    buttonAddTabToCompanyBehavior.value = false
    return
  }

  buttonPlacementResult.value = 'что-то пошло не так'
  setTimeout(() => { buttonPlacementResult.value = false }, timeout)

  buttonAddTabToCompanyBehavior.value = false
}

</script>

<template>
  <!-- Создать тестовые данные -->
  <div class="flex flex-row items-center gap-3">
    <B24Button
        type="submit"
        class="mt-2 whitespace-nowrap"
        color="air-primary"
        :disabled="isRunning"
        @click="createTestData"
    >
      Создать тестовые данные
    </B24Button>
    <div v-if="isRunning">
      Progress: {{ progress }} / total: {{ count }}
    </div>
  </div>


  <!-- Добавить встраиваемое поле в компанию -->
  <div class="flex flex-row items-center gap-3 pt-10">
    <div>
      <B24Button
          type="submit"
          class="mt-2 whitespace-nowrap"
          color="air-primary"
          :disabled="buttonAddUserFieldBehavior"
          @click="addUserField"
      >
        Добавить встраиваемое поле в компанию
      </B24Button>
    </div>
    <div v-if="buttonAddUserFieldResult">
      <span class="text-[#777]"> {{ buttonAddUserFieldResult }}</span>
    </div>

  </div>


  <!-- Добавить обработчик при изменении сделки -->
  <div class="flex flex-row items-center gap-3 pt-10">
    <div>
      <B24Button
          type="submit"
          class="mt-2 whitespace-nowrap"
          color="air-primary"
          :disabled="buttonAddEventBehavior"
          @click="addUpdateEvent"
      >
        Добавить обработчик при изменении сделки
      </B24Button>
    </div>
    <div v-if="buttonEventResult">
      <span class="text-[#777]">{{ buttonEventResult }}</span>
    </div>

  </div>


  <!-- Добавить вкладу "Сделки этой компании" -->
  <div class="flex flex-row items-center gap-3 pt-10">
    <div>
      <B24Button
          type="submit"
          class="mt-2 whitespace-nowrap"
          color="air-primary"
          :disabled="buttonAddTabToCompanyBehavior"
          @click="addTabToCompany"
      >
        Добавить вкладу "Сделки этой компании"
      </B24Button>
    </div>
    <div v-if="buttonPlacementResult">
      <span class="text-[#777]"> {{ buttonPlacementResult }}</span>
    </div>

  </div>



</template>
