import { defineStore } from 'pinia'
import type { Engine } from '@/types'

export const useEnginesStore = defineStore('engines', () => {
  const engines: Engine[] = [
    { id: 'handlebars', name: 'Handlebars', syntaxAlias: 'handlebars' },
    { id: 'pug', name: 'Pug', syntaxAlias: 'jade' },
    { id: 'ejs', name: 'EJS', syntaxAlias: 'html' },
  ]

  function getById(id: string): Engine | undefined {
    return engines.find(e => e.id === id)
  }

  return { engines, getById }
})
