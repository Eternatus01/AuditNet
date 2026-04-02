export interface ApiError {
  response?: {
    status: number;
    data: {
      message?: string;
      error?: string;
      errors?: {
        [field: string]: string[];
      };
    };
  };
  message: string;
}

export function isApiError(error: unknown): error is ApiError {
  return (
    typeof error === "object" &&
    error !== null &&
    "message" in error &&
    typeof (error as ApiError).message === "string"
  );
}

export function extractApiErrorMessage(error: unknown, defaultMessage: string): string {
  if (!isApiError(error)) return defaultMessage;

  const response = (error as ApiError).response;
  if (!response) return defaultMessage;

  if (response.status === 422) {
    if (response.data?.errors) {
      const messages = Object.values(response.data.errors).flat();
      return messages.join(" ");
    }
    return response.data?.message || "Ошибка валидации данных";
  }

  return response.data?.message || response.data?.error || defaultMessage;
}

export function handleApiError(error: unknown, defaultMessage: string): never {
  if (!isApiError(error)) {
    throw new Error(defaultMessage);
  }

  if (error.response?.status === 422 && error.response.data?.errors) {
    const validationErrors = Object.values(error.response.data.errors).flat();
    throw new Error(validationErrors.join(" "));
  }

  const message =
    error.response?.data?.message ||
    error.response?.data?.error ||
    error.message ||
    defaultMessage;

  throw new Error(message);
}

export function getErrorMessage(error: unknown, fallback = "Неизвестная ошибка"): string {
  if (error instanceof Error) {
    return error.message;
  }

  if (isApiError(error)) {
    return (
      error.response?.data?.message ||
      error.response?.data?.error ||
      error.message ||
      fallback
    );
  }

  if (typeof error === "string") {
    return error;
  }

  return fallback;
}

