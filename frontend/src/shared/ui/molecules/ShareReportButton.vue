<template>
  <div class="share-report-wrapper">
    <Button
      class="share-report-button"
      variant="secondary"
      size="md"
      :loading="isLoading"
      :disabled="disabled"
      show-text-while-loading
      aria-label="Поделиться отчётом"
      @click="toggleMenu"
    >
      <template #icon-left>
        <IconLucideShare2 />
      </template>
      {{ isLoading ? "Готовим ссылку..." : "Поделиться" }}
    </Button>

    <div v-if="isOpen" class="share-menu" role="menu">
      <div class="share-menu-title">Публичная ссылка</div>
      <p class="share-menu-description">
        Любой, у кого есть ссылка, сможет посмотреть этот отчёт.
      </p>

      <div v-if="publicUrl" class="share-link-row">
        <input class="share-link-input" :value="publicUrl" readonly aria-label="Публичная ссылка отчёта" />
        <Button variant="primary" size="sm" @click="copyLink">
          Копировать
        </Button>
      </div>

      <p v-if="statusMessage" class="share-status" :class="{ error: hasError }">
        {{ statusMessage }}
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onBeforeUnmount, ref } from "vue";
import { Button } from "@/shared/ui/atoms";
import IconLucideShare2 from "~icons/lucide/share-2";

const props = withDefaults(
  defineProps<{
    disabled?: boolean;
    getShareUrl: () => Promise<string>;
  }>(),
  {
    disabled: false,
  }
);

const isOpen = ref(false);
const isLoading = ref(false);
const publicUrl = ref("");
const statusMessage = ref("");
const hasError = ref(false);

const normalizePublicUrl = (url: string): string =>
  decodeURI(url).replace("xn--80aidlz3acc.xn--p1ai", "аудитнет.рф");

const closeMenu = () => {
  isOpen.value = false;
};

const loadShareUrl = async () => {
  if (publicUrl.value || isLoading.value) return;

  isLoading.value = true;
  statusMessage.value = "";
  hasError.value = false;

  try {
    publicUrl.value = normalizePublicUrl(await props.getShareUrl());
  } catch {
    hasError.value = true;
    statusMessage.value = "Не удалось создать публичную ссылку.";
  } finally {
    isLoading.value = false;
  }
};

const toggleMenu = async () => {
  if (props.disabled) return;

  isOpen.value = !isOpen.value;

  if (isOpen.value) {
    await loadShareUrl();
  }
};

const copyFallback = async (text: string) => {
  const textarea = document.createElement("textarea");
  textarea.value = text;
  textarea.setAttribute("readonly", "");
  textarea.style.position = "fixed";
  textarea.style.opacity = "0";
  document.body.appendChild(textarea);
  textarea.select();
  document.execCommand("copy");
  document.body.removeChild(textarea);
};

const copyLink = async () => {
  if (!publicUrl.value) return;

  try {
    const link = normalizePublicUrl(publicUrl.value);

    if (navigator.clipboard?.writeText) {
      await navigator.clipboard.writeText(link);
    } else {
      await copyFallback(link);
    }

    hasError.value = false;
    statusMessage.value = "Ссылка скопирована.";
  } catch {
    hasError.value = true;
    statusMessage.value = "Не удалось скопировать ссылку.";
  }
};

const onKeydown = (event: KeyboardEvent) => {
  if (event.key === "Escape") {
    closeMenu();
  }
};

document.addEventListener("keydown", onKeydown);

onBeforeUnmount(() => {
  document.removeEventListener("keydown", onKeydown);
});
</script>

<style scoped>
.share-report-wrapper {
  position: relative;
  display: inline-flex;
  justify-content: flex-end;
}

.share-report-wrapper :deep(.share-report-button) {
  min-height: 46px;
  padding: 0.6875rem 1.625rem;
  background: linear-gradient(135deg, #0891b2 0%, #0e7490 100%);
  color: white;
  border: none;
  box-shadow: 0 4px 14px rgba(8, 145, 178, 0.35);
}

.share-report-wrapper :deep(.share-report-button:hover:not(:disabled)) {
  background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(8, 145, 178, 0.42);
}

.share-menu {
  position: absolute;
  right: 0;
  top: calc(100% + 0.75rem);
  z-index: 20;
  width: min(420px, calc(100vw - 2rem));
  padding: 1rem;
  background: var(--bg-secondary, #18181b);
  border: 1px solid var(--border-color, #27272a);
  border-radius: var(--radius-lg, 16px);
  box-shadow: var(--shadow-xl, 0 24px 60px rgba(0, 0, 0, 0.35));
}

.share-menu-title {
  margin-bottom: 0.35rem;
  color: var(--text-primary, rgba(255, 255, 255, 0.92));
  font-size: 0.95rem;
  font-weight: 700;
}

.share-menu-description {
  margin: 0 0 0.85rem;
  color: var(--text-secondary, rgba(255, 255, 255, 0.65));
  font-size: 0.85rem;
  line-height: 1.45;
}

.share-link-row {
  display: flex;
  gap: 0.5rem;
}

.share-link-input {
  min-width: 0;
  flex: 1;
  padding: 0.55rem 0.75rem;
  color: var(--text-primary, rgba(255, 255, 255, 0.92));
  background: var(--bg-tertiary, rgba(39, 39, 42, 0.8));
  border: 1px solid var(--border-color, #27272a);
  border-radius: var(--radius-md, 10px);
  font: inherit;
}

.share-status {
  margin: 0.75rem 0 0;
  color: #10b981;
  font-size: 0.82rem;
}

.share-status.error {
  color: #ef4444;
}

@media (max-width: 768px) {
  .share-report-wrapper {
    width: 100%;
  }

  .share-menu {
    left: 0;
    right: auto;
    width: 100%;
  }

  .share-link-row {
    flex-direction: column;
  }
}
</style>
