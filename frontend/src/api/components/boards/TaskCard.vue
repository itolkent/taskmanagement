<template>
  <div v-if="task && task.title && task.title.trim()" class="task-card">
    <div class="task-title">{{ task.title }}</div>

    <div class="assigned-row">
      <template v-if="task.assigned_members && task.assigned_members.length">
        <div class="assigned-item" v-for="m in task.assigned_members" :key="m.id">
          <img :src="avatar(m)" class="avatar-small" />
          <span class="assigned-name">{{ m.name }}</span>
        </div>
      </template>

      <template v-else>
        <span class="not-assigned">Not yet assigned</span>
      </template>
    </div>

    <div class="task-meta">
      <span v-if="task.priority === 'high'" class="badge badge-high">High</span>
      <span v-if="task.priority === 'medium'" class="badge badge-medium">Medium</span>
      <span v-if="task.priority === 'low'" class="badge badge-low">Low</span>

      <span v-if="task.due_date" class="due-date">
        {{ new Date(task.due_date).toLocaleDateString() }}
      </span>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  task: { type: Object, required: true }
})

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
.task-card {
  background: #fff;
  border-radius: 4px;
  padding: 0.5rem 0.7rem;
  margin-bottom: 0.5rem;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.08);
  cursor: pointer;
}

.task-title {
  font-size: 0.95rem;
  font-weight: 500;
}
.assigned-row {
  margin-top: 0.35rem;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.assigned-item {
  display: flex;
  align-items: center;
  gap: 6px;
}

.avatar-small {
  width: 22px;
  height: 22px;
  border-radius: 50%;
}

.assigned-name {
  font-size: 0.75rem;
  color: #333;
}

.not-assigned {
  font-size: 0.75rem;
  color: #777;
  font-style: italic;
}

.task-meta {
  margin-top: 0.25rem;
  display: flex;
  gap: 0.25rem;
  font-size: 0.75rem;
}

.badge {
  padding: 0.1rem 0.4rem;
  border-radius: 3px;
  color: #fff;
}

.badge-high {
  background: #e53935;
}

.badge-medium {
  background: #fb8c00;
}

.badge-low {
  background: #43a047;
}

.due-date {
  margin-left: auto;
  color: #555;
}
</style>