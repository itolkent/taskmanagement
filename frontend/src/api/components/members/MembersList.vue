<template>
    <div class="members-page">
        <h1 class="title">Team Members</h1>

        <div v-if="loading" class="loading">Loading members...</div>

        <div v-else class="members-list">
            <div v-for="user in members" :key="user.id" class="member-card">
                <img :src="avatar(user)" class="avatar" alt="User Avatar" />

                <div class="info">
                    <h3>{{ user.name }}</h3>
                    <p>{{ user.email }}</p>
                    <span class="role">{{ user.role }}</span>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../../client'

const members = ref([])
const loading = ref(true)

onMounted(async () => {
    try {
        const res = await api.get('/users')

        const allUsers = Array.isArray(res.data) ? res.data : []

        console.log("ALL USERS:", allUsers)

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

.members-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.member-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    background: #1e1e1e;
    padding: 1rem;
    border-radius: 8px;
    color: #fff;
}

.avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
}

.info h3 {
    margin: 0;
    font-size: 1.1rem;
}

.info p {
    margin: 0;
    color: #bbb;
}

.role {
    font-size: 0.8rem;
    color: #4f46e5;
}
</style>