# CLAUDE.md

This file provides guidance for AI coding agents working in the `frontend` app.

## Project Overview

This is a frontend application built with Vue 3 and Vite.

## Run Locally

Start the development server with:

```bash
npm run dev
```

Useful additional commands:

```bash
npm run build
npm run type-check
npm run lint
```

## Tech Stack

- Vue 3
- Vue Router
- Pinia
- Vuetify

## Code Structure

- `src/App.vue` is the root application component.
- `src/router/index.ts` contains the router setup.

## Guidance

- Keep components small and focused.
- Use `vue-router` for navigation.
- Use Pinia for shared application state.
- Prefer Vuetify components over creating custom UI components from scratch.
- Prefer clear, minimal abstractions over premature complexity.
- Prefer Vue Single File Components with `<script setup lang="ts">` where appropriate.
