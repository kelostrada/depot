<?php

namespace App\Http\Controllers\Admin\Concerns;

/**
 * MariaDB reports column defaults as SQL literals ('PLN', '', NULL), and
 * Backpack 7's setFromDb() copies them into the create form verbatim, so a new
 * stock would default to the currency "'PLN'" and a new product to the upc
 * "NULL". Backpack 4 (via doctrine/dbal) unquoted them; do the same here.
 */
trait CleansDbDefaults
{
    protected function cleanDbDefaults(): void
    {
        foreach ($this->crud->fields() as $key => $field) {
            $default = $field['default'] ?? null;

            if (! is_string($default)) {
                continue;
            }

            if (strcasecmp($default, 'NULL') === 0) {
                $this->crud->modifyField($key, ['default' => null]);
            } elseif (preg_match("/^'(.*)'$/s", $default, $matches)) {
                $this->crud->modifyField($key, ['default' => str_replace("''", "'", $matches[1])]);
            }
        }
    }
}
