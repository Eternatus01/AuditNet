export interface RecommendationTranslation {
  title: string;
  solution: string;
}

export const recommendationsMap: Record<string, RecommendationTranslation> = {
  'bootup-time': {
    title: 'Сократите время выполнения JavaScript',
    solution: 'Уменьшите размер JS файлов, используйте code splitting и lazy loading'
  },
  'mainthread-work-breakdown': {
    title: 'Минимизируйте работу основного потока',
    solution: 'Оптимизируйте JavaScript, используйте Web Workers для тяжёлых вычислений'
  },
  'redirects': {
    title: 'Избегайте множественных редиректов',
    solution: 'Используйте прямые ссылки без цепочек редиректов'
  },
  'unused-javascript': {
    title: 'Удалите неиспользуемый JavaScript',
    solution: 'Используйте tree shaking и удалите неиспользуемые библиотеки'
  },
  'unused-css-rules': {
    title: 'Удалите неиспользуемые CSS правила',
    solution: 'Используйте PurgeCSS или удалите лишние стили вручную'
  },
  'render-blocking-resources': {
    title: 'Устраните ресурсы, блокирующие рендеринг',
    solution: 'Используйте async/defer для скриптов, инлайн критический CSS'
  },
  'uses-text-compression': {
    title: 'Включите сжатие текста',
    solution: 'Настройте gzip или brotli сжатие на сервере'
  },
  'uses-long-cache-ttl': {
    title: 'Используйте эффективную политику кеширования',
    solution: 'Установите Cache-Control заголовки с длительным TTL'
  },
  'server-response-time': {
    title: 'Сократите время ответа сервера',
    solution: 'Оптимизируйте backend, используйте CDN, кеширование'
  },
  'modern-image-formats': {
    title: 'Используйте современные форматы изображений',
    solution: 'Конвертируйте изображения в WebP или AVIF'
  },
  'uses-optimized-images': {
    title: 'Оптимизируйте изображения',
    solution: 'Сожмите изображения без потери качества'
  },
  'uses-responsive-images': {
    title: 'Используйте адаптивные изображения',
    solution: 'Используйте srcset и sizes для разных размеров экранов'
  },
  'offscreen-images': {
    title: 'Отложите загрузку невидимых изображений',
    solution: 'Используйте lazy loading: loading="lazy"'
  },
  'unminified-css': {
    title: 'Минифицируйте CSS',
    solution: 'Используйте CSS минификатор в процессе сборки'
  },
  'unminified-javascript': {
    title: 'Минифицируйте JavaScript',
    solution: 'Используйте Terser или другой JS минификатор'
  },
  'efficient-animated-content': {
    title: 'Используйте видео вместо GIF',
    solution: 'Конвертируйте GIF анимации в MP4 или WebM'
  },
  'duplicated-javascript': {
    title: 'Удалите дублирующийся JavaScript',
    solution: 'Вынесите общий код в отдельные модули'
  },
  'legacy-javascript': {
    title: 'Избегайте устаревшего JavaScript',
    solution: 'Используйте современный ES6+ синтаксис'
  },
  'dom-size': {
    title: 'Сократите размер DOM',
    solution: 'Уменьшите количество DOM элементов (оптимально < 1500)'
  },
  'uses-http2': {
    title: 'Используйте HTTP/2',
    solution: 'Настройте HTTP/2 на сервере для параллельной загрузки'
  },
  'uses-passive-event-listeners': {
    title: 'Используйте пассивные обработчики событий',
    solution: 'Добавьте {passive: true} к scroll/touch событиям'
  },
  'no-document-write': {
    title: 'Избегайте document.write()',
    solution: 'Замените document.write() на современные методы вставки'
  },
  'errors-in-console': {
    title: 'Ошибки в консоли браузера',
    solution: 'Исправьте все JavaScript ошибки в консоли'
  },
  'image-aspect-ratio': {
    title: 'Правильные пропорции изображений',
    solution: 'Задайте правильные width и height атрибуты'
  },
  'color-contrast': {
    title: 'Улучшите контраст текста',
    solution: 'Используйте цвета с контрастом минимум 4.5:1'
  },
  'image-alt': {
    title: 'Добавьте alt текст к изображениям',
    solution: 'Добавьте описательный alt атрибут ко всем <img>'
  },
  'link-name': {
    title: 'Добавьте текст ссылкам',
    solution: 'Убедитесь что все ссылки имеют видимый текст'
  },
  'button-name': {
    title: 'Добавьте текст кнопкам',
    solution: 'Добавьте aria-label или текст к кнопкам без текста'
  },
  'document-title': {
    title: 'Добавьте заголовок документа',
    solution: 'Добавьте уникальный <title> на каждую страницу'
  },
  'html-has-lang': {
    title: 'Укажите язык документа',
    solution: 'Добавьте lang="ru" атрибут к <html>'
  },
  'meta-viewport': {
    title: 'Добавьте meta viewport',
    solution: 'Добавьте <meta name="viewport" content="width=device-width, initial-scale=1">'
  },
  'meta-description': {
    title: 'Добавьте meta description',
    solution: 'Добавьте <meta name="description" content="описание страницы">'
  },
  'link-text': {
    title: 'Используйте описательный текст ссылок',
    solution: 'Избегайте "нажмите здесь", используйте описательный текст'
  },
  'crawlable-anchors': {
    title: 'Ссылки недоступны для сканирования',
    solution: 'Используйте <a href="..."> вместо onclick обработчиков'
  },
  'is-crawlable': {
    title: 'Страница блокирует индексацию',
    solution: 'Проверьте robots.txt и meta robots теги'
  },
  'robots-txt': {
    title: 'Проблемы с robots.txt',
    solution: 'Убедитесь что robots.txt не блокирует важные страницы'
  },
  'hreflang': {
    title: 'Добавьте hreflang',
    solution: 'Используйте hreflang для мультиязычных сайтов'
  },
  'canonical': {
    title: 'Добавьте canonical URL',
    solution: 'Используйте <link rel="canonical"> для избежания дублей'
  },
  'font-size': {
    title: 'Увеличьте размер шрифта',
    solution: 'Используйте минимум 16px для основного текста'
  },
  'tap-targets': {
    title: 'Увеличьте размер кликабельных элементов',
    solution: 'Делайте кнопки и ссылки минимум 48x48px'
  },
  'largest-contentful-paint': {
    title: 'Улучшите Largest Contentful Paint',
    solution: 'Оптимизируйте загрузку главного контента, используйте preload'
  },
  'first-contentful-paint': {
    title: 'Улучшите First Contentful Paint',
    solution: 'Уменьшите время загрузки критических ресурсов'
  },
  'speed-index': {
    title: 'Улучшите индекс скорости',
    solution: 'Оптимизируйте видимую часть страницы для быстрой загрузки'
  },
  'total-blocking-time': {
    title: 'Сократите время блокировки',
    solution: 'Разбейте длинные JS задачи на более мелкие'
  },
  'cumulative-layout-shift': {
    title: 'Устраните сдвиги макета',
    solution: 'Задайте размеры изображениям, избегайте динамического контента сверху'
  },
  'interactive': {
    title: 'Улучшите время до интерактивности',
    solution: 'Сократите выполнение JavaScript, используйте code splitting'
  },
  'geolocation-on-start': {
    title: 'Не запрашивайте геолокацию при загрузке',
    solution: 'Запрашивайте геолокацию только при действии пользователя'
  },
  'notification-on-start': {
    title: 'Не запрашивайте уведомления при загрузке',
    solution: 'Запрашивайте разрешения только при действии пользователя'
  },
  'deprecations': {
    title: 'Используются устаревшие API',
    solution: 'Обновите код, используйте современные API'
  },
};

export function getTranslation(auditKey: string, originalTitle: string): RecommendationTranslation {
  return recommendationsMap[auditKey] || {
    title: originalTitle,
    solution: 'Подробности в документации Lighthouse'
  };
}
