FROM node:20-alpine

# Устанавливаем Chrome
RUN apk add --no-cache \
    chromium \
    nss \
    freetype \
    harfbuzz \
    ca-certificates \
    ttf-freefont

# Устанавливаем Lighthouse
WORKDIR /app
COPY package.json .
RUN npm install

# Копируем скрипты
COPY lighthouse-runner.js .
COPY lighthouse-server.js .

# Запускаем HTTP сервер
CMD ["node", "lighthouse-server.js"]