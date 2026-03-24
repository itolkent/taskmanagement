<template>
  <div class="subtasks">
    <div v-for="s in subtasks" :key="s.id" class="subtask">
      <input
        type="checkbox"
        v-model="s.is_completed"
        @change="toggle(s)"
      />

      <input
        class="title"
        v-model="s.title"
        @blur="save(s)"
      />

      <button class="delete" @click="remove(s.id)">✕</button>
    </div>

    <div class="add">
      <input
        v-model="newTitle"
        placeholder="Add subtask…"
        @keyup.enter="add"
      />
      <button @click="add">Add</button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import subtasksApi from '../../subtasks';

const props = defineProps({
  taskId: { type: Number, required: true }
});

const subtasks = ref([]);
const newTitle = ref('');

async function load() {
  const { data } = await subtasksApi.get(props.taskId);
  subtasks.value = data;
}

async function add() {
  if (!newTitle.value.trim()) return;

  const { data } = await subtasksApi.create(props.taskId, newTitle.value);
  subtasks.value.push(data);
  newTitle.value = '';
}

async function save(s) {
  await subtasksApi.update(s.id, {
    title: s.title,
    is_completed: s.is_completed ? 1 : 0,
    sort_order: s.sort_order
  });
}

async function toggle(s) {
  s.is_completed = s.is_completed ? 1 : 0;
  await save(s);
}

async function remove(id) {
  await subtasksApi.delete(id);
  subtasks.value = subtasks.value.filter(s => s.id !== id);
}

onMounted(load);
</script>

<style scoped>
.subtasks {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.subtask {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.subtask .title {
  flex: 1;
}

.delete {
  background: none;
  border: none;
  color: red;
  cursor: pointer;
}

.add {
  display: flex;
  gap: 0.5rem;
}
</style>