<template>
  <div class="reports-dashboard">
    <canvas ref="canvas"></canvas>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import Chart from 'chart.js/auto';
import api from '../../api/client';

const canvas = ref(null);

onMounted(async () => {
  const { data } = await api.get('/reports/overview');
  const ctx = canvas.value.getContext('2d');
  new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: ['Not started', 'In progress', 'Completed', 'On hold'],
      datasets: [{
        data: [
          data.status_counts.not_started,
          data.status_counts.in_progress,
          data.status_counts.completed,
          data.status_counts.on_hold
        ],
        backgroundColor: ['#9e9e9e', '#42a5f5', '#66bb6a', '#ffa726']
      }]
    }
  });
});
</script>

<style scoped>
.reports-dashboard {
  padding: 1rem;
}
</style>