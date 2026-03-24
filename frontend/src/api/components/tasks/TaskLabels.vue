<template>
  <div class="labels">
    <h3>Labels</h3>

    <div class="label-list">
      <span
        v-for="l in taskLabels"
        :key="l.id"
        class="label"
        :style="{ background: l.color }"
        @click="remove(l.id)"
      >
        {{ l.name }} ✕
      </span>
    </div>

    <select v-model="selectedLabel">
      <option disabled value="">Add label…</option>
      <option v-for="l in allLabels" :key="l.id" :value="l.id">
        {{ l.name }}
      </option>
    </select>

    <button @click="add">Add</button>

    <div class="new-label">
      <input v-model="newName" placeholder="New label name" />
      <input type="color" v-model="newColor" />
      <button @click="create">Create</button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import labelsApi from '../../labels';

const props = defineProps({
  taskId: { type: Number, required: true }
});

const allLabels = ref([]);
const taskLabels = ref([]);
const selectedLabel = ref('');
const newName = ref('');
const newColor = ref('#888888');

async function load() {
  const all = await labelsApi.getAll();
  allLabels.value = all.data;

  const task = await labelsApi.getTaskLabels(props.taskId);
  taskLabels.value = task.data;
}

async function add() {
  if (!selectedLabel.value) return;

  await labelsApi.attach(props.taskId, selectedLabel.value);
  await load();
  selectedLabel.value = '';
}

async function remove(labelId) {
  await labelsApi.detach(props.taskId, labelId);
  await load();
}

async function create() {
  if (!newName.value.trim()) return;

  await labelsApi.create(newName.value, newColor.value);
  newName.value = '';
  newColor.value = '#888888';
  await load();
}

onMounted(load);
</script>

<style scoped>
.labels {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.label-list {
  display: flex;
  flex-wrap: wrap;
  gap: 0.3rem;
}

.label {
  padding: 0.2rem 0.5rem;
  border-radius: 4px;
  color: white;
  cursor: pointer;
  font-size: 0.8rem;
}

.new-label {
  display: flex;
  gap: 0.5rem;
}
</style>