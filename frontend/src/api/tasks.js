import api from './client';

export default {
  reorder(id, payload) {
    return api.put(`/tasks/${id}/reorder`, payload);
  }
};  