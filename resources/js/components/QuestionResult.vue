<template>
  <div class="space-y-4">
    <div class="border-b pb-4">
      <h3 class="text-lg font-semibold">{{ result.question }}</h3>
      <p class="text-sm text-gray-600">{{ result.participants }} participants</p>
      <div class="mt-2 p-3 bg-blue-50 rounded-lg">
        <p class="font-medium text-blue-900">{{ result.headline }}</p>
      </div>
    </div>

    <div class="mb-4">
      <h4 class="font-medium mb-2">Summary</h4>
      <p class="text-gray-700">{{ result.summary }}</p>
    </div>

    <div class="space-y-4">
      <h4 class="font-medium">Themes</h4>
      
      <div v-for="(theme, index) in result.themes" :key="index" class="border rounded-lg p-4">
        <div class="flex justify-between items-start mb-2">
          <h5 class="font-medium">{{ theme.title }}</h5>
          <span class="text-sm text-gray-500 bg-gray-100 px-2 py-1 rounded">
            {{ theme.participants }} participants
          </span>
        </div>
        
        <p class="text-gray-700 mb-3">{{ theme.description }}</p>
        
        <div class="space-y-2">
          <h6 class="text-sm font-medium text-gray-600">Supporting Quotes:</h6>
          <div class="space-y-2">
            <div v-for="(quote, quoteIndex) in theme.quotes" :key="quoteIndex" class="bg-gray-50 p-3 rounded">
              <p class="text-sm italic">"{{ quote.text }}"</p>
              <p class="text-xs text-gray-500 mt-1">— Participant {{ quote.participant_id }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Export Options -->
    <div class="border-t pt-4">
      <h4 class="font-medium mb-2">Export Options</h4>
      <div class="flex gap-2">
        <button
          @click="exportAsJson"
          class="text-sm bg-gray-600 text-white px-3 py-1 rounded hover:bg-gray-700"
        >
          Export JSON
        </button>
        <button
          @click="exportAsMarkdown"
          class="text-sm bg-gray-600 text-white px-3 py-1 rounded hover:bg-gray-700"
        >
          Export Markdown
        </button>
        <button
          @click="copyToClipboard"
          class="text-sm bg-gray-600 text-white px-3 py-1 rounded hover:bg-gray-700"
        >
          Copy to Clipboard
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