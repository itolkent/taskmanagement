<template>
  <div class="modal-backdrop" @click.self="close">
    <div class="modal">
      <header>
        <button @click="close">✕</button>
        <input v-model="localTask.title" @blur="save" class="title-input" />

        <textarea v-model="localTask.description" @blur="save" placeholder="Add a description... "
          class="comment-input"></textarea>

      </header>

      <section class="assigned-section">
        <label>Assigned To:</label>

        <div v-if="localTask.assignee" class="assigned-box">
          <img :src="avatar(localTask.assignee)" class="avatar-small" />
          <span>{{ localTask.assignee.name }}</span>
        </div>

        <div v-else class="not-assigned">Not yet assigned</div>

        <select v-if="canEditTask" v-model="selectedAssignee" @change="assignMember">
          <option disabled value="">Assign member...</option>
          <option v-for="m in projectMembers" :key="m.user_id" :value="m.user_id">
            {{ m.name }}
          </option>
        </select>
      </section>

      <section class="priority-section">
        <label>Priority:</label>

        <select v-if="canEditTask" v-model="localTask.priority" @change="updatePriority" :disabled="isTeamMember">
          <option value="low">Low</option>
          <option value="medium">Medium</option>
          <option value="high">High</option>
        </select>
        <div v-else class="prio-level">
          {{ localTask.priority.charAt(0).toUpperCase() + localTask.priority.slice(1) }}
        </div>

      </section>

      <section class="comments-section">
        <h4>Comments</h4>

        <div v-if="localTask.comments && localTask.comments.length">
          <div v-for="c in localTask.comments" :key="c.id" class="comment-item">
            <strong>{{ c.user.name }}:</strong>
            <p>{{ c.text }}</p>
          </div>
        </div>

        <textarea v-model="newComment" placeholder="Write a comment..." class="comment-input"></textarea>

        <button class="btn" @click="addComment">Add Comment</button>
      </section>

      <footer>
        <button @click="save">Save</button>
      </footer>
    </div>
  </div>
</template>
<script setup>
import { reactive, watch, ref, computed } from 'vue'
import { useProjectStore } from '../../../../store'
import { useAuthStore } from '../../../../store'

const auth = useAuthStore()

const emit = defineEmits(['close', 'save'])
const props = defineProps({
  task: Object,
  show: Boolean
})

const canEditTask = computed(() => {
  return ['admin', 'project_manager', 'owner'].includes(myRole.value)
})

const projectStore = useProjectStore()
const myRole = computed(() => projectStore.myRole)
console.log("MOdal MYROLE", myRole)
const isTeamMember = computed(() => myRole.value === 'team_member')


const projectMembers = computed(() => projectStore.members || [])
console.log("Members:", projectMembers.value)

const localTask = reactive({
  id: null,
  title: '',
  description: '',
  assignee: null,
  assignee_id: null,
  comments: [],
  priority: 'medium'
})

const selectedAssignee = ref('')
const newComment = ref('')

watch(
  () => props.task,
  (t) => {
    if (t) {
      if (props.show && t.id === localTask.id) {
        return
      }
      localTask.id = t.id
      localTask.title = t.title
      localTask.description = t.description
      localTask.assignee =
        t.assignee ||
        t.assigned_members?.[0] ||
        null

      localTask.assignee_id = t.assignee_id || null
      localTask.comments = t.comments || []
      localTask.priority = t.priority || 'medium'
    }
  },
  { immediate: true }
)

function close() {
  emit('close')
}
async function save() {
  const payload = {
    id: localTask.id,
    title: localTask.title,
    description: localTask.description,
    priority: localTask.priority,
    assignee_id: localTask.assignee_id ?? null
  }
  console.log("Saving task:", JSON.stringify(payload, null, 2))

  await projectStore.updateTask(localTask.id, payload)
  emit("save", payload)
}

async function assignMember() {
  if (!canEditTask.value) return

  const user = projectMembers.value.find(m => m.user_id == selectedAssignee.value)

  localTask.assignee = user
  localTask.assignee_id = user.user_id

  selectedAssignee.value = ''
}

const updatePriority = () => {
  if (!canEditTask.value) return

  projectStore.updateTask(localTask.id, { priority: localTask.priority })
}

async function addComment() {
  if (!newComment.value.trim()) return

  const comment = await projectStore.addComment(localTask.id, newComment.value)

  localTask.comments.push(comment)
  newComment.value = ''
}

function avatar(user) {
  return (
    user.avatar_url ||
    `https://ui-avatars.com/api/?name=${encodeURIComponent(
      user.name
    )}&background=4f46e5&color=fff`
  )
}
</script>

<style scoped>
.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
}

.modal {
  background: white;
  width: 600px;
  padding: 1.5rem;
  border-radius: 8px;
}

.title-input {
  width: 100%;
  font-size: 1.4rem;
  font-weight: bold;
}

.assigned-section {
  margin: 1rem 0;
}

.prio-level{
  margin-top: 0.4rem;
  padding: 0.4rem 0.6rem;
  background: #f3f3f3;
  border-radius: 4px;
  display: inline-block;
  font-weight: 600;
  color: #444;
}

.assigned-box {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 0.5rem;
}

.not-assigned {
  font-style: italic;
  color: #777;
  margin-bottom: 0.5rem;
}

.avatar-small {
  width: 28px;
  height: 28px;
  border-radius: 50%;
}

.comments-section {
  margin-top: 1rem;
}

.comment-item {
  background: #f5f5f5;
  padding: 0.5rem;
  border-radius: 4px;
  margin-bottom: 0.5rem;
}

.comment-input {
  width: 100%;
  min-height: 60px;
  margin-top: 0.5rem;
}

.btn {
  margin-top: 0.5rem;
}
</style>