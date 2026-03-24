<template>
  <div class="projects-page">
    <header class="page-header">
      <h1>Your Projects</h1>
      <button class="btn-primary" @click="showCreate = true">+ New Project</button>
    </header>

    <div v-if="loading" class="loading">Loading projects...</div>

    <div v-else class="projects-grid">
      <div v-for="project in projects" :key="project.id" class="project-card" @click="openProject(project.id)">
        <h2>{{ project.name }}</h2>
        <p>{{ project.description || 'No description' }}</p>
        <span class="visibility">{{ project.visibility }}</span>
      </div>
    </div>

    <div v-if="showCreate" class="modal-backdrop" @click.self="showCreate = false">
      <div class="modal">
        <h2>Create New Project</h2>

        <div class="form-group">
          <label>Name</label>
          <input v-model="form.name" type="text" />
        </div>

        <div class="form-group">
          <label>Description</label>
          <textarea v-model="form.description"></textarea>
        </div>

        <div class="form-group">
          <label>Visibility</label>
          <select v-model="form.visibility">
            <option value="private">Private</option>
            <option value="team">Team</option>
            <option value="public">Public</option>
          </select>
        </div>
        <div class="modal-actions">
          <button class="btn-secondary" @click="showCreate = false">Cancel</button>
          <button class="btn-primary" @click="createProject">Save</button>
        </div>



      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useProjectStore } from '../../store'
import Sidebar from '../api/components/layout/AppSidebar.vue'

const router = useRouter()
const projectStore = useProjectStore()

const loading = ref(true)
const showCreate = ref(false)

const myRole = computed(() => projectStore.myRole);

const canInvite = computed(() =>
  ['owner', 'manager'].includes(myRole.value)
);

const canEditRoles = computed(() =>
  myRole.value === 'owner'
);

const form = ref({
  name: '',
  description: '',
  visibility: 'private'
})

const projects = computed(() => projectStore.projects)

onMounted(async () => {
  await projectStore.fetchProjects()
  loading.value = false
})

function openProject(id) {
  router.push(`/projects/${id}`)
}

async function createProject() {
  if (!form.value.name.trim()) {
    alert('Project name is required')
    return
  }

  try {
    const payload = {
      name: form.value.name,
      description: form.value.description,
      visibility: form.value.visibility
    }

    const res = await fetch('/api/v1/projects', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${localStorage.getItem('tm_token')}`
      },
      body: JSON.stringify(payload)
    })

    const data = await res.json()

    if (!res.ok) {
      alert(data.error || 'Failed to create project')
      return
    }

    projectStore.projects.push(data)

    showCreate.value = false
    form.value = {
      name: '',
      description: '',
      visibility: 'private'
    }

  } catch (err) {
    console.error(err)
    alert('Something went wrong while creating the project')
  }
}
</script>

<style scoped>
.projects-page {
  padding: 2rem;
  max-width: 1000px;
  margin: auto;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.projects-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
  gap: 1rem;
}

.project-card {
  background: #fff;
  padding: 1.2rem;
  border-radius: 6px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
  cursor: pointer;
  transition: 0.2s;
}

.project-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 3px 8px rgba(0, 0, 0, 0.12);
}

.project-card h2 {
  margin: 0 0 0.5rem;
}

.visibility {
  display: inline-block;
  margin-top: 0.5rem;
  padding: 0.2rem 0.5rem;
  background: #eee;
  border-radius: 4px;
  font-size: 0.75rem;
  text-transform: capitalize;
}

.btn-primary {
  background: #3b82f6;
  color: white;
  padding: 0.6rem 1.2rem;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.btn-primary:hover {
  background: #2563eb;
}

.btn-secondary {
  background: #ccc;
  padding: 0.6rem 1.2rem;
  border-radius: 4px;
  border: none;
  cursor: pointer;
}

.loading {
  text-align: center;
  padding: 2rem;
  font-size: 1.2rem;
}

/* Modal */
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

.form-group {
  margin-bottom: 1rem;
}

textarea,
input,
select {
  width: 100%;
  padding: 0.5rem;
  border-radius: 4px;
  border: 1px solid #ccc;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
}
</style>