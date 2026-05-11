import type { RenderRun } from '@/types'
import { formatBytes, formatMs } from './format-metrics'

export function hasRunMetrics(run: RenderRun): boolean {
  return run.status === 'success'
    && run.avgMs !== null
    && run.minMs !== null
    && run.maxMs !== null
    && run.p95Ms !== null
}

export function formatRunMs(value: number | null): string {
  return value === null ? '-' : formatMs(value)
}

export function formatRunBytes(value: number | null): string {
  return value === null ? '-' : formatBytes(value)
}

export function formatRunDate(iso: string): string {
  return new Date(iso).toLocaleString()
}

export function runStatusLabel(status: string): string {
  if (status === 'success') return 'Успешно'
  if (status === 'failure') return 'Ошибка'
  if (status === 'in_progress') return 'В процессе'
  return status
}

export function runStatusColor(status: string): string {
  if (status === 'success') return 'success'
  if (status === 'failure') return 'error'
  if (status === 'in_progress') return 'warning'
  return 'default'
}
