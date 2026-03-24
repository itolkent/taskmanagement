<template>
  <div class="comments">
    <h3>Comments</h3>

    <div v-if="loading">Loading comments…</div>

    <div v-for="c in comments" :key="c.id" class="comment">
      <div class="meta">
        <strong>{{ c.user_name }}</strong>
        <small>{{ formatDate(c.created_at) }}</small>
      </div>
      <p>{{ c.comment }}</p>
      <button class="delete" @click="remove(c.id)">Delete</button>
    </div>

    <textarea
      v-model="newComment"
      placeholder="Write a comment…"
      rows="3"
    ></textarea>

    <button @click="add" class="add-btn">Add Comment</button>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import commentsApi from '../../comments';

const props = defineProps({
  taskId: { type: Number, required: true }
});

const comments = ref([]);
const newComment = ref('');
const loading = ref(true);

async function load() {
  loading.value = true;
  const { data } = await commentsApi.getComments(props.taskId);
  comments.value = data;
  loading.value = false;
}

async function add() {
  if (!newComment.value.trim()) return;

  const { data } = await commentsApi.addComment(props.taskId, newComment.value);
  comments.value.push(data);
  newComment.value = '';
}

async function remove(id) {
  await commentsApi.deleteComment(id);
  comments.value = comments.value.filter(c => c.id !== id);
}

function formatDate(dt) {
  return new Date(dt).toLocaleString();
}

onMounted(load);
</script>

<style scoped>
.comments {
  margin-top: 1rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.comment {
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

textarea {
  width: 100%;
  padding: 0.5rem;
}

.add-btn {
  align-self: flex-end;
}
.delete {
  background: none;
  border: none;
  color: red;
  font-size: 0.8rem;
  cursor: pointer;
}
</style>