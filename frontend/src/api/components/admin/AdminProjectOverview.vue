<template>
    <div class="admin-projects">
        <h1 class="title">All Projects</h1>

        <table class="project-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Project Name</th>
                    <th>Owner</th>
                    <th>Members</th>
                    <th>Boards</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                <tr v-for="project in projects" :key="project.id">
                    <td>{{ project.id }}</td>
                    <td>{{ project.name }}</td>
                    <td>{{ project.owner?.name || 'Unknown' }}</td>
                    <td>{{ project.member_count }}</td>
                    <td>{{ project.board_count }}</td>

                    <td>
                        <RouterLink :to="`/projects/${project.id}`" class="view-btn">
                            View
                        </RouterLink>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../../../client' // adjust if needed

const projects = ref([])

async function loadProjects() {
    const { data } = await api.get('/admin/projects')
    projects.value = data
}

onMounted(() => {
    loadProjects()
})
</script>

<style scoped>
.admin-projects {
    padding: 2rem;
}

.title {
    font-size: 1.8rem;
    margin-bottom: 1.5rem;
}

.project-table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    border-radius: 8px;
    overflow: hidden;
}

.project-table th,
.project-table td {
    padding: 0.9rem;
    border-bottom: 1px solid #eee;
    text-align: left;
}

.project-table th {
    background: #f3f4f6;
    font-weight: 600;
}

.view-btn {
    padding: 0.4rem 0.8rem;
    background: #4f46e5;
    color: white;
    border-radius: 4px;
    text-decoration: none;
    transition: 0.2s;
}

.view-btn:hover {
    background: #3b36c7;
}
</style>