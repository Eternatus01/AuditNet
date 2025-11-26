import * as yup from "yup";

// Общие правила валидации
export const emailSchema = yup
  .string()
  .required("Email обязателен для заполнения")
  .email("Введите корректный email");

export const passwordSchema = yup
  .string()
  .required("Пароль обязателен для заполнения")
  .min(8, "Пароль должен содержать минимум 8 символов");

export const nameSchema = yup
  .string()
  .required("Имя обязательно для заполнения")
  .min(2, "Имя должно содержать минимум 2 символа")
  .max(50, "Имя не должно превышать 50 символов");

// Схема для формы логина
export const loginSchema = yup.object({
  email: emailSchema,
  password: passwordSchema,
});

// Схема для формы регистрации
export const registerSchema = yup.object({
  name: nameSchema,
  email: emailSchema,
  password: passwordSchema,
});

// Типы для форм
export type LoginFormData = yup.InferType<typeof loginSchema>;
export type RegisterFormData = yup.InferType<typeof registerSchema>;

