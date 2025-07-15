<template>
  <div class="min-h-screen bg-gray-900 text-white p-6">
    <!-- Header -->
    <div class="max-w-6xl mx-auto">
      <!-- Title Section -->
      <div class="mb-8 mt-24">
        <div class="text-left mb-8">
          <h1 class="text-3xl md:text-4xl font-extrabold text-white mb-2">Thematic Analysis Pipeline</h1>
          <p class="text-gray-400 text-lg">AI-powered qualitative data analysis workflow</p>
        </div>

        <div>
          <!-- Prelude -->
          <p class="text-grayx-300 text-md mb-4 font-medium">
            I built this around the director pattern, using the context as a high level orchestrator, and each subtask adding to the global context, then a final summary agent 
            that consolidates the findings and presents them in a coherent manner in the context of the business.

            This leverages advanced AI techniques to ensure high-quality insights, reduce hallucinations, and provide actionable recommendations.
          </p>
          <p class="opacity-50">
            - Alex Mayo
          </p>
        </div>
      </div>

      <!-- Configuration Panel -->
      <div class="bg-gray-800 rounded-xl p-6 mb-8 shadow-2xl border border-gray-700">
        <h2 class="text-xl font-bold text-white mb-6 flex items-center">
          <svg class="w-6 h-6 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
          </svg>
          Analysis Configuration
        </h2>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
          <!-- File Upload Section -->
          <div class="space-y-4">
            <h3 class="text-lg font-semibold text-white flex items-center">
              <svg class="w-5 h-5 mr-2 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
              </svg>
              Data Source
            </h3>
            
            <div class="flex flex-col sm:flex-row gap-2">
              <button
                @click="selectFileOption(true)"
                :class="[
                  'flex-1 px-4 py-3 rounded-lg text-sm font-medium transition-all',
                  useDefaultFile
                    ? 'bg-green-600 text-white border border-green-500 shadow-lg' 
                    : 'bg-gray-700 text-gray-300 border border-gray-600 hover:bg-gray-600'
                ]"
              >
                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Demo File
              </button>
              <button
                @click="FeatureFlags.isEnabled('fileUpload', 'uploadFile') ? selectFileOption(false) : null"
                :disabled="!FeatureFlags.isEnabled('fileUpload', 'uploadFile')"
                :class="[
                  'flex-1 px-4 py-3 rounded-lg text-sm font-medium transition-all relative',
                  !FeatureFlags.isEnabled('fileUpload', 'uploadFile')
                    ? 'bg-gray-800 text-gray-500 border border-gray-600 cursor-not-allowed opacity-60'
                    : !useDefaultFile
                      ? 'bg-green-600 text-white border border-green-500 shadow-lg' 
                      : 'bg-gray-700 text-gray-300 border border-gray-600 hover:bg-gray-600'
                ]"
              >
                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                </svg>
                Upload File
                <span v-if="!FeatureFlags.isEnabled('fileUpload', 'uploadFile')" class="absolute -top-1 -right-1 bg-yellow-500 text-yellow-900 text-xs px-2 py-0.5 rounded-full font-bold">
                  {{ FeatureFlags.getReason('fileUpload', 'uploadFile') }}
                </span>
              </button>
            </div>
            
            <div v-if="useDefaultFile" class="bg-gray-900 p-4 rounded-lg border border-gray-600">
              <p class="text-sm text-gray-300 flex items-center">
                <svg class="w-4 h-4 mr-2 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <strong>VPN Consumer Research Study</strong>
              </p>
              <p class="text-xs text-gray-400 mt-1 ml-6">6 questions • 106 participants</p>
            </div>
            
            <div v-if="!useDefaultFile && FeatureFlags.isEnabled('fileUpload', 'uploadFile')" class="space-y-3">
              <div class="border-2 border-dashed border-gray-600 rounded-lg p-4 text-center hover:border-gray-500 transition-colors">
                <input
                  type="file"
                  ref="fileInput"
                  @change="handleFileUpload"
                  accept=".xlsx,.xls"
                  class="hidden"
                />
                <div v-if="!uploadedFile">
                  <svg class="w-8 h-8 mx-auto text-gray-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                  </svg>
                  <p class="text-gray-400 text-sm mb-2">Drop Excel file or click to browse</p>
                  <button
                    @click="$refs.fileInput.click()"
                    class="bg-blue-600 text-white px-3 py-2 rounded-lg hover:bg-blue-700 transition-colors text-sm"
                  >
                    Choose File
                  </button>
                  <p class="text-xs text-gray-500 mt-1">Supports .xlsx and .xls</p>
                </div>
                <div v-else class="text-green-400">
                  <svg class="w-6 h-6 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                  <p class="font-medium text-sm">{{ uploadedFile.name }}</p>
                  <p class="text-xs text-gray-400">{{ (uploadedFile.size / 1024 / 1024).toFixed(2) }} MB</p>
                  <button
                    @click="removeUploadedFile"
                    class="text-red-400 text-xs mt-1 hover:text-red-300"
                  >
                    Remove
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Model Selection -->
          <div class="space-y-4">
            <h3 class="text-lg font-semibold text-white flex items-center">
              <svg class="w-5 h-5 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
              </svg>
              AI Model
            </h3>
            
            <!-- Built-in Models -->
            <div>
              <label class="text-sm font-medium text-gray-300 mb-2 block">Built-in Models</label>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                <button
                  @click="selectModel('gpt-4o-mini', false)"
                  :class="[
                    'px-3 py-3 rounded-lg text-sm font-medium transition-all',
                    selectedModel === 'gpt-4o-mini' && !useCustomModel
                      ? 'bg-blue-600 text-white border border-blue-500 shadow-lg' 
                      : 'bg-gray-700 text-gray-300 border border-gray-600 hover:bg-gray-600'
                  ]"
                >
                  <div class="text-center">
                    <div>GPT-4o Mini</div>
                    <div class="text-xs opacity-75">Fast & Cheap</div>
                  </div>
                </button>
                <button
                  v-if="FeatureFlags.isEnabled('models', 'gpt4')"
                  @click="selectModel('gpt-4', false)"
                  :class="[
                    'px-3 py-3 rounded-lg text-sm font-medium transition-all',
                    selectedModel === 'gpt-4' && !useCustomModel
                      ? 'bg-blue-600 text-white border border-blue-500 shadow-lg' 
                      : 'bg-gray-700 text-gray-300 border border-gray-600 hover:bg-gray-600'
                  ]"
                >
                  <div class="text-center">
                    <div>GPT-4</div>
                    <div class="text-xs opacity-75">High Quality</div>
                  </div>
                </button>
                <!-- GPT-4 Coming Soon Placeholder -->
                <div
                  v-if="!FeatureFlags.isEnabled('models', 'gpt4')"
                  class="px-3 py-3 rounded-lg text-sm font-medium bg-gray-800 text-gray-500 border border-gray-600 cursor-not-allowed opacity-60 relative"
                >
                  <div class="text-center">
                    <div>GPT-4</div>
                    <div class="text-xs opacity-75">High Quality</div>
                  </div>
                  <span class="absolute -top-1 -right-1 bg-yellow-500 text-yellow-900 text-xs px-2 py-0.5 rounded-full font-bold">
                    {{ FeatureFlags.getReason('models', 'gpt4') }}
                  </span>
                </div>
              </div>
            </div>
            
            <!-- Custom OpenRouter -->
            <div>
              <label class="text-sm font-medium text-gray-300 mb-2 block">Custom Model (OpenRouter)</label>
              <button
                @click="FeatureFlags.isEnabled('models', 'customModel') ? selectModel('', true) : null"
                :disabled="!FeatureFlags.isEnabled('models', 'customModel')"
                :class="[
                  'w-full px-4 py-3 rounded-lg text-sm font-medium transition-all relative',
                  !FeatureFlags.isEnabled('models', 'customModel')
                    ? 'bg-gray-800 text-gray-500 border border-gray-600 cursor-not-allowed opacity-60'
                    : useCustomModel
                      ? 'bg-purple-600 text-white border border-purple-500 shadow-lg' 
                      : 'bg-gray-700 text-gray-300 border border-gray-600 hover:bg-gray-600'
                ]"
              >
                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-3.586l6.879-6.879A6 6 0 0122 9z"></path>
                </svg>
                Use Your Own API Key
                <span v-if="!FeatureFlags.isEnabled('models', 'customModel')" class="absolute -top-1 -right-1 bg-yellow-500 text-yellow-900 text-xs px-2 py-0.5 rounded-full font-bold">
                  {{ FeatureFlags.getReason('models', 'customModel') }}
                </span>
              </button>
              <div v-if="useCustomModel && FeatureFlags.isEnabled('models', 'customModel')" class="mt-3 space-y-2">
                <input
                  v-model="customApiKey"
                  type="password"
                  placeholder="OpenRouter API key..."
                  class="w-full px-3 py-2 bg-gray-900 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:border-purple-500 focus:outline-none text-sm"
                />
                <input
                  v-model="customModel"
                  type="text"
                  placeholder="Model (e.g., anthropic/claude-3-sonnet)"
                  class="w-full px-3 py-2 bg-gray-900 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:border-purple-500 focus:outline-none text-sm"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
          <button
            @click="triggerAnalysis"
            :disabled="isAnalyzing || 
                      (useCustomModel && (!customApiKey || !customModel)) ||
                      (!useDefaultFile && !uploadedFile)"
            class="flex-1 sm:flex-none bg-gradient-to-r from-blue-600 to-blue-700 text-white px-8 py-3 rounded-lg hover:from-blue-700 hover:to-blue-800 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center space-x-2 shadow-lg transform transition-all duration-200 hover:scale-105 font-medium"
          >
            <svg v-if="isAnalyzing" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>{{ isAnalyzing ? 'Processing Pipeline...' : 'Start Analysis' }}</span>
          </button>
          <button
            @click="clearAnalysis"
            :disabled="isAnalyzing"
            class="bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg transform transition-all duration-200 hover:scale-105 flex items-center justify-center space-x-2 font-medium"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
            </svg>
            <span>Clear All</span>
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

      <!-- Business Summary Section -->
      <div v-if="finalDraft" class="bg-gradient-to-br from-purple-900 to-indigo-900 border border-purple-500 rounded-xl p-8 shadow-2xl mt-16">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-2xl font-bold text-white flex items-center">
            <svg class="w-8 h-8 mr-3 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            Business Summary Report
          </h2>
          <span class="px-3 py-1 bg-purple-600 text-purple-100 text-sm rounded-full font-medium">Executive Analysis</span>
        </div>

        <!-- Executive Summary -->
        <div v-if="finalDraft.executive_summary" class="mb-8">
          <h3 class="text-xl font-bold text-purple-200 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
            </svg>
            Executive Summary
          </h3>
          <div class="bg-purple-950 bg-opacity-50 rounded-lg p-6 border border-purple-700">
            <p class="text-purple-100 leading-relaxed text-lg">{{ finalDraft.executive_summary }}</p>
          </div>
        </div>

        <!-- Key Insights -->
        <div v-if="finalDraft.key_insights && finalDraft.key_insights.length > 0" class="mb-8">
          <h3 class="text-xl font-bold text-purple-200 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
            </svg>
            Key Insights
          </h3>
          <div class="grid gap-4">
            <div v-for="(insight, index) in finalDraft.key_insights" :key="index" class="bg-purple-950 bg-opacity-50 rounded-lg p-6 border border-purple-700">
              <h4 class="font-bold text-purple-200 mb-2">{{ insight.title }}</h4>
              <p class="text-purple-100">{{ insight.description }}</p>
            </div>
          </div>
        </div>

        <!-- Strategic Recommendations -->
        <div v-if="finalDraft.strategic_recommendations && finalDraft.strategic_recommendations.length > 0" class="mb-8">
          <h3 class="text-xl font-bold text-purple-200 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
            </svg>
            Strategic Recommendations
          </h3>
          <div class="grid gap-4">
            <div v-for="(recommendation, index) in finalDraft.strategic_recommendations" :key="index" class="bg-purple-950 bg-opacity-50 rounded-lg p-6 border border-purple-700">
              <h4 class="font-bold text-purple-200 mb-2">{{ recommendation.title }}</h4>
              <p class="text-purple-100">{{ recommendation.description }}</p>
            </div>
          </div>
        </div>

        <!-- Two Column Layout for Opportunities and Risks -->
        <div class="grid md:grid-cols-2 gap-6 mb-8">
          <!-- Market Opportunities -->
          <div v-if="finalDraft.market_opportunities && finalDraft.market_opportunities.length > 0">
            <h3 class="text-lg font-bold text-green-300 mb-4 flex items-center">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
              </svg>
              Market Opportunities
            </h3>
            <div class="space-y-3">
              <div v-for="(opportunity, index) in finalDraft.market_opportunities" :key="index" class="bg-green-950 bg-opacity-30 rounded-lg p-4 border border-green-700">
                <h4 class="font-semibold text-green-200 mb-1">{{ opportunity.title }}</h4>
                <p v-if="opportunity.description" class="text-green-100 text-sm">{{ opportunity.description }}</p>
              </div>
            </div>
          </div>

          <!-- Risk Considerations -->
          <div v-if="finalDraft.risk_considerations && finalDraft.risk_considerations.length > 0">
            <h3 class="text-lg font-bold text-red-300 mb-4 flex items-center">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.99-.833-2.732 0L3.132 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
              </svg>
              Risk Considerations
            </h3>
            <div class="space-y-3">
              <div v-for="(risk, index) in finalDraft.risk_considerations" :key="index" class="bg-red-950 bg-opacity-30 rounded-lg p-4 border border-red-700">
                <h4 class="font-semibold text-red-200 mb-1">{{ risk.title }}</h4>
                <p v-if="risk.description" class="text-red-100 text-sm">{{ risk.description }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Implementation Priorities -->
        <div v-if="finalDraft.implementation_priorities" class="mb-6">
          <h3 class="text-xl font-bold text-purple-200 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
            </svg>
            Implementation Roadmap
          </h3>
          
          <div class="grid md:grid-cols-3 gap-6">
            <!-- High Priority -->
            <div v-if="finalDraft.implementation_priorities.high && finalDraft.implementation_priorities.high.length > 0">
              <h4 class="font-bold text-red-300 mb-3 flex items-center">
                <span class="w-3 h-3 bg-red-500 rounded-full mr-2"></span>
                High Priority
              </h4>
              <ul class="space-y-2">
                <li v-for="(item, index) in finalDraft.implementation_priorities.high" :key="index" class="bg-red-950 bg-opacity-30 rounded p-3 border border-red-700">
                  <span class="text-red-100 text-sm">{{ item }}</span>
                </li>
              </ul>
            </div>

            <!-- Medium Priority -->
            <div v-if="finalDraft.implementation_priorities.medium && finalDraft.implementation_priorities.medium.length > 0">
              <h4 class="font-bold text-yellow-300 mb-3 flex items-center">
                <span class="w-3 h-3 bg-yellow-500 rounded-full mr-2"></span>
                Medium Priority
              </h4>
              <ul class="space-y-2">
                <li v-for="(item, index) in finalDraft.implementation_priorities.medium" :key="index" class="bg-yellow-950 bg-opacity-30 rounded p-3 border border-yellow-700">
                  <span class="text-yellow-100 text-sm">{{ item }}</span>
                </li>
              </ul>
            </div>

            <!-- Long-term -->
            <div v-if="finalDraft.implementation_priorities.long_term && finalDraft.implementation_priorities.long_term.length > 0">
              <h4 class="font-bold text-blue-300 mb-3 flex items-center">
                <span class="w-3 h-3 bg-blue-500 rounded-full mr-2"></span>
                Long-term
              </h4>
              <ul class="space-y-2">
                <li v-for="(item, index) in finalDraft.implementation_priorities.long_term" :key="index" class="bg-blue-950 bg-opacity-30 rounded p-3 border border-blue-700">
                  <span class="text-blue-100 text-sm">{{ item }}</span>
                </li>
              </ul>
            </div>
          </div>
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
const finalDraft = ref(null)
const reasoningContext = ref(null)
const workingContext = ref(null)
const metadata = ref(null)
const metadataSummary = ref(null)
const showDebug = ref(false)

// Model configuration
const selectedModel = ref('gpt-4o-mini')
const useCustomModel = ref(false)
const customApiKey = ref('')
const customModel = ref('')

// File upload
const useDefaultFile = ref(true)
const uploadedFile = ref(null)

let pollInterval = null

const selectFileOption = (useDefault) => {
  console.log('📁 File option selected:', useDefault ? 'Demo file' : 'Upload file')
  useDefaultFile.value = useDefault
  if (useDefault) {
    uploadedFile.value = null
    console.log('✅ Using demo file: VPN Consumer Research Study')
  }
}

const selectModel = (model, isCustom) => {
  console.log('🤖 Model selected:', { model, isCustom })
  selectedModel.value = model
  useCustomModel.value = isCustom
  if (!isCustom) {
    customApiKey.value = ''
    customModel.value = ''
    console.log('✅ Using built-in model:', model)
  } else {
    console.log('🔑 Custom model mode activated')
  }
}

const removeUploadedFile = () => {
  console.log('🗑️ Removing uploaded file:', uploadedFile.value?.name)
  uploadedFile.value = null
  if (this.$refs.fileInput) {
    this.$refs.fileInput.value = ''
  }
}

const handleFileUpload = (event) => {
  const file = event.target.files[0]
  if (file) {
    console.log('📤 File uploaded:', {
      name: file.name,
      size: file.size,
      type: file.type
    })
    uploadedFile.value = file
  }
}

const triggerAnalysis = async () => {
  try {
    console.log('🚀 Starting analysis with config:', {
      useDefaultFile: useDefaultFile.value,
      uploadedFile: uploadedFile.value?.name,
      useCustomModel: useCustomModel.value,
      selectedModel: selectedModel.value,
      customModel: customModel.value,
      hasApiKey: !!customApiKey.value
    })
    
    isAnalyzing.value = true
    
    // Prepare form data for file upload
    const formData = new FormData()
    
    // Add model configuration
    formData.append('use_custom_model', useCustomModel.value)
    formData.append('model', useCustomModel.value ? customModel.value : selectedModel.value)
    if (useCustomModel.value && customApiKey.value) {
      formData.append('api_key', customApiKey.value)
      console.log('🔑 Using custom API key')
    }
    
    // Add file if uploaded
    formData.append('use_default_file', useDefaultFile.value)
    if (!useDefaultFile.value && uploadedFile.value) {
      formData.append('excel_file', uploadedFile.value)
      console.log('📤 Uploading file:', uploadedFile.value.name)
    }
    
    console.log('📡 Sending request to /api/analysis/trigger')
    await axios.post('/api/analysis/trigger', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
    console.log('✅ Analysis triggered successfully')
    startPolling()
  } catch (error) {
    console.error('❌ Failed to trigger analysis:', error)
    isAnalyzing.value = false
  }
}

const clearAnalysis = async () => {
  try {
    console.log('🧹 Clearing all analysis data and jobs...')
    
    // Reset all UI state first
    progress.value = null
    errors.value = {}
    analysisResults.value = null
    finalDraft.value = null
    reasoningContext.value = null
    workingContext.value = null
    metadata.value = null
    metadataSummary.value = null
    isAnalyzing.value = false
    
    // Stop any existing polling
    stopPolling()
    
    // Call clear endpoint that will stop all jobs and clear database
    console.log('📡 Sending clear request - stopping all jobs and clearing database')
    await axios.post('/api/analysis/clear')
    console.log('✅ All jobs stopped and database cleared successfully')
    console.log('👆 Click "Start Analysis" to begin a new analysis')
    
  } catch (error) {
    console.error('❌ Failed to clear analysis:', error)
    isAnalyzing.value = false
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
    
    // DEBUG: Log progress data
    console.log('🔍 FRONTEND POLL:', {
      progress: response.data.progress,
      has_results: response.data.has_results,
      has_business_summary: response.data.has_business_summary,
      is_complete: response.data.is_complete,
      status: response.data.status
    })
    
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
    
    // Map business summary to finalDraft if it exists
    if (response.data.analysis_results && response.data.analysis_results.business_summary) {
      const businessSummary = response.data.analysis_results.business_summary
      
      finalDraft.value = {
        executive_summary: businessSummary.executive_summary,
        key_insights: businessSummary.key_insights.map(insight => ({
          title: `Key Insight ${businessSummary.key_insights.indexOf(insight) + 1}`,
          description: insight
        })),
        strategic_recommendations: businessSummary.recommendations.map(rec => ({
          title: `${rec.priority} Priority: ${rec.action}`,
          description: `${rec.rationale} Expected impact: ${rec.impact}`
        })),
        market_opportunities: businessSummary.risks_and_opportunities.opportunities.map(opp => ({
          title: 'Market Opportunity',
          description: opp
        })),
        risk_considerations: businessSummary.risks_and_opportunities.risks.map(risk => ({
          title: 'Risk Consideration',
          description: risk
        })),
        implementation_priorities: {
          high: businessSummary.recommendations
            .filter(rec => rec.priority === 'High')
            .map(rec => rec.action),
          medium: businessSummary.recommendations
            .filter(rec => rec.priority === 'Medium')
            .map(rec => rec.action),
          long_term: businessSummary.next_steps
        }
      }
    } else {
      finalDraft.value = response.data.final_draft
    }
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

// Feature Flags Configuration
class FeatureFlags {
  static flags = {
    models: {
      gpt4: {
        enabled: false,
        beta: true,
        reason: "Coming Soon"
      },
      gpt4oMini: {
        enabled: true,
        beta: false
      },
      customModel: {
        enabled: false,
        beta: true,
        reason: "Coming Soon"
      }
    },
    fileUpload: {
      uploadFile: {
        enabled: false,
        beta: true,
        reason: "Coming Soon"
      },
      demoFile: {
        enabled: true,
        beta: false
      }
    },
    features: {
      businessSummary: {
        enabled: true,
        beta: false
      },
      debugMode: {
        enabled: true,
        beta: false
      }
    }
  }

  static isEnabled(category, feature) {
    return this.flags[category]?.[feature]?.enabled || false
  }

  static isBeta(category, feature) {
    return this.flags[category]?.[feature]?.beta || false
  }

  static getReason(category, feature) {
    return this.flags[category]?.[feature]?.reason || ''
  }

  static getFlag(category, feature) {
    return this.flags[category]?.[feature] || { enabled: false, beta: false, reason: '' }
  }
}
</script>