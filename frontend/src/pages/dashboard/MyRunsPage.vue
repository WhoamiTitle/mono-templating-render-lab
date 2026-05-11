<script setup lang="ts">
import { onMounted } from 'vue'
import { useRenderRunsStore } from '@/stores/render-runs-store'
import {
  formatRunBytes,
  formatRunDate,
  formatRunMs,
  hasRunMetrics,
  runStatusColor,
  runStatusLabel,
} from '@/utils/render-run-format'

const store = useRenderRunsStore()

onMounted(() => store.fetchRuns())
</script>

<template>
  <v-container>
    <div class="text-h5 font-weight-bold mb-5">Мои запуски</div>

    <v-card v-if="store.loading" variant="outlined">
      <v-skeleton-loader type="table-row@5" />
    </v-card>

    <v-card v-else-if="store.runs.length === 0" variant="outlined">
      <v-card-text class="text-medium-emphasis text-center py-10">
        <p class="mb-3">Пока нет запусков.</p>
        <v-btn :to="{ name: 'sandbox' }" color="primary" variant="tonal">Запустить бенчмарк</v-btn>
      </v-card-text>
    </v-card>

    <v-table v-else>
      <thead>
        <tr>
          <th>Движок</th>
          <th>Статус</th>
          <th>Итерации</th>
          <th>Ср. (мс)</th>
          <th>Мин. (мс)</th>
          <th>Макс. (мс)</th>
          <th>P95 (мс)</th>
          <th>Размер</th>
          <th>Дата</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="run in store.runs" :key="run.id">
          <td>
            <v-chip size="x-small" color="primary" variant="tonal" class="text-uppercase">
              {{ run.engineId }}
            </v-chip>
          </td>
          <td>
            <v-chip size="x-small" :color="runStatusColor(run.status)" variant="tonal">
              {{ runStatusLabel(run.status) }}
            </v-chip>
          </td>
          <td>{{ run.iterations }}</td>
          <template v-if="hasRunMetrics(run)">
            <td>{{ formatRunMs(run.avgMs) }}</td>
            <td>{{ formatRunMs(run.minMs) }}</td>
            <td>{{ formatRunMs(run.maxMs) }}</td>
            <td>{{ formatRunMs(run.p95Ms) }}</td>
            <td>{{ formatRunBytes(run.outputBytes) }}</td>
          </template>
          <template v-else>
            <td colspan="5" class="text-medium-emphasis">Метрики пока не записаны</td>
          </template>
          <td>{{ formatRunDate(run.createdAt) }}</td>
        </tr>
      </tbody>
    </v-table>
  </v-container>
</template>
