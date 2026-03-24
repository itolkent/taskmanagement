<template>
  <div class="drawer-backdrop" @click.self="$emit('close')">
    <div class="drawer">
      <header>
        <input v-model="localTask.title" />
        <button @click="save">Save</button>
        <button @click="$emit('close')">Close</button>
      </header>

      <section>
        <label>Description</label>
        <textarea v-model="localTask.description" rows="4"></textarea>
      </section>

      <section class="grid">
        <div>
          <label>Status</label>
          <select v-model="localTask.status">
            <option value="not_started">Not Started</option>
            <option value="in_progress">In Progress</option>
            <option value="completed">Completed</option>
            <option value="on_hold">On Hold</option>
          </select>
        </div>
        <div>
          <label>Priority</label>
          <select v-model="localTask.priority">
            <option value="low">Low</option>
            <option value="medium">Medium</option>
            <option value="high">High</option>
          </select>
        </div>
        <div>
          <label>Due date</label>
          <input type="date" v-model="dueDate" />
        </div>
      </section>

      <section>
        <h3>Subtasks</h3>
        <SubtaskChecklist :task-id="localTask.id" />
      </section>
      <section>
         <TaskComments :task-id="localTask.id" />
        </section>
        <section>
            <TaskLabels :task-id="localTask.id" />
        </section>
        <section>
          <TaskAttachments :task-id="localTask.id" />
        </section>
        <section>
          <TaskActivity :task-id="localTask.id" />
        </section>



    </div>
  </div>
</template>

<script setup>
import { reactive, watch, computed } from 'vue';
import TaskComments from './TaskComments.vue';

import SubtaskChecklist from './SubtaskChecklist.vue';
import TaskLabels from './TaskLabels.vue';
import TaskAttachments from './TaskAttachments.vue';
import TaskActivity from './TaskActivity.vue';

const props = defineProps({
  task: { type: Object, required: true }
});
const emits = defineEmits(['close', 'save']);

const localTask = reactive({ ...props.task });

const dueDate = computed({
  get() {
    return localTask.due_date ? localTask.due_date.substring(0, 10) : '';
  },
  set(val) {
    localTask.due_date = val ? new Date(val).toISOString().slice(0, 19).replace('T', ' ') : null;
  }
});

watch(
  () => props.task,
  (val) => {
    Object.assign(localTask, val);
  }
);

function save() {
  emits('save', { ...localTask });
}
</script>

<style scoped>
.drawer-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.4);
  display: flex;
  justify-content: flex-end;
}
.drawer {
  background: #fff;
  width: 420px;
  max-width: 100%;
  height: 100%;
  padding: 1rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}
header {
  display: flex;
  gap: 0.5rem;
}
header input {
  flex: 1;
}
.grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 0.5rem;
}
section label {
  display: block;
  font-size: 0.8rem;
  margin-bottom: 0.2rem;
}
</style>