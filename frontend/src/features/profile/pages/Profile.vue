<template>
  <!-- Гость -->
  <div v-if="!isAuthenticated" class="auth-wrapper">
    <div class="auth-card">
      <div v-if="!showRegister">
        <Form :validation-schema="loginSchema" class="profile-form" @submit="onLogin">
          <h2>Вход в систему</h2>

          <div v-if="sessionExpiredMessage" class="warning-message">
            {{ sessionExpiredMessage }}
          </div>

          <div class="form-fields">
            <InputField name="email" type="email" placeholder="E-mail" autocomplete="email" label="Email" />
            <InputField name="password" type="password" placeholder="Пароль" autocomplete="current-password" label="Пароль" />
          </div>

          <Button type="submit" variant="primary" size="lg" :loading="isLoading" full-width>
            Войти
          </Button>

          <p v-if="authError" class="error-message">{{ authError }}</p>
        </Form>

        <p class="switch-link">
          Нет аккаунта?
          <button class="link-btn" @click="switchMode(true)">Зарегистрироваться</button>
        </p>
      </div>

      <div v-else>
        <Form :validation-schema="registerSchema" class="profile-form" @submit="onRegister">
          <h2>Регистрация</h2>

          <div class="form-fields">
            <InputField name="name" type="text" placeholder="Имя" autocomplete="name" label="Имя" />
            <InputField name="email" type="email" placeholder="E-mail" autocomplete="email" label="Email" />
            <InputField name="password" type="password" placeholder="Пароль" autocomplete="new-password" label="Пароль" />
          </div>

          <Button type="submit" variant="primary" size="lg" :loading="isLoading" full-width>
            Создать аккаунт
          </Button>

          <p v-if="authError" class="error-message">{{ authError }}</p>
        </Form>

        <p class="switch-link">
          Уже есть аккаунт?
          <button class="link-btn" @click="switchMode(false)">Войти</button>
        </p>
      </div>
    </div>
  </div>

  <!-- Авторизованный пользователь -->
  <div v-else class="profile-container">

    <!-- Уведомления -->
    <Transition name="slide-fade">
      <div v-if="successMessage" class="alert alert-success">
        <IconLucideCheckCircle class="alert-icon" />
        {{ successMessage }}
      </div>
    </Transition>
    <Transition name="slide-fade">
      <div v-if="updateError" class="alert alert-error">
        <IconLucideAlertCircle class="alert-icon" />
        {{ updateError }}
      </div>
    </Transition>

    <!-- Карточка профиля -->
    <div class="profile-card">
      <div class="card-top">
        <!-- Аватар + имя/email -->
        <div class="avatar-row">
          <div class="profile-avatar">
            <span class="avatar-initials">{{ avatarInitials }}</span>
          </div>
          <div class="profile-meta">
            <template v-if="!isEditing">
              <div class="profile-name">{{ user?.name }}</div>
              <div class="profile-email">{{ user?.email }}</div>
            </template>
            <template v-else>
              <div class="edit-fields">
                <input
                  v-model="formName"
                  type="text"
                  class="field-input"
                  placeholder="Имя"
                  minlength="2"
                  maxlength="255"
                  @keydown.enter.prevent="saveInfo"
                  @keydown.escape="cancelEdit"
                />
                <input
                  v-model="formEmail"
                  type="email"
                  class="field-input"
                  placeholder="Email"
                  @keydown.enter.prevent="saveInfo"
                  @keydown.escape="cancelEdit"
                />
              </div>
            </template>
          </div>
        </div>

        <!-- Кнопка настроек -->
        <div class="card-actions">
          <template v-if="!isEditing">
            <button class="icon-btn" title="Редактировать профиль" @click="startEdit">
              <IconLucideSettings />
            </button>
          </template>
          <template v-else>
            <button
              class="icon-btn icon-btn--confirm"
              title="Сохранить"
              :disabled="isSavingInfo"
              @click="saveInfo"
            >
              <IconLucideCheck v-if="!isSavingInfo" />
              <IconLucideLoader2 v-else class="spinner" />
            </button>
            <button class="icon-btn icon-btn--cancel" title="Отменить" @click="cancelEdit">
              <IconLucideX />
            </button>
          </template>
        </div>
      </div>

      <!-- ID -->
      <div class="profile-id">ID #{{ user?.id }}</div>
    </div>

    <!-- Смена пароля -->
    <div class="profile-card">
      <div class="section-header">
        <IconLucideLock class="section-icon" />
        <h3>Смена пароля</h3>
      </div>

      <form class="edit-form" @submit.prevent="savePassword">
        <div class="form-row">
          <label class="field-label">Текущий пароль</label>
          <input
            v-model="currentPassword"
            type="password"
            class="field-input"
            placeholder="Введите текущий пароль"
            autocomplete="current-password"
          />
        </div>
        <div class="form-row">
          <label class="field-label">Новый пароль</label>
          <input
            v-model="newPassword"
            type="password"
            class="field-input"
            placeholder="Минимум 8 символов"
            autocomplete="new-password"
            minlength="8"
          />
        </div>
        <div class="form-row">
          <label class="field-label">Повторите пароль</label>
          <input
            v-model="confirmPassword"
            type="password"
            class="field-input"
            placeholder="Повторите новый пароль"
            autocomplete="new-password"
          />
          <p v-if="passwordMismatch" class="field-error">Пароли не совпадают</p>
        </div>
        <div class="form-actions">
          <Button
            type="submit"
            variant="primary"
            size="md"
            :loading="isSavingPassword"
            :disabled="!canSavePassword"
          >
            Сменить пароль
          </Button>
        </div>
      </form>
    </div>

  </div>
