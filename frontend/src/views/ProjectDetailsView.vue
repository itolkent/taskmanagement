<template>
    <div class="project-details-page">
        <header class="project-header">
            <h1>{{ project.name }}</h1>
            <p>{{ project.description || 'No description provided.' }}</p>
            <span class="visibility-tag">
                {{ project.visibility }}
            </span>
        </header>
        <section class="team-section">
            <div class="section-header">
                <h2>Team Members</h2>

                <button v-if="['owner', 'admin','project_manager'].includes(myRole)" class="btn-primary" @click="showAddMember = true">
                    + Add Member
                </button>

            </div>

            <div class="member-list">
                <div v-for="m in members" :key="m.user_id" class="member-row">
                    <span>{{ m.name }}</span>
                    <span class="role-tag">{{ roleLabels[m.role] }}</span>

                    <button v-if="['owner', 'admin','project_manager'].includes(myRole) && m.role !== 'owner'" class="remove-btn"
                        @click="removeMember(m.user_id)">
                        Remove
                    </button>
                </div>


            </div>
        </section>
        <div v-if="showAddMember" class="modal-backdrop" @click.self="showAddMember = false">
            <div class="modal">
                <h2>Add Member</h2>

                <select v-model="selectedUser" class="input">
                    <option disabled value="">Select user...</option>
                    <option v-for="u in availableUsers" :key="u.id" :value="u.id">
                        {{ u.name }}
                    </option>
                </select>

                <select v-model="selectedRole" class="input">
                    <option value="team_member">Team Member</option>
                    <option value="project_manager">Project Manager</option>

                    <option value="viewer">Viewer</option>
                </select>

                <div class="modal-actions">
                    <button class="btn-secondary" @click="showAddMember = false">Cancel</button>
                    <button class="btn-primary" @click="addMember">Add</button>
                </div>
            </div>
        </div>

        <section class="boards-section">
            <div class="section-header">
                <h2>Boards</h2>
                <button class="btn-primary" @click="showCreateBoard = true">
                    + Create Board
                </button>
            </div>

            <div class="boards-grid">
                <div v-for="board in boards" :key="board.id" class="board-card" @click="openBoard(board.id)">
                    <h3>{{ board.name }}</h3>
                </div>

                <div class="board-card add-board" @click="showCreateBoard = true">
                    + Add Board
                </div>
            </div>
        </section>

        <div v-if="showCreateBoard" class="modal-backdrop" @click.self="showCreateBoard = false">
            <div class="modal">
                <h2>Create Board</h2>

                <input v-model="boardName" placeholder="Board name" class="input" />

                <div class="modal-actions">
                    <button class="btn-secondary" @click="showCreateBoard = false">Cancel</button>
                    <button class="btn-primary" @click="createBoard">Create</button>
                </div>
            </div>
        </div>

    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useProjectStore } from '../../store'

const route = useRoute()
const router = useRouter()
const projectStore = useProjectStore()

const projectId = Number(route.params.projectId)

const project = computed(() => projectStore.currentProject || {})

const boards = computed(() => projectStore.boards)

const showCreateBoard = ref(false)
const boardName = ref('')
const members = computed(() => {
    return [...projectStore.members].sort((a, b) => {
        const order = ['owner', 'project_manager', 'admin', 'team_member', 'viewer']
        return order.indexOf(a.role) - order.indexOf(b.role)
    })
})
const roleLabels = {
    owner: "Owner",
    project_manager: "Project Manager",
    admin: "Admin",
    team_member: "Team Member",
    viewer: "Viewer"
}

const allUsers = computed(() => projectStore.users)
const myRole = computed(() => projectStore.myRole)

const showAddMember = ref(false)
const selectedUser = ref('')
const selectedRole = ref('member')
const availableUsers = computed(() => {
    const memberIds = members.value.map(m => m.user_id)
    return allUsers.value.filter(u => !memberIds.includes(u.id))
})

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

onMounted(async () => {
    await projectStore.fetchProject(projectId)
    await projectStore.fetchBoards(projectId)
    await projectStore.fetchProjectMembers(projectId)
    await projectStore.fetchAllUsers()
})

function openBoard(boardId) {
    router.push(`/projects/${projectId}/boards/${boardId}`)
}

async function createBoard() {
    if (!boardName.value.trim()) return

    await projectStore.createBoard(projectId, boardName.value.trim())
    boardName.value = ''
    showCreateBoard.value = false
}
</script>

<style scoped>
.project-details-page {
    padding: 2rem;
    max-width: 1000px;
    margin: auto;
}

.project-header {
    margin-bottom: 2rem;
}

.visibility-tag {
    display: inline-block;
    margin-top: 0.5rem;
    padding: 0.3rem 0.6rem;
    background: #eee;
    border-radius: 4px;
    font-size: 0.8rem;
    text-transform: capitalize;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.boards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}

.board-card {
    background: white;
    padding: 1rem;
    border-radius: 6px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    cursor: pointer;
    transition: 0.2s;
}

.board-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 3px 8px rgba(0, 0, 0, 0.15);
}

.add-board {
    display: flex;
    justify-content: center;
    align-items: center;
    font-weight: bold;
    background: #f3f3f3;
}

.modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.4);
    display: flex;
    justify-content: center;
    align-items: center;
}

.modal {
    background: white;
    padding: 2rem;
    border-radius: 6px;
    width: 400px;
    max-width: 90%;
}

.input {
    width: 100%;
    padding: 0.6rem;
    border-radius: 4px;
    border: 1px solid #ccc;
    margin-bottom: 1rem;
}

.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
}

span {
    padding: .3rem;
}
</style>