<template>
  <div class="terms-page">
    <div class="terms-card">
      <h1>Пользовательское соглашение</h1>
      <p class="terms-updated">Действует с {{ effectiveDate }}</p>

      <section>
        <h2>1. Общие положения</h2>
        <p>
          Настоящее соглашение регулирует использование сервиса AuditNet (далее — «Сервис»)
          для проведения аудита производительности и безопасности веб-сайтов.
          Регистрируясь в Сервисе, вы подтверждаете, что ознакомились с условиями и принимаете их.
        </p>
      </section>

      <section>
        <h2>2. Регистрация и аккаунт</h2>
        <p>
          Вы обязуетесь указывать достоверные данные при регистрации и не передавать доступ к аккаунту
          третьим лицам. Вы несёте ответственность за все действия, совершённые под вашей учётной записью.
        </p>
      </section>

      <section>
        <h2>3. Использование Сервиса</h2>
        <p>
          Запрещается использовать Сервис для проверки ресурсов без согласия их владельцев,
          для атак, распространения вредоносного ПО и иных действий, нарушающих законодательство РФ.
        </p>
      </section>

      <section>
        <h2>4. Результаты аудита</h2>
        <p>
          Отчёты и рекомендации носят информационный характер. Мы не гарантируем полноту и применимость
          результатов для конкретного проекта; решения по доработке сайта принимаете вы самостоятельно.
        </p>
      </section>

      <section>
        <h2>5. Персональные данные</h2>
        <p>
          Мы обрабатываем email и имя для авторизации и связи с вами. Данные не передаются третьим лицам,
          за исключением случаев, предусмотренных законом.
        </p>
      </section>

      <section>
        <h2>6. Ограничение ответственности</h2>
        <p>
          Сервис предоставляется «как есть». Мы не несём ответственности за косвенные убытки,
          связанные с использованием или невозможностью использования Сервиса.
        </p>
      </section>

      <RouterLink :to="{ name: 'profile' }" class="back-link">← Вернуться к входу</RouterLink>
    </div>
  </div>
</template>

<script setup lang="ts">
const effectiveDate = "01.06.2026";
</script>

<style scoped>
.terms-page {
  max-width: 720px;
  margin: 0 auto;
  padding: 2rem 1rem 4rem;
}

.terms-card {
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 16px;
  padding: 2rem 1.5rem;
}

h1 {
  font-size: 1.5rem;
  color: #fff;
  margin: 0 0 0.5rem;
}

.terms-updated {
  font-size: 0.875rem;
  color: rgba(255, 255, 255, 0.45);
  margin: 0 0 1.5rem;
}

section {
  margin-bottom: 1.25rem;
}

h2 {
  font-size: 1rem;
  color: rgba(255, 255, 255, 0.9);
  margin: 0 0 0.5rem;
}

p {
  font-size: 0.9375rem;
  line-height: 1.6;
  color: rgba(255, 255, 255, 0.65);
  margin: 0;
}

.back-link {
  display: inline-block;
  margin-top: 1.5rem;
  color: #a78bfa;
  font-size: 0.9375rem;
  text-decoration: none;
}

.back-link:hover {
  color: #c4b5fd;
}
</style>