</template>

<script setup lang="ts">
import { computed, ref, onMounted, watch } from 'vue';
import { storeToRefs } from 'pinia';
import { useRouter } from 'vue-router';
import { Form } from 'vee-validate';
import { useAuthStore } from "@/features/auth/stores/auth";
import type { SignInCredentials, SignUpCredentials } from "@/features/auth/types";
import InputField from "@/shared/ui/molecules/InputField.vue";
import { Button } from "@/shared/ui/atoms";
import { loginSchema, registerSchema } from "@/shared/validation/schemas";
import IconLucideSettings from "~icons/lucide/settings";
import IconLucideCheck from "~icons/lucide/check";
import IconLucideX from "~icons/lucide/x";
import IconLucideLoader2 from "~icons/lucide/loader-2";
import IconLucideLock from "~icons/lucide/lock";
import IconLucideCheckCircle from "~icons/lucide/check-circle";
import IconLucideAlertCircle from "~icons/lucide/alert-circle";

const router = useRouter();
const authStore = useAuthStore();
const { isAuthenticated, error: authError, user } = storeToRefs(authStore);

// --- Auth (гость) ---
const isLoading = ref(false);
const showRegister = ref(false);
const sessionExpiredMessage = ref<string | null>(null);

onMounted(() => {
  const message = localStorage.getItem('auth_expired_message');
  if (message) {
    sessionExpiredMessage.value = message;
    localStorage.removeItem('auth_expired_message');
    setTimeout(() => { sessionExpiredMessage.value = null; }, 5000);
  }
});

const switchMode = (toRegister: boolean) => {
  showRegister.value = toRegister;
  authStore.clearError();
};

const onLogin = async (values: unknown) => {
  isLoading.value = true;
  try {
    const response = await authStore.signIn(values as SignInCredentials);
    if (response) {
      const redirect = router.currentRoute.value.query.redirect as string;
      await router.push(redirect || { name: 'dashboard' });
    }
  } finally {
    isLoading.value = false;
  }
};

const onRegister = async (values: unknown) => {
  isLoading.value = true;
  try {
    const response = await authStore.signUp(values as SignUpCredentials);
    if (response) {
      await router.push({ name: 'dashboard' });
    }
  } finally {
    isLoading.value = false;
  }
};

// --- Профиль ---
const successMessage = ref<string | null>(null);
const updateError = ref<string | null>(null);

const isEditing = ref(false);
const formName = ref('');
const formEmail = ref('');

watch(user, (u) => {
  if (u) {
    formName.value = u.name;
    formEmail.value = u.email;
  }
}, { immediate: true });

