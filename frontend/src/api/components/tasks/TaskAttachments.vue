<template>
  <div class="attachments">
    <h3>Attachments</h3>

    <div class="list">
      <div v-for="a in attachments" :key="a.id" class="item">
        <a :href="`/${a.file_path}`" target="_blank">{{ a.file_name }}</a>
        <small>Uploaded by {{ a.uploader_name }}</small>
        <button @click="remove(a.id)">✕</button>
      </div>
    </div>

    <input type="file" @change="handleFile" />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import attachmentsApi from '../../attachments';

const props = defineProps({
  taskId: { type: Number, required: true }
});

const attachments = ref([]);

async function load() {
  const { data } = await attachmentsApi.get(props.taskId);
  attachments.value = data;
}

async function handleFile(e) {
  const file = e.target.files[0];
  if (!file) return;

  const { data } = await attachmentsApi.upload(props.taskId, file);
  attachments.value.unshift(data);
}

async function remove(id) {
  await attachmentsApi.delete(id);
  attachments.value = attachments.value.filter(a => a.id !== id);
}

onMounted(load);
</script>

<style scoped>
.attachments {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.item {
  display: flex;
  flex-direction: column;
  background: #f7f7f7;
  padding: 0.5rem;
  border-radius: 6px;
}

.item button {
  align-self: flex-end;
  background: none;
  border: none;
  color: red;
  cursor: pointer;
}
</style>