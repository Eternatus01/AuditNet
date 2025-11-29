<template>
  <div class="metrics-section dashboard-results" :class="{ hidden: !isSecurityReady }">
    <h2 class="section-title">Проверка безопасности</h2>
    <p class="section-subtitle">
      Анализ HTTP заголовков, чувствительных файлов и настроек безопасности
    </p>

    <div v-if="securityError" class="error-message mb-2">
      {{ securityError }}
    </div>

    <div v-if="securityAudit" class="security-grid">
      <SecurityCard
        title="Security Headers"
        :description="descriptions.security.headers"
        :is-expanded="isExpanded('headers')"
        @toggle-info="onToggle('headers')"
      >
        <template #icon>
          <IconLucideLock />
        </template>
        <div
          v-for="(v, k) in securityAudit.headers"
          :key="k"
          class="security-item"
        >
          <span class="security-item-name">{{
            formatHeaderName(k.toString())
          }}</span>
          <span
            :class="
              v && v !== false ? 'security-badge ok' : 'security-badge bad'
            "
            :aria-label="v && v !== false ? 'Присутствует' : 'Отсутствует'"
          >
            <IconLucideCheck v-if="v && v !== false" />
            <IconLucideX v-else />
          </span>
        </div>
      </SecurityCard>

      <SecurityCard
        title="Чувствительные файлы"
        :description="descriptions.security.sensitiveFiles"
        :is-expanded="isExpanded('files')"
        @toggle-info="onToggle('files')"
      >
        <template #icon>
          <IconLucideFileText />
        </template>
        <div
          v-for="(v, k) in securityAudit.sensitive_files"
          :key="k"
          class="security-item"
        >
          <span class="security-item-name">{{ k }}</span>
          <span 
            :class="v ? 'security-badge bad' : 'security-badge ok'"
            :aria-label="v ? 'Найден (опасно)' : 'Не найден'"
          >
            <IconLucideAlertTriangle v-if="v" />
            <IconLucideCheck v-else />
          </span>
        </div>
      </SecurityCard>

      <SecurityCard
        title="Directory Listing"
        :description="descriptions.security.directoryListing"
        :is-expanded="isExpanded('listing')"
        @toggle-info="onToggle('listing')"
      >
        <template #icon>
          <IconLucideFolder />
        </template>
        <div
          v-for="(v, k) in securityAudit.directory_listing"
          :key="k"
          class="security-item"
        >
          <span class="security-item-name">{{ k }}</span>
          <span 
            :class="v ? 'security-badge bad' : 'security-badge ok'"
            :aria-label="v ? 'Включен (опасно)' : 'Выключен'"
          >
            <IconLucideAlertTriangle v-if="v" />
            <IconLucideCheck v-else />
          </span>
        </div>
      </SecurityCard>

      <SecurityCard
        title="Дополнительно"
        :description="descriptions.security.additional"
        :is-expanded="isExpanded('additional')"
        @toggle-info="onToggle('additional')"
      >
        <template #icon>
          <IconLucideGlobe />
        </template>
        <div class="security-item">
          <span class="security-item-name">robots.txt</span>
          <span
            :class="
              securityAudit.robots_txt
                ? 'security-badge ok'
                : 'security-badge warn'
            "
            :aria-label="securityAudit.robots_txt ? 'Найден' : 'Не найден'"
          >
            <IconLucideCheck v-if="securityAudit.robots_txt" />
            <IconLucideMinus v-else />
          </span>
        </div>
        <div class="security-item">
          <span class="security-item-name">sitemap.xml</span>
          <span
            :class="
              securityAudit.sitemap_xml
                ? 'security-badge ok'
                : 'security-badge warn'
            "
            :aria-label="securityAudit.sitemap_xml ? 'Найден' : 'Не найден'"
          >
            <IconLucideCheck v-if="securityAudit.sitemap_xml" />
            <IconLucideMinus v-else />
          </span>
        </div>
        <div class="security-item">
          <span class="security-item-name">Внешние JS</span>
          <span
            :class="
              securityAudit.scripts_info?.length > 0
                ? 'security-badge warn'
                : 'security-badge ok'
            "
          >
            {{ securityAudit.scripts_info?.length || 0 }}
          </span>
        </div>
      </SecurityCard>
    </div>
  </div>
</template>

<script setup lang="ts">
import SecurityCard from "./SecurityCard.vue";
import { formatHeaderName } from "@/shared/utils/security";
import IconLucideLock from "~icons/lucide/lock";
import IconLucideFileText from "~icons/lucide/file-text";
import IconLucideFolder from "~icons/lucide/folder";
import IconLucideGlobe from "~icons/lucide/globe";
import IconLucideCheck from "~icons/lucide/check";
import IconLucideX from "~icons/lucide/x";
import IconLucideAlertTriangle from "~icons/lucide/alert-triangle";
import IconLucideMinus from "~icons/lucide/minus";

import type { SecurityAudit, AuditDescriptions } from "../types";

defineProps<{
  securityAudit: SecurityAudit | null;
  securityError: string;
  isSecurityReady: boolean;
  descriptions: AuditDescriptions;
  isExpanded: (key: string) => boolean;
}>();

const emit = defineEmits<{
  toggle: [key: string];
}>();

const onToggle = (_key: string) => {
  emit("toggle", _key);
};
</script>

