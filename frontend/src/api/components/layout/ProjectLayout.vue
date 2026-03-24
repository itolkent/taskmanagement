<template>
    <div class="layout">

        <Sidebar :projectId="projectId" />
        <main class="content">
        </main>

    </div>
</template>

<script setup>
import Sidebar from './AppSidebar.vue'
import { useRoute } from 'vue-router'

const route = useRoute()

const projectId = route.params.projectId
    ? Number(route.params.projectId)
    : null
</script>


<style scoped>
.layout {
    display: flex;
    height: 100vh;
    width: 100%;
    overflow: hidden;
}

.content {
    flex: 1;
    overflow-y: auto;
    padding: 2rem;
    background: #f2f2f2;
}
</style>