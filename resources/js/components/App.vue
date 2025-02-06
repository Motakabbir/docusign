<template>
  <div>
    <nav v-if="isAuthenticated" class="bg-gray-800">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <router-link to="/" class="text-white font-bold text-xl">DocuSign Clone</router-link>
            </div>
            <div class="hidden md:block">
              <div class="ml-10 flex items-baseline space-x-4">
                <router-link
                  to="/"
                  class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium"
                  :class="{ 'bg-gray-900 text-white': $route.name === 'dashboard' }"
                >
                  Dashboard
                </router-link>
                <router-link
                  to="/documents"
                  class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium"
                  :class="{ 'bg-gray-900 text-white': $route.name === 'documents' }"
                >
                  Documents
                </router-link>
              </div>
            </div>
          </div>
          <div class="hidden md:block">
            <div class="ml-4 flex items-center md:ml-6">
              <button
                @click="logout"
                class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium"
              >
                Sign Out
              </button>
            </div>
          </div>
        </div>
      </div>
    </nav>

    <main>
      <router-view></router-view>
    </main>
  </div>
</template>

<script>
export default {
  name: 'App',
  data() {
    return {
      isAuthenticated: false
    }
  },
  async created() {
    await this.checkAuth()
  },
  methods: {
    async checkAuth() {
      try {
        const response = await fetch('/api/user')
        this.isAuthenticated = response.ok
      } catch {
        this.isAuthenticated = false
      }
    },
    async logout() {
      try {
        // First, get a fresh CSRF token
        await fetch('/sanctum/csrf-cookie')
        
        const response = await fetch('/api/logout', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
          },
          credentials: 'include' // Important for handling session cookies
        })

        if (!response.ok) {
          const error = await response.json()
          throw new Error(error.message || 'Logout failed')
        }

        // Clear authentication state
        this.isAuthenticated = false
        
        // Redirect to login page
        await this.$router.push('/login')
        
        // Reload the page to clear any cached state
        window.location.reload()
      } catch (error) {
        console.error('Logout error:', error)
        alert(error.message || 'Failed to logout. Please try again.')
      }
    }
  }
}
</script>
