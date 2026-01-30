export interface User {
  id: number;
  name: string;
  email: string;
}

export interface SignUpCredentials {
  name: string;
  email: string;
  password: string;
}

export interface SignInCredentials {
  email: string;
  password: string;
}

export interface AuthResponse {
  user: User;
  token?: string;
}

export interface LogoutResponse {
  message: string;
}

export type ProfileResponse = User;

export interface ValidationErrors {
  [field: string]: string[];
}

export interface ApiErrorResponse {
  message?: string;
  errors?: ValidationErrors;
}

export interface ApiError {
  response?: {
    status: number;
    data: ApiErrorResponse;
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

export interface AuthState {
  user: User | null;
  error: string | null;
  isProfileLoading: boolean;
  isAuthenticated: boolean;
}
