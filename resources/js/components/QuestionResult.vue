<template>
  <div class="space-y-6 text-white">
    <!-- Question Header -->
    <div class="border-b border-gray-600 pb-4">
      <h3 class="text-xl font-bold text-white mb-2">{{ result.question }}</h3>
      <div class="flex items-center space-x-4 text-sm">
        <span class="flex items-center text-gray-300">
          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
          </svg>
          {{ result.participants }} participants
        </span>
      </div>
      <div class="mt-3 p-4 bg-gradient-to-r from-blue-900/30 to-purple-900/30 rounded-lg border border-blue-700/50">
        <p class="font-medium text-blue-200">{{ result.headline }}</p>
      </div>
    </div>

    <!-- Summary -->
    <div class="bg-gray-900 rounded-lg p-4 border border-gray-700">
      <h4 class="font-bold text-white mb-3 flex items-center">
        <svg class="w-5 h-5 mr-2 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        Summary
      </h4>
      <p class="text-gray-300 leading-relaxed">{{ result.summary }}</p>
    </div>

    <!-- Themes -->
    <div class="space-y-4">
      <h4 class="font-bold text-white text-lg flex items-center">
        <svg class="w-6 h-6 mr-2 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
        </svg>
        Themes
      </h4>
      
      <div class="space-y-4">
        <div v-for="(theme, index) in result.themes" :key="index" class="bg-gray-900 border border-gray-700 rounded-lg overflow-hidden">
          <details class="group">
            <summary class="cursor-pointer p-4 hover:bg-gray-800 transition-colors list-none">
              <div class="flex justify-between items-start">
                <div class="flex-1">
                  <div class="flex items-center space-x-3 mb-2">
                    <h5 class="font-bold text-white">{{ theme.title }}</h5>
                    <span class="px-2 py-1 bg-blue-600 text-blue-100 text-xs rounded-full">
                      {{ theme.participants }} participants
                    </span>
                  </div>
                  <p class="text-gray-400 text-sm">{{ theme.description }}</p>
                </div>
                <svg class="w-5 h-5 text-gray-400 transform transition-transform group-open:rotate-180 flex-shrink-0 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
              </div>
            </summary>
            
            <div class="border-t border-gray-700 p-4 bg-gray-850">
              <h6 class="text-sm font-medium text-gray-300 mb-3 flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                </svg>
                Supporting Quotes
              </h6>
              <div class="space-y-3">
                <div v-for="(quote, quoteIndex) in theme.quotes" :key="quoteIndex" class="bg-gray-800 border border-gray-600 p-3 rounded-lg">
                  <p class="text-gray-200 italic leading-relaxed">"{{ quote.text }}"</p>
                  <p class="text-xs text-gray-400 mt-2 flex items-center">
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    Participant {{ quote.participant_id }}
                  </p>
                </div>
              </div>
            </div>
          </details>
        </div>
      </div>
    </div>

    <!-- Metadata Section -->
    <div v-if="metadata" class="border-t border-gray-600 pt-6 mt-6">
      <details class="group">
        <summary class="cursor-pointer font-medium text-yellow-400 hover:text-yellow-300 flex items-center justify-between p-3 bg-gray-900 rounded-lg border border-gray-700">
          <div class="flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
            Analysis Metadata
          </div>
          <svg class="w-4 h-4 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
          </svg>
        </summary>
        <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
          <div class="bg-blue-900/30 border border-blue-700 p-3 rounded-lg">
            <p class="text-xs text-blue-300 mb-1">Tokens Used</p>
            <p class="font-bold text-blue-200">{{ metadata.tokens?.total?.toLocaleString() || 'N/A' }}</p>
          </div>
          <div class="bg-green-900/30 border border-green-700 p-3 rounded-lg">
            <p class="text-xs text-green-300 mb-1">Cost</p>
            <p class="font-bold text-green-200">${{ metadata.costs?.total?.toFixed(4) || '0.0000' }}</p>
          </div>
          <div class="bg-purple-900/30 border border-purple-700 p-3 rounded-lg">
            <p class="text-xs text-purple-300 mb-1">Duration</p>
            <p class="font-bold text-purple-200">{{ metadata.duration_seconds?.toFixed(1) || 0 }}s</p>
          </div>
          <div class="bg-gray-800 border border-gray-600 p-3 rounded-lg">
            <p class="text-xs text-gray-400 mb-1">Participants</p>
            <p class="font-bold text-gray-200">{{ metadata.participant_count || 0 }}</p>
          </div>
        </div>
        <div class="mt-3 text-xs text-gray-400 space-y-1 bg-gray-900 p-3 rounded-lg border border-gray-700">
          <p class="flex items-center">
            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
            </svg>
            Model: {{ metadata.model || 'N/A' }}
          </p>
          <p class="flex items-center">
            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
            </svg>
            Input: {{ metadata.tokens?.input?.toLocaleString() || 0 }} | Output: {{ metadata.tokens?.output?.toLocaleString() || 0 }} tokens
          </p>
          <p class="flex items-center">
            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Processed: {{ metadata.timestamp ? new Date(metadata.timestamp).toLocaleString() : 'N/A' }}
          </p>
        </div>
      </details>
    </div>

    <!-- Export Options -->
    <div class="border-t border-gray-600 pt-4">
      <h4 class="font-medium text-white mb-3 flex items-center">
        <svg class="w-5 h-5 mr-2 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        Export Options
      </h4>
      <div class="flex gap-3 flex-wrap">
        <button
          @click="exportAsJson"
          class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm flex items-center space-x-2 border border-gray-600 transition-all duration-200 hover:border-blue-500"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
          </svg>
          <span>JSON</span>
        </button>
        <button
          @click="exportAsMarkdown"
          class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm flex items-center space-x-2 border border-gray-600 transition-all duration-200 hover:border-green-500"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707L16.414 6.5A1 1 0 0015.586 6H7a2 2 0 00-2 2v11a2 2 0 002 2z"></path>
          </svg>
          <span>Markdown</span>
        </button>
        <button
          @click="copyToClipboard"
          class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm flex items-center space-x-2 border border-gray-600 transition-all duration-200 hover:border-purple-500"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
          </svg>
          <span>Copy</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineProps } from 'vue'