const startEdit = () => {
  formName.value = user.value?.name ?? '';
  formEmail.value = user.value?.email ?? '';
  isEditing.value = true;
};

const cancelEdit = () => {
  isEditing.value = false;
  formName.value = user.value?.name ?? '';
  formEmail.value = user.value?.email ?? '';
};

const isSavingInfo = ref(false);

const saveInfo = async () => {
  const nameChanged = formName.value !== user.value?.name;
  const emailChanged = formEmail.value !== user.value?.email;
  if (!nameChanged && !emailChanged) {
    isEditing.value = false;
    return;
  }
  isSavingInfo.value = true;
  updateError.value = null;
  successMessage.value = null;
  try {
    const ok = await authStore.updateProfile({
      name: formName.value,
      email: formEmail.value,
    });
    if (ok) {
      isEditing.value = false;
      showSuccess('Данные успешно обновлены');
    } else {
      updateError.value = authStore.error ?? 'Ошибка сохранения';
    }
  } finally {
    isSavingInfo.value = false;
  }
};

// --- Пароль ---
const currentPassword = ref('');
const newPassword = ref('');
const confirmPassword = ref('');
const isSavingPassword = ref(false);

const passwordMismatch = computed(() =>
  confirmPassword.value.length > 0 && newPassword.value !== confirmPassword.value
);

const canSavePassword = computed(() =>
  currentPassword.value.length > 0 &&
  newPassword.value.length >= 8 &&
  newPassword.value === confirmPassword.value
);

const savePassword = async () => {
  if (!canSavePassword.value) return;
  isSavingPassword.value = true;
  updateError.value = null;
  successMessage.value = null;
  try {
    const ok = await authStore.updateProfile({
      current_password: currentPassword.value,
      password: newPassword.value,
      password_confirmation: confirmPassword.value,
    });
    if (ok) {
      currentPassword.value = '';
      newPassword.value = '';
      confirmPassword.value = '';
      showSuccess('Пароль успешно изменён');
    } else {
      updateError.value = authStore.error ?? 'Ошибка смены пароля';
    }
  } finally {
    isSavingPassword.value = false;
  }
};

const showSuccess = (msg: string) => {
  successMessage.value = msg;
  setTimeout(() => { successMessage.value = null; }, 4000);
};

// --- Аватар ---
const avatarInitials = computed(() => {
  const n = user.value?.name;
  if (!n) return '?';
  const parts = n.trim().split(' ');
  return parts.length >= 2
    ? (parts[0][0] + parts[1][0]).toUpperCase()
    : n[0].toUpperCase();
});
</script>

<style scoped>
/* ---- Гость ---- */
.auth-wrapper {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 70vh;
  padding: 2rem;
}

.auth-card {
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 20px;
  padding: 2.5rem;
  width: 100%;
  max-width: 420px;
}

