import { createRouter, createWebHistory } from 'vue-router'
import Dashboard from './components/Dashboard.vue'
import Documents from './components/Documents.vue'
import Login from './components/Auth/Login.vue'
import Register from './components/Auth/Register.vue'

const routes = [
    {
        path: '/',
        name: 'dashboard',
        component: Dashboard,
        meta: { requiresAuth: true }
    },
    {
        path: '/documents',
        name: 'documents',
        component: Documents,
        meta: { requiresAuth: true }
    },
    {
        path: '/login',
        name: 'login',
        component: Login,
        meta: { guest: true }
    },
    {
        path: '/register',
        name: 'register',
        component: Register,
        meta: { guest: true }
    }
]

const router = createRouter({
    history: createWebHistory(),
    routes
})

router.beforeEach(async (to, from, next) => {
    const isAuthenticated = await checkAuth()
    
    if (to.matched.some(record => record.meta.requiresAuth)) {
        if (!isAuthenticated) {
            next({ name: 'login' })
        } else {
            next()
        }
    } else if (to.matched.some(record => record.meta.guest)) {
        if (isAuthenticated) {
            next({ name: 'dashboard' })
        } else {
            next()
        }
    } else {
        next()
    }
})

async function checkAuth() {
    try {
        const response = await fetch('/api/user')
        return response.ok
    } catch {
        return false
    }
}

export default router
