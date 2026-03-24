<template>
  <div class="auth-page">
    <div class="auth-card">
      <h1>Create an Account</h1>

      <div class="form-group">
        <label>Name</label>
        <input v-model="form.name" type="text" placeholder="Your name" />
      </div>

      <div class="form-group">
        <label>Email</label>
        <input v-model="form.email" type="email" placeholder="you@example.com" />
      </div>

      <div class="form-group">
        <label>Password</label>
        <input v-model="form.password" type="password" placeholder="••••••••" />
      </div>

      <div class="form-group">
        <label>Confirm Password</label>
        <input v-model="form.confirm" type="password" placeholder="••••••••" />
      </div>

      <button class="btn-primary" @click="register">Create Account</button>

      <p class="switch-text">
        Already have an account?
        <router-link to="/login">Log in</router-link>
      </p>
    </div>
  </div>
</template>

<script setup>
import { reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../store'
import api from '../api/client'

const router = useRouter()
const auth = useAuthStore()

const form = reactive({
  name: '',
  email: '',
  password: '',
  confirm: ''
})
async function register() {
  if (!form.name.trim() || !form.email.trim() || !form.password.trim()) {
    alert('All fields are required');
    return;
  }

  if (form.password !== form.confirm) {
    alert('Passwords do not match');
    return;
  }

  try {
    const { data } = await api.post('/auth/register', {
      name: form.name,
      email: form.email,
      password: form.password
    });

    auth.setUser(data.user);
    auth.setToken(data.token);

    router.push('/projects');
  } catch (err) {
    console.error(err);
    alert(err.response?.data?.error || 'Registration failed');
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