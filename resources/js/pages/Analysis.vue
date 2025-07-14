<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <h1 class="text-2xl font-bold">Thematic Analysis Dashboard</h1>
      <div class="flex gap-4">
        <button
          @click="triggerAnalysis"
          :disabled="isAnalyzing"
          class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 disabled:opacity-50"
        >
          {{ isAnalyzing ? 'Analyzing...' : 'Start Analysis' }}
        </button>
        <button
          @click="restartAnalysis"
          :disabled="isAnalyzing"
          class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700 disabled:opacity-50"
        >
          Restart
        </button>
      </div>
    </div>

    <!-- Progress Section -->
    <div v-if="progress" class="bg-gray-50 p-4 rounded-lg">
      <h3 class="text-lg font-semibold mb-2">Progress</h3>
      <div class="w-full bg-gray-200 rounded-full h-2.5">
        <div 
          class="bg-blue-600 h-2.5 rounded-full transition-all duration-500"
          :style="{ width: progress.percent + '%' }"
        ></div>
      </div>
      <p class="text-sm text-gray-600 mt-2">
        {{ progress.completed }} of {{ progress.total }} questions completed ({{ progress.percent }}%)
      </p>
    </div>

    <!-- Errors Section -->
    <div v-if="errors && Object.keys(errors).length > 0" class="bg-red-50 p-4 rounded-lg">
      <h3 class="text-lg font-semibold text-red-800 mb-2">Errors</h3>
      <ul class="text-sm text-red-700 space-y-1">
        <li v-for="(error, question) in errors" :key="question">
          <strong>{{ question }}:</strong> {{ error }}
          <button
            @click="reprocessQuestion(question)"
            class="ml-2 text-blue-600 hover:text-blue-800"
          >
            Retry
          </button>
        </li>
      </ul>
    </div>

    <!-- Results Section -->
    <div v-if="analysisResults" class="space-y-6">
      <h2 class="text-xl font-bold">Analysis Results</h2>
      
      <div v-for="(result, questionKey) in analysisResults" :key="questionKey" class="bg-white border rounded-lg p-6">
        <QuestionResult :question-key="questionKey" :result="result" />
      </div>
    </div>

    <!-- Context Debug Section (Optional) -->
    <div v-if="showDebug" class="bg-gray-50 p-4 rounded-lg">
      <h3 class="text-lg font-semibold mb-2">Debug Information</h3>
      <div class="space-y-2">
        <details>
          <summary class="cursor-pointer font-medium">Reasoning Context</summary>
          <pre class="text-xs bg-gray-100 p-2 rounded mt-2 overflow-auto">{{ JSON.stringify(reasoningContext, null, 2) }}</pre>
        </details>
        <details>
          <summary class="cursor-pointer font-medium">Working Context</summary>
          <pre class="text-xs bg-gray-100 p-2 rounded mt-2 overflow-auto">{{ JSON.stringify(workingContext, null, 2) }}</pre>
        </details>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import QuestionResult from '../components/QuestionResult.vue'
import axios from 'axios'

const isAnalyzing = ref(false)
const progress = ref(null)
const errors = ref({})
const analysisResults = ref(null)
const reasoningContext = ref(null)
const workingContext = ref(null)
const showDebug = ref(false)

let pollInterval = null

const triggerAnalysis = async () => {
  try {
    isAnalyzing.value = true
    await axios.post('/api/analysis/trigger')
    startPolling()
  } catch (error) {
    console.error('Failed to trigger analysis:', error)
  }
}

const restartAnalysis = async () => {
  try {
    isAnalyzing.value = true
    await axios.post('/api/analysis/trigger?restart=1')
    startPolling()
  } catch (error) {
    console.error('Failed to restart analysis:', error)
  }
}

const reprocessQuestion = async (questionKey) => {
  try {
    await axios.post(`/api/analysis/reprocess/${questionKey}`)
    startPolling()
  } catch (error) {
    console.error(`Failed to reprocess question ${questionKey}:`, error)
  }
}

const fetchStatus = async () => {
  try {
    const response = await axios.get('/api/analysis/status')
    progress.value = response.data.progress
    errors.value = response.data.errors
    
    if (response.data.has_results) {
      await fetchResults()
    }
    
    if (progress.value && progress.value.percent >= 100) {
      isAnalyzing.value = false
      stopPolling()
    }
  } catch (error) {
    console.error('Failed to fetch status:', error)
  }
}

const fetchResults = async () => {
  try {
    const response = await axios.get('/api/analysis/results')
    analysisResults.value = response.data.analysis_results
    reasoningContext.value = response.data.reasoning_context
    workingContext.value = response.data.working_context
  } catch (error) {
    console.error('Failed to fetch results:', error)
  }
}

const startPolling = () => {
  if (pollInterval) clearInterval(pollInterval)
  
  pollInterval = setInterval(fetchStatus, 2000) // Poll every 2 seconds
}

const stopPolling = () => {
  if (pollInterval) {
    clearInterval(pollInterval)
    pollInterval = null
  }
}

onMounted(() => {
  fetchStatus()
})

onUnmounted(() => {
  stopPolling()
})
</script>