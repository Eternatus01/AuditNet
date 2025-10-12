import TelegramBot from 'node-telegram-bot-api';

const token = 'YOUR_API_TOKEN';

const bot = new TelegramBot(token, {polling: true});

bot.onText(/\/start/, (msg) => {
  const chatId = msg.chat.id;
  bot.sendMessage(chatId, 'Привет! Я бот-помощник сервиса AuditNet.');
});

bot.on('message', (msg) => {
  const chatId = msg.chat.id;
  const text = msg.text;

  if (text.startsWith('/')) {
    return;
  }

  bot.sendMessage(chatId, `Вы отправили: ${text}`);
});