<template>
    <div class="admin-dashboard">
        <h1 class="title">Admin Dashboard</h1>

        <div class="grid">
            <div class="card">
                <h2>Total Users</h2>
                <p class="value">{{ stats.totalUsers }}</p>
            </div>

            <div class="card">
                <h2>Admins</h2>
                <p class="value">{{ stats.adminCount }}</p>
            </div>

            <div class="card">
                <h2>Project Managers</h2>
                <p class="value">{{ stats.pmCount }}</p>
            </div>
            <div class="card">
                <h2>Team Members</h2>
                <p class="value">{{ stats.memberCount }}</p>
            </div>
        </div>

        <div class="quick-links">
            <RouterLink to="/admin/manage-members" class="quick-link">
                Manage Users
            </RouterLink>

            <RouterLink to="/projects" class="quick-link">
                View All Projects
            </RouterLink>
        </div>
    </div>
</template>

<script setup>
import { reactive, onMounted } from 'vue'
import api from '../../../api/client'

const stats = reactive({
    totalUsers: 0,
    adminCount: 0,
    pmCount: 0,
    memberCount: 0
})

async function loadStats() {
    const { data } = await api.get('/admin/users', {
        params: { page: 1, limit: 9999 } 
    })

    const users = data

    stats.totalUsers = users.length
    stats.adminCount = users.filter(u => u.role === 'admin').length
    stats.pmCount = users.filter(u => u.role === 'project_manager').length
    stats.memberCount = users.filter(u => u.role === 'team_member').length

}

onMounted(() => {
    loadStats()
})
</script>

<style scoped>
.admin-dashboard {
    padding: 2rem;
}

.title {
    font-size: 2rem;
    margin-bottom: 1.5rem;
}

.grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.card {
    background: #ffffff;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
}

.card h2 {
    margin: 0;
    font-size: 1.2rem;
    color: #555;
}

.value {
    font-size: 2rem;
    font-weight: bold;
    margin-top: 0.5rem;
    color: #4f46e5;
}

.quick-links {
    display: flex;
    gap: 1rem;
}

.quick-link {
    padding: 0.8rem 1.2rem;
    background: #4f46e5;
    color: white;
    border-radius: 6px;
    text-decoration: none;
    transition: 0.2s;
}

.quick-link:hover {
    background: #3b36c7;
}
</style>