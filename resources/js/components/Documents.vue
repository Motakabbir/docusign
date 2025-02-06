<template>
  <div>
    <div class="bg-white px-4 py-5 border-b border-gray-200 sm:px-6">
      <div class="-ml-4 -mt-2 flex items-center justify-between flex-wrap sm:flex-nowrap">
        <div class="ml-4 mt-2">
          <h3 class="text-lg leading-6 font-medium text-gray-900">All Documents</h3>
        </div>
        <div class="ml-4 mt-2 flex-shrink-0">
          <button
            type="button"
            @click="showUploadModal = true"
            class="relative inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            Upload New Document
          </button>
        </div>
      </div>
    </div>
    
    <div class="flex flex-col mt-6">
      <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
        <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
          <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
            <div v-if="loading" class="bg-white p-4 text-center">
              <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
              <p class="mt-2 text-sm text-gray-500">Loading documents...</p>
            </div>

            <div v-else-if="error" class="bg-white p-4 text-center">
              <div class="text-red-600 mb-2">
                <svg class="h-6 w-6 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <p class="text-sm text-gray-900">{{ error }}</p>
              <button
                @click="fetchDocuments"
                class="mt-3 inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
              >
                Try Again
              </button>
            </div>

            <div v-else-if="!documents.length" class="bg-white p-4 text-center">
              <p class="text-sm text-gray-500">No documents found</p>
            </div>

            <table v-else class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Document
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Status
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Date Added
                  </th>
                  <th scope="col" class="relative px-6 py-3">
                    <span class="sr-only">Actions</span>
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="document in documents" :key="document.id">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="flex-shrink-0 h-10 w-10">
                        <svg class="h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                      </div>
                      <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">
                          {{ document.name }}
                        </div>
                        <div class="text-sm text-gray-500">
                          {{ formatSize(document.size) }}
                        </div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span
                      :class="[
                        document.status === 'signed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800',
                        'px-2 inline-flex text-xs leading-5 font-semibold rounded-full'
                      ]"
                    >
                      {{ document.status }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ formatDate(document.created_at) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <button 
                      @click="viewDocument(document)"
                      class="text-blue-600 hover:text-blue-900 mr-4"
                    >
                      View
                    </button>
                    <button 
                      @click="downloadDocument(document)"
                      class="text-blue-600 hover:text-blue-900 mr-4"
                    >
                      Download
                    </button>
                    <button 
                      v-if="document.status !== 'signed'"
                      @click="openSignModal(document)"
                      class="text-blue-600 hover:text-blue-900"
                    >
                      Sign
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Upload Modal -->
    <UploadDocument
      :show="showUploadModal"
      @close="showUploadModal = false"
      @uploaded="handleDocumentUploaded"
    />

    <!-- Sign Modal -->
    <SignDocument
      :show="showSignModal"
      :document="selectedDocument"
      @close="showSignModal = false"
      @signed="handleDocumentSigned"
      v-if="selectedDocument"
    />

    <!-- View Modal -->
    <ViewDocument
      :show="showViewModal"
      :document="selectedDocument"
      @close="showViewModal = false"
      v-if="selectedDocument"
    />
  </div>
</template>

<script>
import UploadDocument from './UploadDocument.vue'
import SignDocument from './SignDocument.vue'
import ViewDocument from './ViewDocument.vue'

export default {
  name: 'Documents',
  components: {
    UploadDocument,
    SignDocument,
    ViewDocument
  },
  data() {
    return {
      documents: [],
      showUploadModal: false,
      showSignModal: false,
      showViewModal: false,
      selectedDocument: null,
      loading: false,
      error: null
    }
  },
  mounted() {
    this.fetchDocuments()
  },
  methods: {
    async fetchDocuments() {
      this.loading = true
      this.error = null
      try {
        const response = await fetch('/api/documents', {
          headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
          },
          credentials: 'include'
        })

        if (!response.ok) {
          const errorData = await response.json()
          throw new Error(errorData.message || 'Failed to fetch documents')
        }

        const data = await response.json()
        this.documents = Array.isArray(data) ? data : []
      } catch (error) {
        console.error('Error fetching documents:', error)
        this.error = error.message || 'Failed to load documents'
      } finally {
        this.loading = false
      }
    },
    formatSize(bytes) {
      const sizes = ['Bytes', 'KB', 'MB', 'GB']
      if (bytes === 0) return '0 Byte'
      const i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)))
      return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i]
    },
    formatDate(date) {
      return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      })
    },
    async downloadDocument(document) {
      try {
        const response = await fetch(`/api/documents/${document.id}/download`)
        if (!response.ok) throw new Error('Failed to download document')
        
        const blob = await response.blob()
        const url = window.URL.createObjectURL(blob)
        const a = document.createElement('a')
        a.href = url
        a.download = document.name
        document.body.appendChild(a)
        a.click()
        window.URL.revokeObjectURL(url)
        a.remove()
      } catch (error) {
        console.error('Error downloading document:', error)
        alert('Failed to download document')
      }
    },
    viewDocument(document) {
      this.selectedDocument = document
      this.showViewModal = true
    },
    openSignModal(document) {
      this.selectedDocument = document
      this.showSignModal = true
    },
    handleDocumentSigned(document) {
      const index = this.documents.findIndex(d => d.id === document.id)
      if (index !== -1) {
        this.documents[index] = document
      }
      this.showSignModal = false
      this.showViewModal = false
    },
    handleDocumentUploaded(document) {
      this.documents.unshift(document)
      this.showUploadModal = false
    }
  }
}
</script>
