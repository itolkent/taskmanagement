<template>
  <div class="board-view">

    <div v-if="boardLists.length === 0" class="empty-state">
      <p>No lists yet. Create your first list.</p>

      <button class="btn-primary" @click="creatingList = true">
        + Add List
      </button>
    </div>

    <div v-else class="lists-container">
      <TaskListColumn v-for="list in boardLists" :key="list.id" :list="list" @create-task="onCreateTask"
        @task-drop="onTaskDrop" @open-task="openTaskModal" />


      <div class="add-list" @click="creatingList = true">
        + Add List
      </div>
    </div>

    <div v-if="creatingList" class="modal-backdrop" @click.self="cancelList">
      <div class="modal">
        <h3>Create List</h3>
        <input v-model="newListName" placeholder="List name" />
        <div class="actions">
          <button @click="cancelList">Cancel</button>
          <button @click="submitList">Create</button>
        </div>
      </div>
    </div>

    <TaskModal v-if="showTaskModal" :task="activeTask" @close="closeTaskModal" @save="saveTask" />

  </div>
</template>

<script setup>
import { computed, ref, onMounted, watch } from 'vue'

import { useProjectStore } from '../../../../store'
import TaskListColumn from './TaskListColumn.vue'
import TaskModal from '../tasks/TaskModal.vue'

const props = defineProps({
  boardId: { type: [String, Number], required: true },
  projectId: { type: [String, Number], required: false }
})

const boardId = Number(props.boardId)
const projectStore = useProjectStore()

const boardLists = computed(() => projectStore.boardLists)


onMounted(async () => {
  await projectStore.fetchProject(props.projectId)
  await projectStore.fetchProjectMembers(props.projectId)
  await projectStore.fetchBoard(boardId)
})
const creatingList = ref(false)
const newListName = ref('')

async function submitList() {
  const name = newListName.value.trim()
  if (!name) return

  await projectStore.createList(boardId, name)
  newListName.value = ''
  creatingList.value = false
}

function cancelList() {
  creatingList.value = false
  newListName.value = ''
}

function onCreateTask(list, title) {
  projectStore.createTask(list.id, title)
}

const showTaskModal = ref(false)
const activeTask = ref(null)

async function openTaskModal(task) {
  await projectStore.fetchProjectMembers(props.projectId)
  activeTask.value = task
  showTaskModal.value = true
}

function closeTaskModal() {
  showTaskModal.value = false
  activeTask.value = null
}

async function saveTask(updated) {
  await projectStore.updateTask(updated.id, updated)
  closeTaskModal()
}
function onTaskDrop({ task, toListId, newIndex }) {
  projectStore.updateTask(task.id, {
    task_list_id: toListId,
    sort_order: newIndex
  })
}
</script>