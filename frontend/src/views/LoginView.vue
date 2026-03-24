<template>
  <div class="auth-page">
    <div class="auth-card">
      <h1>Welcome Back</h1>

      <div class="form-group">
        <label>Email</label>
        <input
          v-model="form.email"
          type="email"
          placeholder="you@example.com"
        />
      </div>

      <div class="form-group">
        <label>Password</label>
        <input
          v-model="form.password"
          type="password"
          placeholder="••••••••"
        />
      </div>

      <button class="btn-primary" @click="login">Log In</button>

      <p class="switch-text">
        Don't have an account?
        <router-link to="/register">Create one</router-link>
      </p>
    </div>
  </div>
</template>

<script setup>
import { reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../store/auth'
import api from '../api/client'

const router = useRouter();
const auth = useAuthStore();

const form = reactive({
  email: '',
  password: ''
})

async function login() {
  if (!form.email.trim() || !form.password.trim()) {
    alert('Email and password are required')
    return
  }

  try {
    const { data } = await api.post('/auth/login', {
      email: form.email,
      password: form.password
    })

    auth.setUser(data.user)
    auth.setToken(data.token)

    router.push('/projects')
  } catch (err) {
    console.error(err)
    alert(err.response?.data?.error || 'Login failed')
  }
}
</script>

<style scoped>
.auth-page {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  background: #f5f5f5;
}

.auth-card {
  background: white;
  padding: 2rem;
  width: 360px;
  border-radius: 6px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

h1 {
  margin-bottom: 1.5rem;
  text-align: center;
}

.form-group {
  margin-bottom: 1rem;
}

label {
  display: block;
  font-size: 0.85rem;
  margin-bottom: 0.3rem;
}

input {
  width: 100%;
  padding: 0.55rem 0.7rem;
  border: 1px solid #ccc;
  border-radius: 4px;
}

.btn-primary {
  width: 100%;
  background: #3b82f6;
  color: white;
  padding: 0.7rem;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  margin-top: 0.5rem;
}
.btn-primary:hover {
  background: #2563eb;
}

.switch-text {
  text-align: center;
  margin-top: 1rem;
  font-size: 0.9rem;
}
</style>