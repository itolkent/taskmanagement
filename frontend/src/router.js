import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../store/auth'

import LoginView from './views/LoginView.vue'
import RegisterView from './views/RegisterView.vue'

import AuthenticatedLayout from './api/components/layout/AuthenticatedLayout.vue'

import ProjectsView from './views/ProjectsView.vue'
import ProjectDetailsView from './views/ProjectDetailsView.vue'
import ProjectMembersView from './views/ProjectMembersView.vue'
import ProjectSettingsView from './views/ProjectSettingsView.vue'

import AdminUserRole from './api/components/admin/AdminUserRole.vue'

import AdminLayout from './api/components/admin/AdminLayout.vue'
import AdminDashboard from './api/components/admin/AdminDashboard.vue'
import AdminSettings from './api/components/admin/AdminSettings.vue'

import BoardView from './api/components/boards/BoardView.vue'

import AcceptInviteView from './views/AcceptInviteView.vue'
import MembersList from './api/components/members/MembersList.vue'
import ManageMembers from './api/components/members/ManageMembers.vue'
import Settings from './views/SettingsView.vue'
const routes = [
    { path: '/login', name: 'login', component: LoginView, meta: { guestOnly: true } },
    { path: '/register', name: 'register', component: RegisterView, meta: { guestOnly: true } },

    {
        path: '/invite/accept',
        name: 'invite-accept',
        component: AcceptInviteView,
        meta: { requiresAuth: true }
    },

    {
        path: '/',
        component: AuthenticatedLayout,
        meta: { requiresAuth: true },
        children: [
            { path: '', redirect: '/projects' },

            { path: 'projects', name: 'projects', component: ProjectsView },

            {
                path: 'projects/:projectId',
                component: ProjectDetailsView,
                props: true,
                children: [
                    { path: '', name: 'project-overview', component: () => import('./views/ProjectOverviewView.vue') },
                    { path: 'members', name: 'project-members', component: ProjectMembersView },
                    { path: 'settings', name: 'project-settings', component: ProjectSettingsView }
                ]
            },

            {
                path: 'projects/:projectId/boards/:boardId',
                name: 'board',
                component: BoardView,
                props: true
            },
            {
                path: 'admin',
                component: AdminLayout,
                meta: { requiresAdmin: true },
                children: [
                    {
                        path: '',
                        name: 'admin.dashboard',
                        component: AdminDashboard
                    },
                    {
                        path: 'manage-members',
                        name: 'admin.manage.members',
                        component: ManageMembers
                    },

                    {
                        path: 'settings',
                        name: 'admin.settings',
                        component: AdminSettings
                    }
                ]
            },
            {
                path: '/members',
                name: 'members',
                component: MembersList

            },
            {
                path: '/settings',
                name: 'settings',
                component: Settings
            }


        ]
    },

    { path: '/:pathMatch(.*)*', redirect: '/projects' }
]

const router = createRouter({
    history: createWebHistory(),
    routes
})

router.beforeEach(async (to, from, next) => {
    const auth = useAuthStore()
    const isLoggedIn = await auth.checkAuth()

    if (to.meta.guestOnly && isLoggedIn) {
        return next('/projects')
    }

    if (to.meta.requiresAuth && !isLoggedIn) {
        return next('/login')
    }

    if (to.meta.requiresAdmin) {
        if (!auth.user || !['admin', 'project_manager'].includes(auth.user.role)) {
            return next('/projects')
        }
    }
    if (!auth.user && auth.token) {
        await auth.checkAuth()
    }


    next()
})

export default router