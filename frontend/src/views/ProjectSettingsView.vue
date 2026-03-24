<template>
    <div class="project-settings">
        <h1 class="title">Project Settings</h1>

        <div class="settings-card">
            <h2>General</h2>

            <label>
                <span>Project Name</span>
                <input v-model="form.name" type="text" />
            </label>

            <label>
                <span>Description</span>
                <textarea v-model="form.description"></textarea>
            </label>

            <label>
                <span>Visibility</span>
                <select v-model="form.visibility">
                    <option value="private">Private</option>
                    <option value="public">Public</option>
                    <option value="team">Team</option>
                </select>
            </label>

            <button class="btn-save" @click="saveSettings" :disabled="loading">
                {{ loading ? 'Saving...' : 'Save Changes' }}
            </button>
        </div>

        <div class="settings-card danger">
            <h2>Danger Zone</h2>

            <button class="btn-archive" @click="toggleArchive" :disabled="loading">
                {{ project.archived ? 'Unarchive Project' : 'Archive Project' }}
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import { useProjectStore } from '../../store/index'
import api from '../api/client'

const route = useRoute()
const projectStore = useProjectStore()

const projectId = route.params.projectId
const loading = ref(false)

const project = computed(() => projectStore.currentProject || {})

const form = ref({
    name: '',
    description: '',
    visibility: 'private'
})

onMounted(async () => {
    const data = await projectStore.fetchProject(projectId)
    form.value = {
        name: data.name,
        description: data.description,
        visibility: data.visibility
    }
})

async function saveSettings() {
    loading.value = true

    try {
        const { data } = await api.put(`/projects/${projectId}`, form.value)
        projectStore.currentProject = data
        alert('Settings saved')
    } catch (err) {
        console.error(err)
        alert('Failed to save settings')
    }

    loading.value = false
}

async function toggleArchive() {
    loading.value = true

    try {
        const { data } = await api.put(`/projects/${projectId}`, {
            archived: project.value.archived ? 0 : 1
        })
        projectStore.currentProject = data
    } catch (err) {
        console.error(err)
        alert('Failed to update archive state')
    }

    loading.value = false
}
</script>

<style scoped>
.project-settings {
    max-width: 700px;
    margin: 0 auto;
    padding: 20px;
}

.title {
    margin-bottom: 20px;
}

.settings-card {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 25px;
    border: 1px solid #ddd;
}

.settings-card h2 {
    margin-bottom: 15px;
}

label {
    display: block;
    margin-bottom: 15px;
}

label span {
    display: block;
    font-weight: 600;
    margin-bottom: 5px;
}

input,
textarea,
select {
    width: 100%;
    padding: 8px;
    border-radius: 6px;
    border: 1px solid #ccc;
}

textarea {
    min-height: 100px;
}

.btn-save {
    background: #2563eb;
    color: white;
    padding: 10px 16px;
    border-radius: 6px;
    border: none;
    cursor: pointer;
}

.btn-archive {
    background: #dc2626;
    color: white;
    padding: 10px 16px;
    border-radius: 6px;
    border: none;
    cursor: pointer;
}

.settings-card.danger {
    border-color: #dc2626;
}
</style>