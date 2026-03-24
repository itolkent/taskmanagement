<template>
    <div class="admin-settings">
        <h1 class="title">System Settings</h1>

        <section class="settings-section">
            <h2>General</h2>

            <div class="setting-item">
                <label>System Name</label>
                <input v-model="settings.systemName" type="text" />
            </div>

            <div class="setting-item">
                <label>Default User Role</label>
                <select v-model="settings.defaultRole">
                    <option value="team_member">Team Member</option>
                    <option value="project_manager">Project Manager</option>
                </select>
            </div>
        </section>

        <section class="settings-section">
            <h2>Security</h2>

            <div class="setting-item">
                <label>Require Strong Passwords</label>
                <input type="checkbox" v-model="settings.requireStrongPasswords" />
            </div>

            <div class="setting-item">
                <label>Session Timeout (minutes)</label>
                <input v-model.number="settings.sessionTimeout" type="number" min="5" />
            </div>
        </section>

        <button class="save-btn" @click="saveSettings">
            Save Settings
        </button>
    </div>
</template>

<script setup>
import { reactive, onMounted } from 'vue'
import api from '../../../api/client' 
const settings = reactive({
    systemName: '',
    defaultRole: 'team_member',
    requireStrongPasswords: false,
    sessionTimeout: 30
})

async function loadSettings() {
    const { data } = await api.get('/admin/settings')
    Object.assign(settings, data)
}

async function saveSettings() {
    await api.put('/admin/settings', settings)
    alert('Settings saved successfully')
}

onMounted(() => {
    loadSettings()
})
</script>

<style scoped>
.admin-settings {
    padding: 2rem;
}

.title {
    font-size: 1.8rem;
    margin-bottom: 1.5rem;
}

.settings-section {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
}

.settings-section h2 {
    margin-bottom: 1rem;
    font-size: 1.2rem;
    color: #444;
}

.setting-item {
    display: flex;
    flex-direction: column;
    margin-bottom: 1rem;
}

.setting-item label {
    font-weight: 600;
    margin-bottom: 0.4rem;
}

.setting-item input,
.setting-item select {
    padding: 0.5rem;
    border-radius: 4px;
    border: 1px solid #ccc;
}

.save-btn {
    padding: 0.8rem 1.4rem;
    background: #4f46e5;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 1rem;
    transition: 0.2s;
}

.save-btn:hover {
    background: #3b36c7;
}
</style>