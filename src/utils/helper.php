<?php

namespace Zec\Utils;

if (!function_exists('sort_errors')) {
    function sort_errors(array $map, ?string $parentKey = null): array {
        $children = [];

        foreach ($map as $child) {
            if ((isset($child['parent']) && $child['parent'] === $parentKey) || (!isset($child['parent']) && $parentKey === null)) {
                $newChild = $child;
                $key = $child['key'];
                unset($newChild['parent']); // Clean up the parent key

                // Recursively find children
                $newChildChildren = sort_errors($map, $key);
                if (!empty($newChildChildren)) {
                    $newChild['children'] = $newChildChildren;
                }
                
                $children[] = $newChild;
            }
        }

        return $children;
    }
}