.profile-form {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.form-fields {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.switch-link {
  margin-top: 1.25rem;
  text-align: center;
  font-size: 0.875rem;
  color: rgba(255, 255, 255, 0.5);
}

.link-btn {
  background: none;
  border: none;
  color: #7c3aed;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  padding: 0;
  text-decoration: underline;
  text-underline-offset: 2px;
}

.link-btn:hover { color: #a78bfa; }

.warning-message {
  padding: 1rem;
  background-color: rgba(255, 193, 7, 0.1);
  border: 1px solid rgba(255, 193, 7, 0.3);
  border-radius: 8px;
  color: #ffc107;
  font-size: 0.9rem;
  text-align: center;
}

.error-message {
  color: #f87171;
  font-size: 0.875rem;
  text-align: center;
  margin: 0;
}

/* ---- Профиль ---- */
.profile-container {
  max-width: 640px;
  margin: 0 auto;
  padding: 2rem 1rem 4rem;
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.profile-card {
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 16px;
  padding: 1.5rem;
}

/* Верх карточки: аватар + кнопки */
.card-top {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 1rem;
}

.avatar-row {
  display: flex;
  align-items: center;
  gap: 1rem;
  flex: 1;
  min-width: 0;
}

.profile-avatar {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  background: linear-gradient(135deg, #7c3aed, #a78bfa);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.avatar-initials {
  font-size: 1.375rem;
  font-weight: 700;
  color: #fff;
}

.profile-meta {
  min-width: 0;
  flex: 1;
}

.profile-name {
  font-size: 1.125rem;
  font-weight: 600;
  color: #fff;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.profile-email {
  font-size: 0.875rem;
  color: rgba(255, 255, 255, 0.5);
  margin-top: 0.2rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.profile-id {
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid rgba(255, 255, 255, 0.06);
  font-size: 0.8125rem;
  color: rgba(255, 255, 255, 0.3);
}

/* Поля редактирования внутри карточки */
.edit-fields {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  width: 100%;
}

/* Кнопки действий справа */
.card-actions {
  display: flex;
  gap: 0.375rem;
  flex-shrink: 0;
}

.icon-btn {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  background: rgba(255, 255, 255, 0.05);
  color: rgba(255, 255, 255, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: background 0.15s ease, color 0.15s ease, border-color 0.15s ease;
  padding: 0;
}

.icon-btn:hover {
  background: rgba(255, 255, 255, 0.1);
  color: rgba(255, 255, 255, 0.9);
  border-color: rgba(255, 255, 255, 0.2);
}

.icon-btn--confirm {
  border-color: rgba(16, 185, 129, 0.3);
  color: #10b981;
  background: rgba(16, 185, 129, 0.08);
}

.icon-btn--confirm:hover:not(:disabled) {
  background: rgba(16, 185, 129, 0.18);
  border-color: rgba(16, 185, 129, 0.5);
  color: #10b981;
}

.icon-btn--confirm:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.icon-btn--cancel {
  border-color: rgba(248, 113, 113, 0.3);
  color: #f87171;
  background: rgba(248, 113, 113, 0.08);
}

.icon-btn--cancel:hover {
  background: rgba(248, 113, 113, 0.18);
  border-color: rgba(248, 113, 113, 0.5);
  color: #f87171;
}

.icon-btn svg {
  width: 16px;
  height: 16px;
}

.spinner {
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Секция пароля */
.section-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1.25rem;
}

.section-header h3 {
  font-size: 0.9375rem;
  font-weight: 600;
  color: rgba(255, 255, 255, 0.9);
  margin: 0;
}

.section-icon {
  color: #7c3aed;
  width: 18px;
  height: 18px;
  flex-shrink: 0;
}

.edit-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.form-row {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
}

.field-label {
  font-size: 0.8125rem;
  font-weight: 500;
  color: rgba(255, 255, 255, 0.55);
}

.field-input {
  width: 100%;
  box-sizing: border-box;
  padding: 0.625rem 0.875rem;
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 9px;
  color: #fff;
  font-size: 0.9375rem;
  font-family: inherit;
  outline: none;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.field-input:focus {
  border-color: #7c3aed;
  box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.2);
}

.field-input::placeholder {
  color: rgba(255, 255, 255, 0.25);
}

.field-error {
  font-size: 0.8125rem;
  color: #f87171;
  margin: 0;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  padding-top: 0.25rem;
}

/* Уведомления */
.alert {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.875rem 1.125rem;
  border-radius: 12px;
  font-size: 0.9375rem;
  font-weight: 500;
}

.alert-success {
  background: rgba(16, 185, 129, 0.12);
  border: 1px solid rgba(16, 185, 129, 0.3);
  color: #10b981;
}

.alert-error {
  background: rgba(248, 113, 113, 0.1);
  border: 1px solid rgba(248, 113, 113, 0.3);
  color: #f87171;
}

.alert-icon {
  width: 18px;
  height: 18px;
  flex-shrink: 0;
}

.slide-fade-enter-active,
.slide-fade-leave-active {
  transition: all 0.25s ease;
}

.slide-fade-enter-from,
.slide-fade-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}

/* Адаптив */
@media (max-width: 600px) {
  .auth-card {
    padding: 1.75rem 1.25rem;
  }

  .profile-container {
    padding: 1.25rem 0 3rem;
  }

  .form-actions {
    justify-content: stretch;
  }
}
</style>
