<template>
    <div class="invite-container">
        <div class="invite-card">
            <h1>Joining Project…</h1>

            <p v-if="loading">Validating your invite, please wait…</p>

            <p v-if="error" class="error">
                {{ error }}
            </p>

            <button v-if="error" class="btn" @click="goHome">
                Go to Dashboard
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../api/client'
import { useProjectStore } from '../../store/index'

const route = useRoute()
const router = useRouter()
const projectStore = useProjectStore()

const loading = ref(true)
const error = ref(null)

onMounted(async () => {
    const token = route.query.token

    if (!token) {
        error.value = 'Invalid invite link'
        loading.value = false
        return
    }

    try {
        const { data } = await api.post('/invites/accept', { token })

        await projectStore.fetchProject(data.project_id)

        router.push(`/projects/${data.project_id}`)
    } catch (err) {
        error.value = err.response?.data?.error || 'Failed to accept invite'
    }

    loading.value = false
})

function goHome() {
    router.push('/projects')
}
</script>

<style scoped>
.invite-container {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 80vh;
}

.invite-card {
    background: white;
    padding: 30px;
    border-radius: 10px;
    width: 380px;
    text-align: center;
    border: 1px solid #ddd;
}

.error {
    color: #dc2626;
    margin-top: 10px;
    margin-bottom: 20px;
}

.btn {
    background: #2563eb;
    color: white;
    padding: 10px 16px;
    border-radius: 6px;
    border: none;
    cursor: pointer;
}
</style>