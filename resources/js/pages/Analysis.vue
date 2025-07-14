<template>
  <div class="min-h-screen bg-gray-900 text-white p-6">
    <!-- Header -->
    <div class="max-w-6xl mx-auto">
      <div class="flex justify-between items-center mb-8">
        <div>
          <h1 class="text-3xl font-bold text-white mb-2">Thematic Analysis Pipeline</h1>
          <p class="text-gray-400">AI-powered qualitative data analysis workflow</p>
        </div>
        <div class="flex gap-4">
          <button
            @click="triggerAnalysis"
            :disabled="isAnalyzing"
            class="bg-gradient-to-r from-blue-600 to-blue-700 text-white px-6 py-3 rounded-lg hover:from-blue-700 hover:to-blue-800 disabled:opacity-50 disabled:cursor-not-allowed flex items-center space-x-2 shadow-lg transform transition-all duration-200 hover:scale-105"
          >
            <svg v-if="isAnalyzing" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>{{ isAnalyzing ? 'Processing Pipeline...' : 'Start Analysis' }}</span>
          </button>
          <button
            @click="restartAnalysis"
            :disabled="isAnalyzing"
            class="bg-gray-700 text-white px-6 py-3 rounded-lg hover:bg-gray-600 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg transform transition-all duration-200 hover:scale-105"
          >
            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
            </svg>
            Restart
          </button>
        </div>
      </div>

      <!-- Progress Section with Workflow Design -->
      <div v-if="progress" class="bg-gray-800 rounded-xl p-6 mb-8 shadow-2xl border border-gray-700">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-xl font-bold text-white flex items-center">
            <svg class="w-6 h-6 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
            Pipeline Progress
          </h3>
          <div v-if="isAnalyzing" class="flex items-center text-blue-400">
            <svg class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="font-medium">Processing...</span>
          </div>
        </div>
        
        <!-- Progress Bar -->
        <div class="w-full bg-gray-700 rounded-full h-3 mb-4 overflow-hidden">
          <div 
            class="bg-gradient-to-r from-blue-500 to-purple-600 h-3 rounded-full transition-all duration-1000 ease-out shadow-lg"
            :style="{ width: progress.percent + '%' }"
          ></div>
        </div>
        
        <div class="flex justify-between items-center text-sm">
          <span class="text-gray-300">
            {{ progress.completed }} of {{ progress.total }} questions completed
          </span>
          <span class="text-blue-400 font-bold">{{ progress.percent }}%</span>
        </div>
        
        <div v-if="isAnalyzing && progress.completed < progress.total" class="mt-3 p-3 bg-blue-900/30 rounded-lg border border-blue-700/50">
          <p class="text-blue-300 text-sm">
            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
            </svg>
            Currently processing Question {{ progress.completed + 1 }}
          </p>
        </div>
      </div>

      <!-- Metadata Summary Section -->
      <div v-if="metadataSummary && metadataSummary.questions_completed > 0" class="bg-gradient-to-r from-gray-800 to-gray-700 rounded-xl p-6 mb-8 shadow-2xl border border-gray-600">
        <h3 class="text-xl font-bold text-white mb-4 flex items-center">
          <svg class="w-6 h-6 mr-2 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
          </svg>
          Analysis Metrics
        </h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div class="bg-gray-900 p-4 rounded-lg border border-gray-600 hover:border-blue-500 transition-colors">
            <p class="text-gray-400 text-sm mb-1">Total Tokens</p>
            <p class="text-2xl font-bold text-blue-400">{{ metadataSummary.total_tokens?.toLocaleString() || 0 }}</p>
          </div>
          <div class="bg-gray-900 p-4 rounded-lg border border-gray-600 hover:border-green-500 transition-colors">
            <p class="text-gray-400 text-sm mb-1">Total Cost</p>
            <p class="text-2xl font-bold text-green-400">${{ metadataSummary.total_cost?.toFixed(4) || '0.0000' }}</p>
          </div>
          <div class="bg-gray-900 p-4 rounded-lg border border-gray-600 hover:border-purple-500 transition-colors">
            <p class="text-gray-400 text-sm mb-1">Duration</p>
            <p class="text-2xl font-bold text-purple-400">{{ metadataSummary.total_duration_seconds?.toFixed(1) || 0 }}s</p>
          </div>
          <div class="bg-gray-900 p-4 rounded-lg border border-gray-600 hover:border-yellow-500 transition-colors">
            <p class="text-gray-400 text-sm mb-1">Model</p>
            <p class="text-lg font-bold text-yellow-400">{{ metadataSummary.model || 'N/A' }}</p>
          </div>
        </div>
        <div class="mt-4 flex flex-wrap gap-4 text-sm text-gray-400">
          <span class="flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
            {{ metadataSummary.total_participants || 0 }} participants
          </span>
          <span class="flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
            </svg>
            {{ metadataSummary.avg_tokens_per_question || 0 }} avg tokens/question
          </span>
          <span class="flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
            </svg>
            ${{ metadataSummary.avg_cost_per_question?.toFixed(4) || '0.0000' }} avg cost/question
          </span>
        </div>
      </div>

      <!-- Errors Section -->
      <div v-if="errors && Object.keys(errors).length > 0" class="bg-red-900/30 border border-red-700 rounded-xl p-6 mb-8 shadow-2xl">
        <h3 class="text-xl font-bold text-red-400 mb-4 flex items-center">
          <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          Pipeline Errors
        </h3>
        <div class="space-y-3">
          <div v-for="(error, question) in errors" :key="question" class="bg-red-800/20 border border-red-600 rounded-lg p-4">
            <div class="flex justify-between items-start">
              <div>
                <strong class="text-red-300">{{ question }}:</strong>
                <p class="text-red-200 mt-1">{{ error }}</p>
              </div>
              <button
                @click="reprocessQuestion(question)"
                class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm flex items-center transition-colors"
              >
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Retry
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Workflow Results Section -->
      <div v-if="analysisResults || isAnalyzing" class="space-y-6">
        <div class="flex items-center mb-6">
          <svg class="w-8 h-8 mr-3 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
          </svg>
          <h2 class="text-2xl font-bold text-white">Analysis Results</h2>
        </div>
        
        <!-- Workflow Tree -->
        <div class="space-y-4">
          <!-- Completed Results -->
          <div v-for="(result, questionKey, index) in analysisResults" :key="questionKey" class="relative">
            <!-- Connection Line -->
            <div v-if="index > 0" class="absolute -top-4 left-6 w-0.5 h-4 bg-gradient-to-b from-blue-500 to-purple-500"></div>
            
            <!-- Result Node -->
            <div class="flex items-start space-x-4">
              <!-- Status Icon -->
              <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-r from-green-500 to-emerald-600 rounded-full flex items-center justify-center shadow-lg">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
              </div>
              
              <!-- Result Content -->
              <div class="flex-1 bg-gray-800 border border-gray-700 rounded-xl shadow-2xl overflow-hidden">
                <details class="group">
                  <summary class="cursor-pointer p-6 hover:bg-gray-750 transition-colors list-none">
                    <div class="flex items-center justify-between">
                      <div class="flex items-center space-x-3">
                        <h3 class="text-lg font-bold text-white">Question {{ index + 1 }}</h3>
                        <span class="px-2 py-1 bg-green-600 text-green-100 text-xs rounded-full">Completed</span>
                      </div>
                      <svg class="w-5 h-5 text-gray-400 transform transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                      </svg>
                    </div>
                    <p class="text-gray-300 mt-2 text-sm">{{ result.question || questionKey }}</p>
                  </summary>
                  
                  <div class="border-t border-gray-700 p-6 bg-gray-850">
                    <QuestionResult 
                      :question-key="questionKey" 
                      :result="result" 
                      :metadata="metadata && metadata[questionKey] ? metadata[questionKey] : null"
                    />
                  </div>
                </details>
              </div>
            </div>
          </div>
          
          <!-- Current Processing Question -->
          <div v-if="isAnalyzing && progress && progress.completed < progress.total" class="relative">
            <!-- Connection Line -->
            <div v-if="analysisResults && Object.keys(analysisResults).length > 0" class="absolute -top-4 left-6 w-0.5 h-4 bg-gradient-to-b from-blue-500 to-purple-500"></div>
            
            <div class="flex items-start space-x-4">
              <!-- Processing Icon -->
              <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center shadow-lg">
                <svg class="animate-spin w-6 h-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
              </div>
              
              <!-- Processing Content -->
              <div class="flex-1 bg-gray-800 border border-blue-500 rounded-xl shadow-2xl p-6">
                <div class="flex items-center space-x-3 mb-4">
                  <h3 class="text-lg font-bold text-white">Question {{ progress.completed + 1 }}</h3>
                  <span class="px-2 py-1 bg-blue-600 text-blue-100 text-xs rounded-full animate-pulse">Processing</span>
                </div>
                <p class="text-blue-300 text-sm mb-4">Analyzing responses with AI...</p>
                
                <!-- Loading Animation -->
                <div class="bg-gray-900 rounded-lg p-4">
                  <div class="animate-pulse space-y-3">
                    <div class="h-3 bg-gray-700 rounded w-3/4"></div>
                    <div class="h-3 bg-gray-700 rounded w-1/2"></div>
                    <div class="h-3 bg-gray-700 rounded w-5/6"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Pending Questions -->
          <template v-if="isAnalyzing && progress && (progress.total - progress.completed - 1) > 0">
            <div v-for="n in (progress.total - progress.completed - 1)" :key="`pending-${n}`" class="relative">
              <!-- Connection Line -->
              <div class="absolute -top-4 left-6 w-0.5 h-4 bg-gray-600"></div>
              
              <div class="flex items-start space-x-4">
                <!-- Pending Icon -->
                <div class="flex-shrink-0 w-12 h-12 bg-gray-700 rounded-full flex items-center justify-center shadow-lg">
                  <span class="text-gray-400 font-bold">{{ progress.completed + n + 1 }}</span>
                </div>
                
                <!-- Pending Content -->
                <div class="flex-1 bg-gray-800 border border-gray-600 rounded-xl shadow-xl p-6 opacity-60">
                  <div class="flex items-center space-x-3">
                    <h3 class="text-lg font-bold text-gray-400">Question {{ progress.completed + n + 1 }}</h3>
                    <span class="px-2 py-1 bg-gray-600 text-gray-300 text-xs rounded-full">Waiting</span>
                  </div>
                  <p class="text-gray-500 text-sm mt-2">Queued for processing</p>
                </div>
              </div>
            </div>
          </template>
        </div>
      </div>

      <!-- Debug Section -->
      <div v-if="showDebug" class="bg-gray-800 border border-gray-700 rounded-xl p-6 shadow-2xl">
        <h3 class="text-xl font-bold text-white mb-4 flex items-center">
          <svg class="w-6 h-6 mr-2 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
          </svg>
          Debug Information
        </h3>
        <div class="space-y-4">
          <details class="group">
            <summary class="cursor-pointer font-medium text-purple-300 hover:text-purple-200 flex items-center justify-between p-3 bg-gray-900 rounded-lg">
              <span>Reasoning Context</span>
              <svg class="w-4 h-4 transform transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
              </svg>
            </summary>
            <pre class="text-xs bg-gray-900 p-4 rounded-lg mt-2 overflow-auto text-gray-300 border border-gray-700">{{ JSON.stringify(reasoningContext, null, 2) }}</pre>
          </details>
          <details class="group">
            <summary class="cursor-pointer font-medium text-purple-300 hover:text-purple-200 flex items-center justify-between p-3 bg-gray-900 rounded-lg">
              <span>Working Context</span>
              <svg class="w-4 h-4 transform transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
              </svg>
            </summary>
            <pre class="text-xs bg-gray-900 p-4 rounded-lg mt-2 overflow-auto text-gray-300 border border-gray-700">{{ JSON.stringify(workingContext, null, 2) }}</pre>
          </details>
          <details v-if="metadata" class="group">
            <summary class="cursor-pointer font-medium text-purple-300 hover:text-purple-200 flex items-center justify-between p-3 bg-gray-900 rounded-lg">
              <span>Individual Question Metadata</span>
              <svg class="w-4 h-4 transform transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
              </svg>
            </summary>
            <pre class="text-xs bg-gray-900 p-4 rounded-lg mt-2 overflow-auto text-gray-300 border border-gray-700">{{ JSON.stringify(metadata, null, 2) }}</pre>
          </details>
        </div>
      </div>

      <!-- Debug Toggle Button -->
      <div class="flex justify-center mt-8">
        <button
          @click="showDebug = !showDebug"
          class="text-sm text-gray-500 hover:text-gray-300 flex items-center space-x-2 transition-colors"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
          </svg>
          <span>{{ showDebug ? 'Hide' : 'Show' }} Debug Information</span>
        </button>
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