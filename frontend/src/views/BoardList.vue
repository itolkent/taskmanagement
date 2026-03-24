<template>
    <div class="boards-list">
        <div v-for="board in boards" :key="board.id" class="board-card" @click="openBoard(board)">
            <h3>{{ board.name }}</h3>
        </div>

        <div class="add-board">
            <button v-if="!creating" @click="creating = true">
                + Add Board
            </button>

            <div v-else class="add-board-form">
                <input v-model="newBoardName" placeholder="Board name…" @keyup.enter="submit" @blur="cancel"
                    autofocus />
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useProjectStore } from '../../store'

const props = defineProps({
    projectId: {
        type: [String, Number],
        required: true
    }
})

const router = useRouter()
const store = useProjectStore()

const boards = computed(() => store.boards)

const creating = ref(false)
const newBoardName = ref('')

function openBoard(board) {
    router.push(`/boards/${board.id}`)
}

async function submit() {
    if (!newBoardName.value.trim()) {
        creating.value = false
        return
    }

    await store.createBoard(props.projectId, newBoardName.value.trim())
    newBoardName.value = ''
    creating.value = false
}

function cancel() {
    creating.value = false
    newBoardName.value = ''
}
</script>