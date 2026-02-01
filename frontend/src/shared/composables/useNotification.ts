import { ref } from 'vue';

export type NotificationType = 'success' | 'error' | 'warning' | 'info';

interface Notification {
  id: number;
  message: string;
  type: NotificationType;
  duration: number;
}

const notifications = ref<Notification[]>([]);
let notificationId = 0;

export function useNotification() {
  const show = (message: string, type: NotificationType = 'info', duration = 5000) => {
    const id = ++notificationId;
    
    notifications.value.push({
      id,
      message,
      type,
      duration,
    });

    if (duration > 0) {
      setTimeout(() => {
        remove(id);
      }, duration);
    }
  };

  const remove = (id: number) => {
    const index = notifications.value.findIndex((n) => n.id === id);
    if (index > -1) {
      notifications.value.splice(index, 1);
    }
  };

  const success = (message: string, duration?: number) => show(message, 'success', duration);
  const error = (message: string, duration?: number) => show(message, 'error', duration);
  const warning = (message: string, duration?: number) => show(message, 'warning', duration);
  const info = (message: string, duration?: number) => show(message, 'info', duration);

  return {
    notifications,
    show,
    remove,
    success,
    error,
    warning,
    info,
  };
}
