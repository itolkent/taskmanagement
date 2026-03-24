<template>
    <div class="admin-container">
        <h1 class="title">Admin – Manage User Roles</h1>

        <table class="user-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Change Role</th>
                </tr>
            </thead>

            <tbody>
                <tr v-for="user in adminUsers" :key="user.id">
                    <td>{{ user.id }}</td>
                    <td>{{ user.name }}</td>
                    <td>{{ user.email }}</td>

                    <td>
                        <span class="role-badge" :class="user.role">
                            {{ user.role }}
                        </span>
                    </td>

                    <td>
                        <select v-model="selectedRoles[user.id]" @change="updateRole(user.id)" class="role-select">
                            <option value="admin">Admin</option>
                            <option value="project_manager">Project Manager</option>
                            <option value="team_member">Team Member</option>
                        </select>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="pagination">
            <button v-for="p in pagination.total_pages" :key="p" @click="fetchPage(p)"
                :class="{ active: p === pagination.page }">
                {{ p }}
            </button>
        </div>
    </div>
</template>

<script setup>
import { reactive, onMounted } from 'vue'
import { useProjectStore } from '../../../../store'

const store = useProjectStore()

const adminUsers = store.adminUsers
const pagination = store.pagination

const selectedRoles = reactive({})

async function fetchPage(page = 1) {
    await store.fetchAdminUsers(page, pagination.limit)

    store.adminUsers.forEach(u => {
        selectedRoles[u.id] = u.role
    })
}

async function updateRole(userId) {
    const newRole = selectedRoles[userId]
    await store.updateUserRole(userId, newRole)
}

onMounted(() => {
    fetchPage(1)
})
</script>

<style scoped>
.admin-container {
    padding: 2rem;
}

.title {
    font-size: 1.8rem;
    margin-bottom: 1.5rem;
}

.user-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 1.5rem;
}

.user-table th,
.user-table td {
    padding: 0.8rem;
    border-bottom: 1px solid #ddd;
    text-align: left;
}

.role-badge {
    padding: 0.3rem 0.6rem;
    border-radius: 4px;
    color: white;
    text-transform: capitalize;
}

.role-badge.admin {
    background: #4f46e5;
}

.role-badge.project_manager {
    background: #0ea5e9;
}

.role-badge.team_member {
    background: #10b981;
}

.role-select {
    padding: 0.4rem;
    border-radius: 4px;
    border: 1px solid #ccc;
}

.pagination {
    display: flex;
    gap: 0.5rem;
}

.pagination button {
    padding: 0.4rem 0.8rem;
    border: none;
    background: #eee;
    cursor: pointer;
    border-radius: 4px;
}

.pagination button.active {
    background: #4f46e5;
    color: white;
}
</style>
