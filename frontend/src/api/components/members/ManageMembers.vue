<template>
    <div class="members-page">
        <h1 class="title">Manage Team Members</h1>

        <div v-if="loading" class="loading">Loading members...</div>

        <table v-else class="members-table">
            <thead>
                <tr>
                    <th>Avatar</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th class="actions-col">Actions</th>
                </tr>
            </thead>

            <tbody>
                <tr v-for="user in members" :key="user.id">
                    <td>
                        <img :src="avatar(user)" class="avatar" />
                    </td>

                    <td>{{ user.name }}</td>
                    <td>{{ user.email }}</td>

                    <td>
                        <select v-model="user.role" class="role-select">

                            <option value="project_manager">Project Manager</option>
                            <option value="team_member">Team Member</option>
                        </select>
                    </td>

                    <td class="actions">
                        <button class="btnSave " @click="updateRole(user)">Save</button>
                        <button class="btn  " @click="removeUser(user)">Remove</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../../client'
import { useProjectStore } from '../../../../store'

const members = ref([])
const loading = ref(true)
const projectStore = useProjectStore()

onMounted(async () => {
    try {
        const res = await api.get('/admin/users')

        const allUsers = Array.isArray(res.data) ? res.data : []

        members.value = allUsers.filter(user => {
            const role = user.role?.toLowerCase().trim()
            return role !== 'admin'
        })
    } catch (err) {
        console.error('Failed to load members:', err)
    } finally {
        loading.value = false
    }
})

function avatar(user) {
    return (
        user.avatar_url ||
        `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=4f46e5&color=fff`
    )
}

async function updateRole(user) {
    try {
        await projectStore.updateUserRole(user.id, user.role)
        alert(`Role updated to ${user.role}`)
    } catch (err) {
        console.error("Failed to update role:", err)
        alert("Error updating role")
    }
}


function removeUser(user) {
    console.log("Remove user:", user)
}
</script>

<style scoped>
.members-page {
    padding: 1.5rem;
}

.title {
    font-size: 1.6rem;
    font-weight: 600;
    margin-bottom: 1rem;
}

.loading {
    color: #888;
}

.members-table {
    width: 100%;
    border-collapse: collapse;
    background: #1e1e1e;
    color: #fff;
    border-radius: 8px;
    overflow: hidden;
}

.members-table th,
.members-table td {
    padding: 0.8rem;
    border-bottom: 1px solid #333;
}

.members-table th {
    background: #2a2a2a;
    text-align: left;
}

.avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
}

.role-select {
    background: #2a2a2a;
    color: #fff;
    padding: 0.3rem;
    border-radius: 4px;
    border: 1px solid #444;
}

.actions-col {
    width: 150px;
}

.actions {
    display: flex;
    gap: 0.5rem;
}

.btn {
    padding: 0.3rem 0.6rem;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    color: #fff;
    background: #d9534f;
}

.btnSave {
    padding: 0.3rem 0.6rem;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    color: #030303;
    font-size: 0.8rem;
}

.btn:hover {
    opacity: 0.8;
}
</style>