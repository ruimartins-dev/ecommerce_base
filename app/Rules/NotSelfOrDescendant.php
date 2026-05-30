<?php

declare(strict_types=1);

namespace App\Rules;

use App\Models\Category;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Ensures a category is not assigned a parent that would create a hierarchy
 * loop. A category may not be its own parent, nor may it be parented to any of
 * its own descendants (which would produce an A → B → A cycle).
 */
class NotSelfOrDescendant implements ValidationRule
{
    public function __construct(private readonly Category $category)
    {
    }

    /**
     * @param  Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $parentId = (int) $value;

        if ($parentId === $this->category->id) {
            $fail(__('A category cannot be its own parent.'));

            return;
        }

        if (in_array($parentId, $this->category->descendantIds(), true)) {
            $fail(__('You cannot move a category under one of its own descendants.'));
        }
    }
}

