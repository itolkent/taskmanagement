<template>
  <div class="task-list-column">
    <h3 class="list-title">{{ list.name }}</h3>

  
    <div ref="listEl" class="task-list">
      <TaskCard v-for="task in list.task_lists" :key="task.id" :task="task" @click="$emit('open-task', task)" />
    </div>

    <input v-model="newTitle" @keyup.enter="create" placeholder="Add a task..." class="add-task-input" />
  </div>
</template>

<script setup>
import { onMounted, ref, computed, watch } from 'vue'
import Sortable from 'sortablejs'
import TaskCard from './TaskCard.vue'

import { useAuthStore } from '../../../../store/auth'
import { useProjectStore } from '../../../../store'

const auth = useAuthStore()
const projectStore = useProjectStore()

const projectMembers = computed(() => projectStore.members)

const props = defineProps({
  list: { type: Object, required: true }
})

const emit = defineEmits(['task-drop', 'create-task', 'open-task', 'assign-member'])

const listEl = ref(null)
const newTitle = ref('')
const selectedMember = ref('')

function assignMember() {
  if (!selectedMember.value) return

  emit('assign-member', {
    listId: props.list.id,
    userId: selectedMember.value
  })

  selectedMember.value = ''
}

onMounted(() => {
  Sortable.create(listEl.value, {
    group: 'task_lists',
    animation: 150,
    onEnd(evt) {
      const task = props.list.task_lists[evt.oldIndex]

      emit('task-drop', {
        task,
        toListId: props.list.id,
        newIndex: evt.newIndex
      })
    }
  })
})

watch(projectMembers, () => {
  console.log("PROJECT MEMBERS:", projectMembers.value)
})

function create() {
  const title = newTitle.value.trim()
  if (!title) return

  emit('create-task', props.list, title)
  newTitle.value = ''
}

function avatar(user) {
  return (
    user.avatar_url ||
    `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=4f46e5&color=fff`
  )
}
</script>

<style scoped>
.task-list-column {
  background: #f5f5f5;
  border-radius: 6px;
  padding: 0.75rem;
  min-width: 260px;
  max-height: calc(100vh - 140px);
  display: flex;
  flex-direction: column;
}

.assigned-members {
  margin-bottom: 0.5rem;
  display: flex;
  flex-direction: column;
  gap: 0.3rem;
}

.label {
  font-size: 0.8rem;
  color: #555;
}

.avatars {
  display: flex;
  gap: 4px;
}

.avatar-small {
  width: 26px;
  height: 26px;
  border-radius: 50%;
}

.assign-select {
  padding: 0.3rem;
  border-radius: 4px;
  border: 1px solid #ccc;
  font-size: 0.8rem;
}

.task-list {
  flex: 1;
  overflow-y: auto;
  padding: 0.5rem 0;
}

.add-task-input {
  width: 100%;
  padding: 0.45rem 0.6rem;
  border-radius: 4px;
  border: 1px solid #ccc;
  font-size: 0.9rem;
}
</style>