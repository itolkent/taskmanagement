import { defineStore } from 'pinia'
import api from '../src/api/client'

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        token: localStorage.getItem('tm_token') || null
    }),

    actions: {
        async login(email, password) {
            const { data } = await api.post('/auth/login', { email, password })

            this.token = data.token
            this.user = data.user

            localStorage.setItem('tm_token', data.token)
        },

        logout() {
            this.user = null
            this.token = null
            localStorage.removeItem('tm_token')
        },

        async checkAuth() {
            if (!this.token) {
                this.user = null
                return false
            }

            try {
                const { data } = await api.get('/auth/me')

                this.user = data

                return true
            } catch (e) {
                this.user = null
                this.token = null

                localStorage.removeItem('tm_token')

                return false
            }
        }
    }
})

export const useProjectStore = defineStore('projects', {
    state: () => ({
        projects: [],
        currentProject: null,

        boards: [],
        currentBoard: null,

        boardLists: [],

        members: [],
        invites: [],
        users: [],
        myRole: null,
        adminUsers: [],
        tasks: []
    }),

    actions: {
        async fetchProjects() {
            const { data } = await api.get('/projects')
            this.projects = data

            this.syncTaskAssignees()
        },

        async fetchAdminUsers() {
            const { data } = await api.get('/admin/users')
            this.adminUsers = data
        },

        async updateUserRole(userId, role) {
            const { data } = await api.put(`/admin/users/${userId}/role`, { role })

            const user = this.adminUsers.find(u => u.id === userId)
            if (user) user.role = data.role

            const member = this.members.find(m => m.id === userId)
            if (member) member.role = data.role
        },

        async fetchProject(projectId) {
            const { data } = await api.get(`/projects/${projectId}`)
            this.currentProject = data
            this.myRole = data.myRole
            this.syncTaskAssignees()
            console.log("FETCH MEMBERS RESPONSE:", data)
            return data
        },

        async updateProject(projectId, payload) {
            const { data } = await api.put(`/projects/${projectId}`, payload)
            this.currentProject = data
            return data
        },

        async fetchMembers(projectId) {
            const { data } = await api.get(`/projects/${projectId}/members`)
            this.members = data.members.map(m => ({
                user_id: m.user_id,
                name: m.name,
                role: m.role
            }))
            this.myRole = data.myRole
            this.invites = data.pending_invites
            this.syncTaskAssignees()

        },

        async updateMemberRole(projectId, userId, role) {
            await api.put(`/projects/${projectId}/members/${userId}`, { role })
            await this.fetchMembers(projectId)
        },

        async removeMember(projectId, userId) {
            await api.delete(`/projects/${projectId}/members/${userId}`)
            await this.fetchMembers(projectId)
        },

        async sendInvite(projectId, email, role = 'member') {
            const { data } = await api.post(`/projects/${projectId}/invites`, {
                email,
                role
            })
            this.invites.push(data)
        },
        applyLocalAssigneeUpdate(updatedTask) {
            const index = this.tasks.findIndex(t => t.id === updatedTask.id)
            if (index !== -1) {
                const task = this.tasks[index]

                task.assignee_id = updatedTask.assignee_id

                task.assigned_members = updatedTask.assignee
                    ? [{
                        id: updatedTask.assignee.user_id,
                        name: updatedTask.assignee.name,
                        avatar_url: updatedTask.assignee.avatar_url
                    }]
                    : []
            }

        },
        syncTaskAssignees() {
            if (!this.tasks || !this.members) return

            this.tasks.forEach(task => {
                if (task.assignee_id) {
                    const user = this.members.find(m => m.user_id === task.assignee_id)
                    task.assigned_members = user
                        ? [{
                            id: user.user_id,
                            name: user.name,
                            avatar_url: user.avatar_url
                        }]
                        : []
                } else {
                    task.assigned_members = []
                }
            })
        },
        async fetchBoards(projectId) {
            const { data } = await api.get(`/projects/${projectId}/boards`)
            this.boards = data
            this.syncTaskAssignees()
            return data

        },

        async createBoard(projectId, name) {
            const { data } = await api.post(`/projects/${projectId}/boards`, { name })
            this.boards.push(data)
            return data
        },

        async fetchBoard(boardId) {
            const { data } = await api.get(`/boards/${boardId}/lists`)
            this.boardLists = data
            this.currentBoard = { id: boardId }

            this.tasks = []
            this.boardLists.forEach(list => {
                list.task_lists.forEach(task => {
                    this.tasks.push(task)
                })
            })

            this.syncTaskAssignees()
        },

        async createList(boardId, name) {
            const { data } = await api.post(`/boards/${boardId}/lists`, { name })

            this.boardLists.push({
                ...data,
                task_lists: []
            })
        },

        async createTask(listId, title) {
            const { data } = await api.post(`/lists/${listId}/tasks`, { title })

            const list = this.boardLists.find(l => l.id === listId)
            if (list) list.task_lists.push(data)
        },
        async updateTask(taskId, payload) {
            const { data } = await api.put(`/tasks/${taskId}`, payload)
            const updated = data

            this.boardLists.forEach(list => {
                const idx = list.task_lists.findIndex(t => t.id === updated.id)
                if (idx !== -1) list.task_lists.splice(idx, 1)
            })

            const targetList = this.boardLists.find(l => l.id === updated.task_list_id)
            if (targetList) targetList.task_lists.push(updated)

            const idx = this.tasks.findIndex(t => t.id === updated.id)
            if (idx !== -1) {
                this.tasks[idx] = updated
            } else {
                this.tasks.push(updated)
            }

            this.syncTaskAssignees()

            return updated
        },


        async deleteTask(taskId) {
            await api.delete(`/tasks/${taskId}`)

            this.boardLists.forEach(list => {
                const idx = list.task_lists.findIndex(t => t.id === taskId)
                if (idx !== -1) list.task_lists.splice(idx, 1)
            })
        },
        async fetchProjectMembers(projectId) {
            const res = await fetch(`/api/v1/projects/${projectId}/members`, {
                headers: { Authorization: `Bearer ${localStorage.getItem('tm_token')}` }
            })
            const data = await res.json()
            this.members = data.members.map(m => ({
                user_id: m.user_id,
                name: m.name,
                role: m.role
            }))

            this.myRole = data.myRole
        },

        async fetchAllUsers() {
            const res = await fetch(`/api/v1/users`, {
                headers: { Authorization: `Bearer ${localStorage.getItem('tm_token')}` }
            })
            const data = await res.json()

            this.users = data
        },

        async addProjectMember(projectId, payload) {
            const { data } = await api.post(`/projects/${projectId}/members`, payload)
            this.members.push(data.member)
            await this.fetchProjectMembers(projectId)
        },

        async removeProjectMember(projectId, userId) {
            await api.delete(`/projects/${projectId}/members/${userId}`)
            this.members = this.members.filter(m => m.user_id !== userId)
        }

    }
})
