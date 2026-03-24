<template>

    <div v-if="showAddMember" class="modal-backdrop" @click.self="showAddMember = false">
        <div class="modal">
            <h2>Add Member</h2>

            <select v-model="selectedUser" class="input">
                <option disabled value="">Select user...</option>
                <option v-for="u in allUsers" :key="u.id" :value="u.id">
                    {{ u.name }}
                </option>
            </select>

            <select v-model="selectedRole" class="input">
                <option value="member">Member</option>
                <option value="viewer">Viewer</option>
                <option value="admin">Admin</option>
            </select>

            <div class="modal-actions">
                <button class="btn-secondary" @click="showAddMember = false">Cancel</button>
                <button class="btn-primary" @click="addMember">Add</button>
            </div>
        </div>
    </div>

</template>
<script>
const myRole = computed(() => projectStore.myRole)
const members = computed(() => projectStore.members)
const allUsers = computed(() => projectStore.users)

const showAddMember = ref(false)
const selectedUser = ref('')
const selectedRole = ref('member')

async function addMember() {
    if (!selectedUser.value) return

    await projectStore.addProjectMember(projectId, {
        user_id: selectedUser.value,
        role: selectedRole.value
    })

    selectedUser.value = ''
    selectedRole.value = 'member'
    showAddMember.value = false
}

async function removeMember(userId) {
    await projectStore.removeProjectMember(projectId, userId)
}

</script>
<style>
.team-section {
    margin-top: 2rem;
}

.member-list {
    margin-top: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.member-row {
    background: #fff;
    padding: 0.7rem;
    border-radius: 6px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.role-tag {
    background: #eee;
    padding: 0.2rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    text-transform: capitalize;
}

.remove-btn {
    background: #e53935;
    color: white;
    border: none;
    padding: 0.3rem 0.6rem;
    border-radius: 4px;
    cursor: pointer;
}
</style>