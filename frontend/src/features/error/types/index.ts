export interface ErrorPageProps {
  code: number;
  title: string;
  description: string;
}

export const ERROR_PAGES = {
  NOT_FOUND: {
    code: 404,
    title: 'Страница не найдена',
    description: 'К сожалению, запрашиваемая страница не существует или была удалена.',
  },
  FORBIDDEN: {
    code: 403,
    title: 'Доступ запрещён',
    description: 'У вас нет прав для просмотра этой страницы.',
  },
  SERVER_ERROR: {
    code: 500,
    title: 'Ошибка сервера',
    description: 'Произошла внутренняя ошибка сервера. Попробуйте позже.',
  },
} as const;

