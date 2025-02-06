<template>
  <div class="bg-white shadow overflow-hidden sm:rounded-lg">
    <div class="px-4 py-5 sm:px-6">
      <h3 class="text-lg leading-6 font-medium text-gray-900">Recent Documents</h3>
      <p class="mt-1 max-w-2xl text-sm text-gray-500">Documents that need your attention.</p>
    </div>
    <div class="border-t border-gray-200">
      <ul role="list" class="divide-y divide-gray-200">
        <li v-for="document in documents" :key="document.id" class="px-4 py-4 sm:px-6">
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
              </div>
              <div class="ml-4">
                <div class="text-sm font-medium text-blue-600">{{ document.name }}</div>
                <div class="text-sm text-gray-500">Added {{ document.date }}</div>
              </div>
            </div>
            <div class="flex space-x-2">
              <button
                class="inline-flex items-center px-3 py-1 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              >
                Sign
              </button>
              <button
                class="inline-flex items-center px-3 py-1 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              >
                View
              </button>
            </div>
          </div>
        </li>
      </ul>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Dashboard',
  data() {
    return {
      documents: [
        { id: 1, name: 'Contract Agreement.pdf', date: '2 hours ago' },
        { id: 2, name: 'NDA Document.pdf', date: '1 day ago' },
        { id: 3, name: 'Employment Agreement.pdf', date: '3 days ago' },
      ]
    }
  }
}
</script>
