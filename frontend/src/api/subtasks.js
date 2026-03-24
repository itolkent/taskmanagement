import api from './client';

export default {
    get(taskId) {
        return api.get(`/tasks/${taskId}/subtasks`);
    },

    create(taskId, title) {
        return api.post(`/tasks/${taskId}/subtasks`, { title });
    },

    update(id, payload) {
        return api.put(`/subtasks/${id}`, payload);
    },

    delete(id) {
        return api.delete(`/subtasks/${id}`);
    }
};