const props = defineProps({
  questionKey: {
    type: String,
    required: true
  },
  result: {
    type: Object,
    required: true
  },
  metadata: {
    type: Object,
    default: null
  }
})

const exportAsJson = () => {
  const dataStr = JSON.stringify(props.result, null, 2)
  const dataUri = 'data:application/json;charset=utf-8,'+ encodeURIComponent(dataStr)
  
  const exportFileDefaultName = `${props.questionKey}_analysis.json`
  
  const linkElement = document.createElement('a')
  linkElement.setAttribute('href', dataUri)
  linkElement.setAttribute('download', exportFileDefaultName)
  linkElement.click()
}

const exportAsMarkdown = () => {
  let markdown = `# ${props.result.question}\n\n`
  markdown += `**Participants:** ${props.result.participants}\n\n`
  markdown += `**Headline:** ${props.result.headline}\n\n`
  markdown += `## Summary\n\n${props.result.summary}\n\n`
  markdown += `## Themes\n\n`
  
  props.result.themes.forEach((theme, index) => {
    markdown += `### ${index + 1}. ${theme.title}\n\n`
    markdown += `**Description:** ${theme.description}\n\n`
    markdown += `**Participants:** ${theme.participants}\n\n`
    markdown += `**Supporting Quotes:**\n\n`
    
    theme.quotes.forEach((quote) => {
      markdown += `- "${quote.text}" - Participant ${quote.participant_id}\n`
    })
    
    markdown += `\n`
  })
  
  const dataStr = markdown
  const dataUri = 'data:text/markdown;charset=utf-8,'+ encodeURIComponent(dataStr)
  
  const exportFileDefaultName = `${props.questionKey}_analysis.md`
  
  const linkElement = document.createElement('a')
  linkElement.setAttribute('href', dataUri)
  linkElement.setAttribute('download', exportFileDefaultName)
  linkElement.click()
}

const copyToClipboard = async () => {
  let text = `${props.result.question}\n`
  text += `Participants: ${props.result.participants}\n`
  text += `Headline: ${props.result.headline}\n\n`
  text += `Summary: ${props.result.summary}\n\n`
  text += `Themes:\n\n`
  
  props.result.themes.forEach((theme, index) => {
    text += `${index + 1}. ${theme.title}\n`
    text += `Description: ${theme.description}\n`
    text += `Participants: ${theme.participants}\n`
    text += `Supporting Quotes:\n`
    
    theme.quotes.forEach((quote) => {
      text += `- "${quote.text}" - Participant ${quote.participant_id}\n`
    })
    
    text += `\n`
  })
  
  try {
    await navigator.clipboard.writeText(text)
    // You could show a toast notification here
    console.log('Copied to clipboard')
  } catch (err) {
    console.error('Failed to copy: ', err)
  }
}
</script>