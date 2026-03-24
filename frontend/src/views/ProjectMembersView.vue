<template>
    <div class="members-page">
        <h1>Project Members</h1>

        <!-- Members List -->
        <div class="section">
            <h2>Members</h2>

            <table class="members-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th></th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="m in members" :key="m.user_id">
                        <td>{{ m.name }}</td>
                        <td>{{ m.email }}</td>

                        <td>
                            <select v-model="m.role" @change="changeRole(m)">
                                <option value="owner">Owner</option>
                                <option value="admin">Admin</option>
                                <option value="member">Member</option>
                                <option value="viewer">Viewer</option>
                            </select>
                        </td>

                        <td>
                            <button class="btn danger" @click="remove(m)">
                                Remove
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="section">
            <h2>Pending Invites</h2>

            <ul>
                <li v-for="i in invites" :key="i.id">
                    {{ i.email }} — {{ i.role }} (pending)
                </li>
            </ul>
        </div>

        <div class="section">
            <h2>Invite Someone</h2>

            <input v-model="inviteEmail" placeholder="Email" />
            <select v-model="inviteRole">
                <option value="member">Member</option>
                <option value="viewer">Viewer</option>
                <option value="admin">Admin</option>
            </select>

            <button class="btn" @click="sendInviteAction">
                Send Invite
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import { useProjectStore } from '../../store/index'

const route = useRoute()
const projectStore = useProjectStore()

const projectId = route.params.projectId

const members = computed(() => projectStore.members)
const invites = computed(() => projectStore.invites)

const inviteEmail = ref('')
const inviteRole = ref('member')

onMounted(async () => {
    await projectStore.fetchMembers(projectId)
})

async function sendInviteAction() {
    if (!inviteEmail.value) return alert('Email required')

    await projectStore.sendInvite(projectId, inviteEmail.value, inviteRole.value)
    inviteEmail.value = ''
}

async function changeRole(member) {
    await projectStore.updateMemberRole(projectId, member.user_id, member.role)
}

async function remove(member) {
    if (!confirm('Remove this member?')) return
    await projectStore.removeMember(projectId, member.user_id)
}
</script>

<style scoped>
.members-page {
    max-width: 900px;
    margin: 0 auto;
    padding: 20px;
}

.section {
    margin-bottom: 40px;
}

.members-table {
    width: 100%;
    border-collapse: collapse;
}

.members-table th,
.members-table td {
    padding: 10px;
    border-bottom: 1px solid #ddd;
}

.btn {
    background: #2563eb;
    color: white;
    padding: 6px 12px;
    border-radius: 6px;
    border: none;
    cursor: pointer;
}

.btn.danger {
    background: #dc2626;
}
</style>