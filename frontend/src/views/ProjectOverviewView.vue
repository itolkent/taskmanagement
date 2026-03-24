<template>
    <div class="overview">
        <h1 class="title">{{ project?.name }}</h1>

        <p class="description" v-if="project?.description">
            {{ project.description }}
        </p>

        <div class="meta">
            <span class="tag">{{ project?.visibility }}</span>
            <span class="tag" v-if="project?.archived">Archived</span>
        </div>

        <div class="section">
            <h2>Members</h2>

            <div class="members">
                <div v-for="m in members" :key="m.user_id" class="member">
                    <div class="avatar">
                        {{ m.name.charAt(0).toUpperCase() }}
                    </div>
                    <span>{{ m.name }}</span>
                </div>
            </div>

            <router-link :to="`/projects/${projectId}/members`" class="btn small">
                Manage Members
            </router-link>
        </div>

        <div class="section">
            <h2>Boards</h2>

            <div class="boards">
                <router-link v-for="b in boards" :key="b.id" :to="`/projects/${projectId}/boards/${b.id}`"
                    class="board-card">
                    {{ b.name }}
                </router-link>
            </div>

            <button class="btn" @click="createBoard">
                + Create Board
            </button>
        </div>

        <!-- Settings -->
        <div class="section">
            <router-link :to="`/projects/${projectId}/settings`" class="btn secondary">
                Project Settings
            </router-link>
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref, computed } from 'vue'
import { useRoute } from 'vue-router'
import { useProjectStore } from '../../store/index'

const route = useRoute()
const projectStore = useProjectStore()

const projectId = route.params.projectId

const project = computed(() => projectStore.currentProject)
const boards = computed(() => projectStore.boards)
const members = computed(() => projectStore.members)

onMounted(async () => {
    await projectStore.fetchProject(projectId)
    await projectStore.fetchBoards(projectId)
    await projectStore.fetchMembers(projectId)
})

async function createBoard() {
    const name = prompt('Board name:')
    if (!name) return

    await projectStore.createBoard(projectId, name)
}
</script>

<style scoped>
.overview {
    max-width: 900px;
    margin: 0 auto;
    padding: 20px;
}

.title {
    margin-bottom: 10px;
}

.description {
    margin-bottom: 15px;
    color: #555;
}

.meta {
    margin-bottom: 25px;
}

.tag {
    background: #e5e7eb;
    padding: 4px 10px;
    border-radius: 6px;
    margin-right: 8px;
    font-size: 13px;
}

.section {
    margin-bottom: 40px;
}

.members {
    display: flex;
    gap: 15px;
    margin-bottom: 15px;
}

.member {
    display: flex;
    align-items: center;
    gap: 8px;
}

.avatar {
    width: 34px;
    height: 34px;
    background: #2563eb;
    color: white;
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
}

.boards {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 15px;
}

.board-card {
    background: #f3f4f6;
    padding: 15px 20px;
    border-radius: 8px;
    text-decoration: none;
    color: #111;
    font-weight: 500;
    border: 1px solid #ddd;
}

.btn {
    background: #2563eb;
    color: white;
    padding: 10px 16px;
    border-radius: 6px;
    border: none;
    cursor: pointer;
}

.btn.small {
    padding: 6px 12px;
    font-size: 14px;
}

.btn.secondary {
    background: #6b7280;
}
</style>