import api from './client';

export default {
    getComments(taskId) {
        return api.get(`/tasks/${taskId}/comments`);
    },

    addComment(taskId, comment) {
        return api.post(`/tasks/${taskId}/comments`, { comment });
    },

    deleteComment(commentId) {
        return api.delete(`/comments/${commentId}`);
    }
};