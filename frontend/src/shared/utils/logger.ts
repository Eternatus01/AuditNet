/**
 * Logger utility для управления логами в зависимости от окружения
 * В production console.log будет отключен, error и warn - всегда активны
 */

/* eslint-disable no-console */
// В этом файле разрешено использовать console - это утилита для логирования

type LogLevel = "log" | "info" | "warn" | "error" | "debug";

interface Logger {
  log: (...args: unknown[]) => void;
  info: (...args: unknown[]) => void;
  warn: (...args: unknown[]) => void;
  error: (...args: unknown[]) => void;
  debug: (...args: unknown[]) => void;
}

const isDevelopment = import.meta.env.DEV;

/**
 * Создает функцию логирования с проверкой окружения
 */
const createLogFunction = (level: LogLevel, alwaysLog = false) => {
  return (...args: unknown[]) => {
    if (alwaysLog || isDevelopment) {
      console[level](...args);
    }
  };
};

/**
 * Logger с автоматическим отключением в production
 */
export const logger: Logger = {
  // Только для разработки
  log: createLogFunction("log", false),
  info: createLogFunction("info", false),
  debug: createLogFunction("debug", false),
  
  // Всегда активны (даже в production)
  warn: createLogFunction("warn", true),
  error: createLogFunction("error", true),
};

/**
 * Группировка логов (только в dev)
 */
export const logGroup = (label: string, callback: () => void): void => {
  if (isDevelopment) {
    console.group(label);
    callback();
    console.groupEnd();
  }
};

/**
 * Логирование с таймером (только в dev)
 */
export const logTime = (label: string): (() => void) => {
  if (isDevelopment) {
    console.time(label);
    return () => console.timeEnd(label);
  }
  return () => {}; // noop в production
};

/**
 * Таблица для логирования (только в dev)
 */
export const logTable = (data: unknown): void => {
  if (isDevelopment && console.table) {
    console.table(data);
  }
};

export default logger;

