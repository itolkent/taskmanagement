<template>
    <div class="admin-layout">
        <main class="admin-content">
            <router-view />
        </main>
    </div>
</template>


<script setup>
import { useRoute } from 'vue-router'
import { useProjectStore } from '../../../../store'

const route = useRoute()
const auth = useProjectStore()

function isActive(path) {
    return route.path.startsWith(path)
}

function logout() {
    auth.logout()
}
</script>

<style scoped>
.admin-layout {
    display: flex;
    height: 100vh;
    width: 100%;
    background: #f5f5f5;
}
.sidebar {
    width: 240px;
    background: #1e1e1e;
    color: white;
    padding: 1.5rem 1rem;
    display: flex;
    flex-direction: column;
}

.title {
    font-size: 1.4rem;
    margin-bottom: 2rem;
    font-weight: 600;
}

.nav {
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
}

.nav-item {
    padding: 0.7rem 1rem;
    border-radius: 4px;
    color: #ccc;
    text-decoration: none;
    transition: 0.2s;
}

.nav-item:hover {
    background: #333;
    color: #fff;
}

.nav-item.active {
    background: #4f46e5;
    color: #fff;
}

.logout {
    background: transparent;
    border: none;
    text-align: left;
    cursor: pointer;
    color: #ccc;
}

.logout:hover {
    background: #333;
    color: #fff;
}

.content {
    flex: 1;
    padding: 2rem;
    overflow-y: auto;
}
</style>