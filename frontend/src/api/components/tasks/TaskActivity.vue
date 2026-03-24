<template>
  <div class="activity">
    <h3>Activity</h3>

    <div v-if="loading">Loading activity…</div>

    <div v-for="a in activity" :key="a.id" class="entry">
      <div class="meta">
        <strong>{{ a.user_name }}</strong>
        <small>{{ formatDate(a.created_at) }}</small>
      </div>
      <p>{{ a.action }}</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import activityApi from '../../activity';

const props = defineProps({
  taskId: { type: Number, required: true }
});

const activity = ref([]);
const loading = ref(true);

async function load() {
  loading.value = true;
  const { data } = await activityApi.get(props.taskId);
  activity.value = data;
  loading.value = false;
}

function formatDate(dt) {
  return new Date(dt).toLocaleString();
}

onMounted(load);
</script>

<style scoped>
.activity {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.entry {
  background: #f7f7f7;
  padding: 0.7rem;
  border-radius: 6px;
}

.meta {
  display: flex;
  justify-content: space-between;
  font-size: 0.8rem;
  margin-bottom: 0.3rem;
}
</style>