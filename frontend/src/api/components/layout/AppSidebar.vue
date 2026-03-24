<template>
    <aside class="sidebar">
        <div>
            <h2 class="app-title">Task Management System</h2>
        </div>
        <div class="user-section">
            <img :src="userAvatar" alt="User Avatar" class="avatar" />

            <div class="user-info">
                <h3 class="user-name">{{ user?.name }}</h3>
                <p class="user-email">{{ user?.email }}</p>
            </div>
        </div>
        <nav class="sidebar-nav">

            <RouterLink :to="projectId ? `/projects/${projectId}/boards` : '/projects'" class="nav-item"
                :class="{ active: isActive(`/projects/${projectId}/boards`) }">
                Projects
            </RouterLink>
            <div v-if="isAdmin" class="nav-item" :class="{ active: route.path.startsWith('/admin') }"
                @click="toggleAdmin">
                Admin Panel
            </div>
            <div v-if="adminOpen" class="submenu">
                <RouterLink to="/admin" class="nav-sub-item" :class="{ active: route.name === 'admin.dashboard' }">
                    Dashboard
                </RouterLink>
                <RouterLink to="/admin/manage-members" class="nav-sub-item"
                    :class="{ active: route.name === 'admin.manage.members' }">
                    Manage Team Members
                </RouterLink>

                <RouterLink to="/admin/settings" class="nav-sub-item"
                    :class="{ active: route.name === 'admin.settings' }">
                    Settings
                </RouterLink>
            </div>
            <RouterLink to="/members" class="nav-item" :class="{ active: isActive('/members') }">Team Member
            </RouterLink>
            <RouterLink to="/settings" class="nav-item">
                Settings
            </RouterLink>

            <button class="nav-item logout-btn" @click="logout">
                Logout
            </button>
        </nav>
    </aside>
</template>

<script setup>
import { computed, watch, ref } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '../../../../store'

const props = defineProps({
    projectId: {
        type: [String, Number],
        required: false,
        default: null
    }
})

const adminOpen = ref(false)

function toggleAdmin() {
    adminOpen.value = !adminOpen.value
}

const route = useRoute()
const auth = useAuthStore()

const user = computed(() => auth.user)

watch(user, (val) => {
    console.log("USER OBJECT:", val)
    console.log("USER ROLE:", val?.role)
})

function logout() {
    auth.logout()
}

const isAdmin = computed(() => {
    const role = user.value?.role
    if (!role) return false
    return role.toLowerCase().trim() === 'admin' || role.toLowerCase().trim() === 'project_manager'

})

const userAvatar = computed(() =>
    user.value?.avatar_url ??
    `https://ui-avatars.com/api/?name=${encodeURIComponent(user.value?.name || 'User')}&background=4f46e5&color=fff`
)

function isActive(path) {
    return route.path.startsWith(path)
}
</script>

<style scoped>
.sidebar {
    width: 260px;
    background: #1e1e1e;
    color: #fff;
    height: 100vh;
    padding: 1.5rem 1rem;
    display: flex;
    flex-direction: column;
}

.user-section {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 2rem;
}

.avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
}

.user-info {
    display: flex;
    flex-direction: column;
}

.user-name {
    font-size: 1rem;
    font-weight: 600;
    margin: 0;
}

.user-email {
    font-size: 0.8rem;
    color: #bbb;
    margin: 0;
}

.sidebar-nav {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.nav-item {
    padding: 0.6rem 0.8rem;
    border-radius: 4px;
    color: #ccc;
    text-decoration: none;
    transition: 0.2s;
}

.nav-item:hover {
    background: #333;
    color: #fff;
}

.nav-item.active {
    background: #4f46e5;
    color: #fff;
}

.logout-btn {
    background: transparent;
    border: none;
    text-align: left;
    cursor: pointer;
    color: #ccc;
    padding: 0.6rem 0.8rem;
    border-radius: 4px;
    transition: 0.2s;
}

.logout-btn:hover {
    background: #333;
    color: #fff;
}

.submenu {
    margin-left: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
}

.nav-sub-item {
    padding: 0.4rem 0.8rem;
    border-radius: 4px;
    color: #bbb;
    text-decoration: none;
    transition: 0.2s;
}

.nav-sub-item:hover {
    background: #333;
    color: #fff;
}

.nav-sub-item.active {
    background: #4f46e5;
    color: #fff;
}
</style>