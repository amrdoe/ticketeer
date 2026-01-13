<template>
    <div id="app">
        <Navbar />
        <main>
            <router-view />
        </main>
    </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue';
import Navbar from './components/Navbar.vue';
import { useAuthStore } from './stores/useAuthStore';

const authStore = useAuthStore();

onMounted(async () => {
    if (authStore.token) {
        await authStore.getUser();
    }
});
</script>

<style scoped>
main {
    min-height: calc(100vh - 64px);
}
</style>
