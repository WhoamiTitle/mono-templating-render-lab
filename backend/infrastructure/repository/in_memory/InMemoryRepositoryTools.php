<?php

declare(strict_types=1);

namespace infrastructure\repository\in_memory;

final class InMemoryRepositoryTools
{
    public static function cloneModel(object $model): object
    {
        return clone $model;
    }

    /**
     * @template T of object
     * @param array<string, T> $models
     * @return array<int, T>
     */
    public static function filterByProperties(array $models, array $filters = []): array
    {
        $result = [];

        foreach ($models as $model) {
            if (!self::matchesFilters($model, $filters)) {
                continue;
            }

            /** @var T $cloned */
            $cloned = self::cloneModel($model);
            $result[] = $cloned;
        }

        return $result;
    }

    private static function matchesFilters(object $model, array $filters): bool
    {
        foreach ($filters as $property => $expectedValue) {
            if (!property_exists($model, (string)$property)) {
                return false;
            }

            $actualValue = $model->{$property};
            if ($actualValue !== $expectedValue) {
                return false;
            }
        }

        return true;
    }
}
