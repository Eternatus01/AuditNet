/* eslint-disable no-console */

type LogLevel = "log" | "info" | "warn" | "error" | "debug";

interface Logger {
  log: (...args: unknown[]) => void;
  info: (...args: unknown[]) => void;
  warn: (...args: unknown[]) => void;
  error: (...args: unknown[]) => void;
  debug: (...args: unknown[]) => void;
}

const isDevelopment = import.meta.env.DEV;

const createLogFunction = (level: LogLevel, alwaysLog = false) => {
  return (...args: unknown[]) => {
    if (alwaysLog || isDevelopment) {
      console[level](...args);
    }
  };
};

export const logger: Logger = {
  log: createLogFunction("log", false),
  info: createLogFunction("info", false),
  debug: createLogFunction("debug", false),
  warn: createLogFunction("warn", true),
  error: createLogFunction("error", true),
};

export const logGroup = (label: string, callback: () => void): void => {
  if (isDevelopment) {
    console.group(label);
    callback();
    console.groupEnd();
  }
};

export const logTime = (label: string): (() => void) => {
  if (isDevelopment) {
    console.time(label);
    return () => console.timeEnd(label);
  }
  return () => {};
};

export const logTable = (data: unknown): void => {
  if (isDevelopment && console.table) {
    console.table(data);
  }
};

export default logger;

