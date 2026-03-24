import api from './client';

export default {
  get(taskId) {
    return api.get(`/tasks/${taskId}/activity`);
  }
};