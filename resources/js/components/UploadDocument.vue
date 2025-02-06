<template>
  <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" v-if="show">
    <div class="fixed inset-0 z-10 overflow-y-auto">
      <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
        <div class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
          <div>
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-blue-100">
              <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
            </div>
            <div class="mt-3 text-center sm:mt-5">
              <h3 class="text-base font-semibold leading-6 text-gray-900">Upload Document</h3>
              <div class="mt-2">
                <p class="text-sm text-gray-500">Select a PDF document to upload for signing.</p>
              </div>
            </div>
          </div>

          <div class="mt-5 sm:mt-6">
            <div class="flex items-center justify-center w-full">
              <label
                :class="[
                  'flex flex-col items-center justify-center w-full h-64 border-2 border-dashed rounded-lg cursor-pointer',
                  isDragging ? 'border-blue-500 bg-blue-50' : 'border-gray-300 hover:border-gray-400'
                ]"
                @dragenter.prevent="isDragging = true"
                @dragleave.prevent="isDragging = false"
                @dragover.prevent
                @drop.prevent="handleDrop"
              >
                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                  <svg class="w-10 h-10 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                  </svg>
                  <p class="mb-2 text-sm text-gray-500">
                    <span class="font-semibold">Click to upload</span> or drag and drop
                  </p>
                  <p class="text-xs text-gray-500">PDF (MAX. 10MB)</p>
                </div>
                <input
                  type="file"
                  class="hidden"
                  accept=".pdf"
                  @change="handleFileSelect"
                  ref="fileInput"
                />
              </label>
            </div>
          </div>

          <div class="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3">
            <button
              type="button"
              class="inline-flex w-full justify-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 sm:col-start-2"
              :disabled="!selectedFile || uploading"
              @click="uploadDocument"
            >
              {{ uploading ? 'Uploading...' : 'Upload' }}
            </button>
            <button
              type="button"
              class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:col-start-1 sm:mt-0"
              @click="$emit('close')"
              :disabled="uploading"
            >
              Cancel
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'UploadDocument',
  props: {
    show: {
      type: Boolean,
      required: true
    }
  },
  data() {
    return {
      selectedFile: null,
      isDragging: false,
      uploading: false
    }
  },
  methods: {
    handleDrop(e) {
      this.isDragging = false
      const files = e.dataTransfer.files
      if (files.length > 0) {
        this.validateAndSetFile(files[0])
      }
    },
    handleFileSelect(e) {
      const files = e.target.files
      if (files.length > 0) {
        this.validateAndSetFile(files[0])
      }
    },
    validateAndSetFile(file) {
      if (file.type !== 'application/pdf') {
        alert('Please select a PDF file')
        return
      }
      if (file.size > 10 * 1024 * 1024) {
        alert('File size should not exceed 10MB')
        return
      }
      this.selectedFile = file
    },
    async uploadDocument() {
      if (!this.selectedFile) return

      this.uploading = true
      const formData = new FormData()
      formData.append('document', this.selectedFile)

      try {
        const response = await fetch('/api/documents', {
          method: 'POST',
          body: formData,
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
          },
          credentials: 'same-origin'
        })

        if (!response.ok) {
          const errorData = await response.json()
          throw new Error(errorData.message || 'Upload failed')
        }

        const result = await response.json()
        this.$emit('uploaded', result)
        this.$emit('close')
      } catch (error) {
        console.error('Upload error:', error)
        alert(error.message || 'Failed to upload document. Please try again.')
      } finally {
        this.uploading = false
        this.selectedFile = null
        if (this.$refs.fileInput) {
          this.$refs.fileInput.value = ''
        }
      }
    }
  }
}
</script>
