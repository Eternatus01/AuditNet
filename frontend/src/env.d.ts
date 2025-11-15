declare module '*.vue' {
  import type { DefineComponent } from 'vue'
  const component: DefineComponent<{}, {}, any>
  export default component
}

// Добавь для JSX поддержки
declare namespace JSX {
  interface IntrinsicElements {
    [elem: string]: any
  }
}