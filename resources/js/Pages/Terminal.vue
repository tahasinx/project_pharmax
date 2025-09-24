<template>
  <div class="min-h-screen bg-black text-green-400 font-mono">
    <!-- Header -->
    <div class="bg-gray-900 border-b border-gray-700 p-4">
      <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold text-green-400">🚀 Terminal</h1>
        <div class="flex space-x-4">
          <button
            @click="clearOutput"
            class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white rounded text-sm"
          >
            Clear
          </button>
          <button
            @click="toggleFullscreen"
            class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded text-sm"
          >
            {{ isFullscreen ? 'Exit Fullscreen' : 'Fullscreen' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Terminal Output -->
    <div class="flex-1 p-4 overflow-auto h-96" ref="terminalOutput">
      <div class="space-y-2">
        <!-- Welcome Message -->
        <div v-if="outputs.length === 0" class="text-green-300">
          <div class="mb-4">
            <p class="text-lg">Welcome to the Terminal!</p>
            <p class="text-sm text-gray-400">Execute system commands directly from your browser.</p>
          </div>

          <div class="text-sm">
            <p class="mb-2"><span class="text-yellow-400">Available Commands:</span></p>
            <ul class="ml-4 space-y-1">
              <li><span class="text-blue-400">composer</span> install, update, dump-autoload, require, remove</li>
              <li><span class="text-blue-400">npm</span> install, run, build, dev, prod, test</li>
              <li><span class="text-blue-400">artisan</span> migrate, make:controller, cache:clear, etc.</li>
              <li><span class="text-blue-400">php</span> --version, -v, -m</li>
              <li><span class="text-blue-400">git</span> status, pull, push, add, commit</li>
              <li><span class="text-blue-400">ls</span> -la, -l, -a</li>
              <li><span class="text-blue-400">pwd</span>, <span class="text-blue-400">whoami</span>, <span class="text-blue-400">date</span></li>
            </ul>
          </div>
        </div>

        <!-- Command Outputs -->
        <div
          v-for="(output, index) in outputs"
          :key="index"
          class="border border-gray-700 rounded p-3 bg-gray-900"
        >
          <!-- Command Header -->
          <div class="flex items-center justify-between mb-2">
            <div class="flex items-center space-x-2">
              <span class="text-gray-400">$</span>
              <span class="text-white">{{ output.command }}</span>
            </div>
            <div class="flex items-center space-x-2">
              <span
                :class="output.success ? 'text-green-400' : 'text-red-400'"
                class="text-sm"
              >
                {{ output.success ? '✅ Success' : '❌ Failed' }}
              </span>
              <span class="text-gray-500 text-xs">
                {{ formatTime(output.timestamp) }}
              </span>
            </div>
          </div>

          <!-- Command Output -->
          <div
            v-if="output.output"
            class="text-gray-300 text-sm whitespace-pre-wrap font-mono"
          >
            {{ output.output }}
          </div>

          <!-- Error Output -->
          <div
            v-if="output.error"
            class="text-red-400 text-sm whitespace-pre-wrap font-mono mt-2"
          >
            {{ output.error }}
          </div>

          <!-- Execution Info -->
          <div class="text-gray-500 text-xs mt-2">
            Exit Code: {{ output.exit_code }} |
            Working Directory: {{ output.working_directory }}
          </div>
        </div>
      </div>
    </div>

    <!-- Command Input -->
    <div class="bg-gray-900 border-t border-gray-700 p-4">
      <form @submit.prevent="executeCommand" class="flex items-center space-x-2">
        <span class="text-green-400">$</span>
        <input
          v-model="currentCommand"
          ref="commandInput"
          type="text"
          placeholder="Enter command (e.g., composer install)"
          class="flex-1 bg-black text-white border border-gray-600 rounded px-3 py-2 focus:outline-none focus:border-green-400"
          :disabled="isExecuting"
        />
        <button
          type="submit"
          :disabled="isExecuting || !currentCommand.trim()"
          class="px-4 py-2 bg-green-600 hover:bg-green-700 disabled:bg-gray-600 disabled:cursor-not-allowed text-white rounded"
        >
          {{ isExecuting ? 'Executing...' : 'Execute' }}
        </button>
      </form>

      <!-- Quick Actions -->
      <div class="mt-3 flex flex-wrap gap-2">
        <button
          v-for="quickCommand in quickCommands"
          :key="quickCommand.command"
          @click="executeQuickCommand(quickCommand)"
          :disabled="isExecuting"
          class="px-3 py-1 bg-gray-700 hover:bg-gray-600 disabled:bg-gray-800 disabled:cursor-not-allowed text-gray-300 text-sm rounded"
        >
          {{ quickCommand.label }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue'

const terminalOutput = ref(null)
const commandInput = ref(null)
const currentCommand = ref('')
const isExecuting = ref(false)
const isFullscreen = ref(false)
const outputs = ref([])

const quickCommands = [
  { label: 'Composer Install',     command: 'composer install' },
  { label: 'Composer Update',      command: 'composer update' },
  { label: 'NPM Install',          command: 'npm install' },
  { label: 'NPM Build',           command: 'npm run build' },
  { label: 'Artisan Migrate',     command: 'php artisan migrate' },
  { label: 'Artisan Cache Clear', command: 'php artisan cache:clear' },
  { label: 'PHP Version',         command: 'php --version' },
  { label: 'Node Version',        command: 'node --version' },
  { label: 'Git Status',          command: 'git status' },
  { label: 'List Files (dir)',    command: 'dir' },
  { label: 'Current Directory',   command: 'cd' }
]

const executeCommand = async () => {
  if (!currentCommand.value.trim() || isExecuting.value) return

  const commandParts = currentCommand.value.trim().split(' ')
  const command = commandParts[0]
  const commandArgs = commandParts.slice(1)

  isExecuting.value = true

  try {
    const response = await fetch('/terminal/execute', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      },
      body: JSON.stringify({
        command,
        arguments: commandArgs,
        working_directory: null
      })
    })

    const result = await response.json()

    outputs.value.unshift({
      ...result,
      timestamp: new Date(),
      command: currentCommand.value.trim()
    })

    // Keep only last 50 outputs
    if (outputs.value.length > 50) {
      outputs.value = outputs.value.slice(0, 50)
    }

  } catch (error) {
    outputs.value.unshift({
      success: false,
      error: error.message,
      output: '',
      exit_code: 1,
      timestamp: new Date(),
      command: currentCommand.value.trim()
    })
  } finally {
    isExecuting.value = false
    currentCommand.value = ''
    await nextTick()
    scrollToBottom()
  }
}

const executeQuickCommand = (quickCommand) => {
  currentCommand.value = quickCommand.command
  executeCommand()
}

const clearOutput = () => {
  outputs.value = []
}

const toggleFullscreen = () => {
  if (!document.fullscreenElement) {
    document.documentElement.requestFullscreen()
    isFullscreen.value = true
  } else {
    document.exitFullscreen()
    isFullscreen.value = false
  }
}

const scrollToBottom = () => {
  if (terminalOutput.value) {
    terminalOutput.value.scrollTop = terminalOutput.value.scrollHeight
  }
}

const formatTime = (timestamp) => {
  return timestamp.toLocaleTimeString()
}

onMounted(() => {
  commandInput.value?.focus()
})
</script>

<style scoped>
/* Custom scrollbar */
.overflow-auto::-webkit-scrollbar {
  width: 8px;
}

.overflow-auto::-webkit-scrollbar-track {
  background: #374151;
}

.overflow-auto::-webkit-scrollbar-thumb {
  background: #6b7280;
  border-radius: 4px;
}

.overflow-auto::-webkit-scrollbar-thumb:hover {
  background: #9ca3af;
}
</style>
