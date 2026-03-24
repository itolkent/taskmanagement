<template>
  <div class="calendar-view">
    <div class="calendar-header">
      <button @click="prevMonth">&lt;</button>
      <h2>{{ monthLabel }}</h2>
      <button @click="nextMonth">&gt;</button>
    </div>
    <div class="calendar-grid">
      <div class="day-name" v-for="d in dayNames" :key="d">{{ d }}</div>
      <div
        v-for="day in days"
        :key="day.date.toISOString()"
        class="day-cell"
      >
        <div class="day-number">{{ day.date.getDate() }}</div>
        <div class="tasks">
          <div
            v-for="task in day.tasks"
            :key="task.id"
            class="task-pill"
          >
            {{ task.title }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import api from '../../api/client';

const current = ref(new Date());
const tasks = ref([]);

const dayNames = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

const monthLabel = computed(() =>
  current.value.toLocaleString(undefined, { month: 'long', year: 'numeric' })
);

const days = computed(() => {
  const start = new Date(current.value.getFullYear(), current.value.getMonth(), 1);
  const end = new Date(current.value.getFullYear(), current.value.getMonth() + 1, 0);
  const result = [];

  const startDay = (start.getDay() + 6) % 7; 
  for (let i = 0; i < startDay; i++) {
    result.push({ date: new Date(start.getTime() - (startDay - i) * 86400000), tasks: [] });
  }

  for (let d = 1; d <= end.getDate(); d++) {
    const date = new Date(current.value.getFullYear(), current.value.getMonth(), d);
    const dayTasks = tasks.value.filter(t => t.due_date && new Date(t.due_date).toDateString() === date.toDateString());
    result.push({ date, tasks: dayTasks });
  }

  return result;
});

async function loadTasks() {
  const { data } = await api.get('/reports/overdue'); 
  tasks.value = data;
}

function prevMonth() {
  current.value = new Date(current.value.getFullYear(), current.value.getMonth() - 1, 1);
}
function nextMonth() {
  current.value = new Date(current.value.getFullYear(), current.value.getMonth() + 1, 1);
}

onMounted(loadTasks);
</script>

<style scoped>
.calendar-view {
  padding: 1rem;
}
.calendar-header {
  display: flex;
  align-items: center;
  gap: 1rem;
}
.calendar-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 0.25rem;
  margin-top: 0.5rem;
}
.day-name {
  font-weight: 600;
  text-align: center;
}
.day-cell {
  min-height: 80px;
  border: 1px solid #eee;
  padding: 0.25rem;
  display: flex;
  flex-direction: column;
}
.day-number {
  font-size: 0.8rem;
  margin-bottom: 0.25rem;
}
.task-pill {
  background: #42a5f5;
  color: #fff;
  border-radius: 3px;
  padding: 0.1rem 0.3rem;
  font-size: 0.7rem;
  margin-bottom: 0.1rem;
}
</style>