import api from './client';

export default {
    get(taskId) {
        return api.get(`/tasks/${taskId}/attachments`);
    },

    upload(taskId, file) {
        const form = new FormData();
        form.append('file', file);

        return api.post(`/tasks/${taskId}/attachments`, form, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
    },

    delete(id) {
        return api.delete(`/attachments/${id}`);
    }
};