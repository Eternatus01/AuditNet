<script setup lang="ts">
import { ref } from 'vue'

// Твои reactive переменные
const apiKey = ref('AIzaSyCIQTFcOWHRnDsG11DxTP-R0phLUsGsHgk') // ЗАМЕНИ НА СВОЙ КЛЮЧ!
const websiteUrl = ref('')
const isLoading = ref(false)
const error = ref('')
const results = ref<any>(null)

// Метод для вызова Lighthouse API
const runAudit = async () => {
  // Сбрасываем предыдущие результаты
  results.value = null
  error.value = ''
  
  // Проверяем введенный URL
  if (!websiteUrl.value) {
    error.value = 'Пожалуйста, введите URL'
    return
  }
  
  // Простая валидация URL
  try {
    new URL(websiteUrl.value)
  } catch (e) {
    error.value = 'Пожалуйста, введите корректный URL (например: https://example.com)'
    return
  }
  
  // Показываем загрузку
  isLoading.value = true
  
  try {
    // Вызываем API
    const data = await callLighthouseAPI()
    // Обрабатываем результаты
    results.value = extractScores(data)
  } catch (err: any) {
    error.value = `Ошибка: ${err.message}`
  } finally {
    isLoading.value = false
  }
}

// Метод для вызова Lighthouse API
const callLighthouseAPI = async () => {
  const apiUrl = new URL('https://www.googleapis.com/pagespeedonline/v5/runPagespeed')
  
  // Устанавливаем основные параметры
  apiUrl.searchParams.set('url', websiteUrl.value)
  apiUrl.searchParams.set('key', apiKey.value)
  
  // ЯВНО указываем все нужные категории
  apiUrl.searchParams.append('category', 'PERFORMANCE')
  apiUrl.searchParams.append('category', 'ACCESSIBILITY')
  apiUrl.searchParams.append('category', 'SEO')
  apiUrl.searchParams.append('category', 'BEST_PRACTICES')
  
  console.log('Делаем запрос к:', apiUrl.toString())
  
  const response = await fetch(apiUrl)
  
  if (!response.ok) {
    throw new Error(`API вернул ошибку: ${response.status}`)
  }
  
  const data = await response.json()
  return data
}

// Метод для извлечения нужных данных
const extractScores = (apiData: any) => {
  // Проверяем, что данные есть
  if (!apiData.lighthouseResult || !apiData.lighthouseResult.categories) {
    throw new Error('Некорректный ответ от API')
  }
  
  const categories = apiData.lighthouseResult.categories
  
  // Извлекаем 3 основные статистики
  return {
    performance: {
      score: Math.round((categories.performance?.score || 0) * 100),
      title: categories.performance?.title || 'Performance'
    },
    accessibility: {
      score: Math.round((categories.accessibility?.score || 0) * 100),
      title: categories.accessibility?.title || 'Accessibility'
    },
    seo: {
      score: Math.round((categories.seo?.score || 0) * 100),
      title: categories.seo?.title || 'SEO'
    }
  }
}

// Метод для определения цвета оценки
const getScoreColor = (score: number) => {
  if (score >= 90) return '#0cce6b' // зеленый
  if (score >= 50) return '#ffa400' // оранжевый
  return '#ff4e42' // красный
}
</script>

<template>
  <div class="container">
    <h1>🔍 Lighthouse Auditor</h1>
    
    <!-- Форма ввода -->
    <div class="input-section">
      <input 
        type="url" 
        v-model="websiteUrl"
        placeholder="Введите URL сайта (например: https://google.com)"
        :disabled="isLoading"
        @keyup.enter="runAudit"
      >
      <button 
        @click="runAudit"
        :disabled="isLoading || !websiteUrl"
      >
        {{ isLoading ? 'Проверяем...' : 'Запустить аудит' }}
      </button>
    </div>
    
    <!-- Сообщение об ошибке -->
    <div v-if="error" class="error">
      {{ error }}
    </div>
    
    <!-- Индикатор загрузки -->
    <div v-if="isLoading" class="loading">
      Загружаем данные... Это может занять несколько секунд
    </div>
    
    <!-- Результаты -->
    <div v-if="results && !isLoading" class="results">
      <h2>Результаты для: {{ websiteUrl }}</h2>
      
      <!-- Производительность -->
      <div class="metric">
        <div class="score" :style="{ color: getScoreColor(results.performance.score) }">
          Производительность: {{ results.performance.score }}/100
        </div>
        <div>{{ results.performance.title }}</div>
      </div>
      
      <!-- Доступность -->
      <div class="metric">
        <div class="score" :style="{ color: getScoreColor(results.accessibility.score) }">
          Доступность: {{ results.accessibility.score }}/100
        </div>
        <div>{{ results.accessibility.title }}</div>
      </div>
      
      <!-- SEO -->
      <div class="metric">
        <div class="score" :style="{ color: getScoreColor(results.seo.score) }">
          SEO: {{ results.seo.score }}/100
        </div>
        <div>{{ results.seo.title }}</div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.container {
  font-family: Arial, sans-serif;
  max-width: 600px;
  margin: 50px auto;
  padding: 20px;
  border-radius: 8px;
}

.input-section {
  margin-bottom: 20px;
}

input[type="url"] {
  width: 70%;
  padding: 10px;
  margin-right: 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 16px;
}

button {
  padding: 10px 20px;
  background: #007cba;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 16px;
  margin-top: 16px;
}

button:disabled {
  background: #ccc;
  cursor: not-allowed;
}

.loading {
  color: #007cba;
  margin: 10px 0;
}

.error {
  color: red;
  margin: 10px 0;
}

.results {
  margin-top: 20px;
}

.metric {
  padding: 15px;
  margin: 10px 0;
  border-radius: 4px;
  border-left: 4px solid #007cba;
}

.score {
  font-size: 18px;
  font-weight: bold;
  margin-bottom: 5px;
}
</style>