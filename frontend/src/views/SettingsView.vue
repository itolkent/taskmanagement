<template>
  <div class="settings-page">
    <h1>User Settings</h1>

    <section class="card">
      <h2>Profile Information</h2>

      <div class="form-group">
        <label>Name</label>
        <input v-model="form.name" type="text" />
      </div>

      <div class="form-group">
        <label>Email</label>
        <input v-model="form.email" type="email" disabled />
      </div>

      <div class="form-group">
        <label>Timezone</label>
        <select v-model="form.timezone">
          <option v-for="tz in timezones" :key="tz" :value="tz">
            {{ tz }}
          </option>
        </select>
      </div>

      <button class="btn" @click="saveProfile">Save Changes</button>
    </section>

    <section class="card">
      <h2>Change Password</h2>

      <div class="form-group">
        <label>New Password</label>
        <input v-model="password.new" type="password" />
      </div>

      <div class="form-group">
        <label>Confirm Password</label>
        <input v-model="password.confirm" type="password" />
      </div>

      <button class="btn" @click="changePassword">Update Password</button>
    </section>
  </div>
</template>

<script setup>
import { reactive } from 'vue'
import { useAuthStore } from '../../store'
import api from '../api/client'

const auth = useAuthStore()

const form = reactive({
  name: auth.user?.name || '',
  email: auth.user?.email || '',
  timezone: auth.user?.timezone || 'UTC'
})

const password = reactive({
  new: '',
  confirm: ''
})

const timezones = [
  'UTC',
  'Europe/London',
  'Europe/Paris',
  'America/New_York',
  'America/Los_Angeles',
  'Asia/Tokyo',
  'Asia/Dubai'
]

async function saveProfile() {
  try {
    const { data } = await api.put('/users/me', {
      name: form.name,
      timezone: form.timezone
    })

    auth.user.name = data.name
    auth.user.timezone = data.timezone

    alert('Profile updated successfully')
  } catch (err) {
    console.error(err)
    alert('Failed to update profile')
  }
}

async function changePassword() {
  if (password.new !== password.confirm) {
    alert('Passwords do not match')
    return
  }

  try {
    await api.post('/users/change-password', {
      password: password.new
    })

    password.new = ''
    password.confirm = ''

    alert('Password updated successfully')
  } catch (err) {
    console.error(err)
    alert('Failed to update password')
  }
}
</script>

<style scoped>
.settings-page {
  max-width: 700px;
  margin: 2rem auto;
  padding: 1rem;
}

.card {
  background: #fff;
  padding: 1.5rem;
  margin-bottom: 2rem;
  border-radius: 6px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.08);
}

h1 {
  margin-bottom: 1.5rem;
}

.form-group {
  margin-bottom: 1rem;
}

label {
  display: block;
  font-size: 0.85rem;
  margin-bottom: 0.3rem;
}

input, select {
  width: 100%;
  padding: 0.45rem 0.6rem;
  border: 1px solid #ccc;
  border-radius: 4px;
}

.btn {
  background: #3b82f6;
  color: white;
  padding: 0.6rem 1.2rem;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}
.btn:hover {
  background: #2563eb;
}
</style>