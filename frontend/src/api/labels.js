import api from './client';

export default {
    getAll() {
        return api.get('/labels');
    },

    create(name, color) {
        return api.post('/labels', { name, color });
    },

    getTaskLabels(taskId) {
        return api.get(`/tasks/${taskId}/labels`);
    },

    attach(taskId, labelId) {
        return api.post(`/tasks/${taskId}/labels`, { label_id: labelId });
    },

    detach(taskId, labelId) {
        return api.delete(`/tasks/${taskId}/labels/${labelId}`);
    }
};