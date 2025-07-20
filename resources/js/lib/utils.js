// import { clsx } from "clsx";
// import { twMerge } from "tailwind-merge";

// export function cn(...inputs) {
//   return twMerge(clsx(inputs));
// }


// import { clsx } from 'clsx'
// import { twMerge } from 'tailwind-merge'

// export function cn(...inputs) {
//   return twMerge(clsx(inputs))
// }

// export function valueUpdater(updaterOrValue, ref) {
//   ref.value = typeof updaterOrValue === 'function' 
//     ? updaterOrValue(ref.value) 
//     : updaterOrValue
// }


// import type { Updater } from '@tanstack/vue-table'
// import type { ClassValue } from 'clsx'

// import type { Ref } from 'vue'
import { clsx } from 'clsx'
import { twMerge } from 'tailwind-merge'

export function cn(...inputs) {
  return twMerge(clsx(inputs))
}

export function valueUpdater(updaterOrValue, ref) {
  ref.value = typeof updaterOrValue === 'function'
    ? updaterOrValue(ref.value)
    : updaterOrValue
}
