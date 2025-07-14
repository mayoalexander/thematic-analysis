<template>
  <div class="space-y-6 p-6">
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

    <!-- Metadata Summary Section -->
    <div v-if="metadataSummary && metadataSummary.questions_completed > 0" class="bg-blue-50 p-4 rounded-lg">
      <h3 class="text-lg font-semibold mb-2 text-blue-800">Analysis Metadata</h3>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
        <div class="bg-white p-3 rounded">
          <p class="font-medium text-gray-600">Total Tokens</p>
          <p class="text-xl font-bold text-blue-600">{{ metadataSummary.total_tokens?.toLocaleString() || 0 }}</p>
        </div>
        <div class="bg-white p-3 rounded">
          <p class="font-medium text-gray-600">Total Cost</p>
          <p class="text-xl font-bold text-green-600">${{ metadataSummary.total_cost?.toFixed(4) || '0.0000' }}</p>
        </div>
        <div class="bg-white p-3 rounded">
          <p class="font-medium text-gray-600">Duration</p>
          <p class="text-xl font-bold text-purple-600">{{ metadataSummary.total_duration_seconds?.toFixed(1) || 0 }}s</p>
        </div>
        <div class="bg-white p-3 rounded">
          <p class="font-medium text-gray-600">Model</p>
          <p class="text-lg font-bold text-gray-700">{{ metadataSummary.model || 'N/A' }}</p>
        </div>
      </div>
      <div class="mt-3 text-xs text-gray-600 space-y-1">
        <p>• {{ metadataSummary.total_participants || 0 }} participants analyzed</p>
        <p>• {{ metadataSummary.avg_tokens_per_question || 0 }} avg tokens/question</p>
        <p>• ${{ metadataSummary.avg_cost_per_question?.toFixed(4) || '0.0000' }} avg cost/question</p>
      </div>
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
      
      <div v-for="(result, questionKey) in analysisResults" :key="questionKey" class="bg-gray-900 border rounded-lg p-6">
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
        <details v-if="metadata">
          <summary class="cursor-pointer font-medium">Individual Question Metadata</summary>
          <pre class="text-xs bg-gray-100 p-2 rounded mt-2 overflow-auto">{{ JSON.stringify(metadata, null, 2) }}</pre>
        </details>
      </div>
    </div>

    <!-- Debug Toggle Button -->
    <div class="flex justify-center">
      <button
        @click="showDebug = !showDebug"
        class="text-sm text-gray-500 hover:text-gray-700"
      >
        {{ showDebug ? 'Hide' : 'Show' }} Debug Information
      </button>
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
const metadata = ref(null)
const metadataSummary = ref(null)
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
    metadataSummary.value = response.data.metadata_summary
    
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
    metadata.value = response.data.metadata
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