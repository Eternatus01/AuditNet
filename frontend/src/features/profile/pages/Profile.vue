<template>
  <div class="profile-container">
    <div class="profile-header">
      <h1>Профиль пользователя</h1>
      <p class="profile-subtitle">Управление личными данными</p>
    </div>

    <div class="profile-card">
      <div class="profile-avatar-section">
        <div class="profile-avatar">
          <span class="avatar-initials">{{ avatarInitials }}</span>
        </div>
        <div class="profile-info-header">
          <h2 class="profile-name">{{ name || 'Не указано' }}</h2>
          <p class="profile-email">{{ email || 'Не указано' }}</p>
        </div>
      </div>

      <div class="profile-details">
        <div class="detail-section">
          <div class="detail-item">
            <div class="detail-label">
              <svg class="detail-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
              </svg>
              <span>Имя пользователя</span>
            </div>
            <div class="detail-value">{{ name || 'Не указано' }}</div>
          </div>

          <div class="detail-item">
            <div class="detail-label">
              <svg class="detail-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                <polyline points="22,6 12,13 2,6"></polyline>
              </svg>
              <span>Email</span>
            </div>
            <div class="detail-value">{{ email || 'Не указано' }}</div>
          </div>

          <div class="detail-item">
            <div class="detail-label">
              <svg class="detail-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="8.5" cy="7" r="4"></circle>
                <line x1="20" y1="8" x2="20" y2="14"></line>
                <line x1="23" y1="11" x2="17" y2="11"></line>
              </svg>
              <span>ID пользователя</span>
            </div>
            <div class="detail-value">#{{ id || 'Не указано' }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { storeToRefs } from 'pinia';
import { useAuthStore } from "@/features/auth/stores/auth";

const authStore = useAuthStore();

// ✅ Получаем user через storeToRefs
const { user } = storeToRefs(authStore);

// ✅ Computed для доступа к вложенным свойствам (это нормально)
const name = computed<string | undefined>(() => user.value?.name);
const id = computed<number | undefined>(() => user.value?.id);
const email = computed<string | undefined>(() => user.value?.email);

const avatarInitials = computed<string>(() => {
  if (!name.value) return '?';
  const parts = name.value.trim().split(' ');
  if (parts.length >= 2) {
    return (parts[0][0] + parts[1][0]).toUpperCase();
  }
  return name.value[0].toUpperCase();
});
</script>
