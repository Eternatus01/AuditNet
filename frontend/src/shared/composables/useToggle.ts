import { ref } from "vue";

/**
 * Composable для управления состоянием раскрытия/скрытия элементов
 * @param singleMode - если true, только один элемент может быть раскрыт одновременно
 */
export const useToggle = (singleMode = true) => {
  const expandedItems = ref(new Set<string>());

  /**
   * Переключает состояние элемента
   */
  const toggle = (key: string) => {
    const newSet = new Set(expandedItems.value);
    
    if (newSet.has(key)) {
      newSet.delete(key);
    } else {
      if (singleMode) {
        newSet.clear();
      }
      newSet.add(key);
    }
    
    expandedItems.value = newSet;
  };

  /**
   * Проверяет, раскрыт ли элемент
   */
  const isExpanded = (key: string): boolean => {
    return expandedItems.value.has(key);
  };

  /**
   * Раскрывает элемент
   */
  const expand = (key: string) => {
    if (!expandedItems.value.has(key)) {
      expandedItems.value = new Set([...expandedItems.value, key]);
    }
  };

  /**
   * Скрывает элемент
   */
  const collapse = (key: string) => {
    if (expandedItems.value.has(key)) {
      const newSet = new Set(expandedItems.value);
      newSet.delete(key);
      expandedItems.value = newSet;
    }
  };

  /**
   * Скрывает все элементы
   */
  const collapseAll = () => {
    if (expandedItems.value.size > 0) {
      expandedItems.value = new Set();
    }
  };

  return {
    expandedItems,
    toggle,
    isExpanded,
    expand,
    collapse,
    collapseAll,
  };
};

