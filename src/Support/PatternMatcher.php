<?php

namespace LaravelViewAnalyzer\Support;

class PatternMatcher
{
    public static function matchViewCall(string $content): array
    {
        $patterns = [
            // response()->view('name') - Must come before view('name') to avoid double matching
            '/response\(\)->view\s*\(\s*[\'"]([^\'"]+)[\'"]\s*[,\)]/',
            // view('name') or view('name', [...])
            '/(?<!->)view\s*\(\s*[\'"]([^\'"]+)[\'"]\s*[,\)]/',
            // ->view('name') or ->markdown('name') - used in Mailables, Notifications, etc.
            '/->(?:view|markdown)\s*\(\s*[\'"]([^\'"]+)[\'"]\s*[,\)]/',
            // View::make('name')
            '/View::make\s*\(\s*[\'"]([^\'"]+)[\'"]\s*[,\)]/',
            // $users->links('name')
            '/->links\s*\(\s*[\'"]([^\'"]+)[\'"]\s*[,\)]/',
            // Paginator::defaultView('name')
            '/Paginator::defaultView\s*\(\s*[\'"]([^\'"]+)[\'"]\s*[,\)]/',
            // Paginator::defaultSimpleView('name')
            '/Paginator::defaultSimpleView\s*\(\s*[\'"]([^\'"]+)[\'"]\s*[,\)]/',
        ];

        $matches = [];
        $processedPositions = [];

        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $content, $found, PREG_OFFSET_CAPTURE)) {
                foreach ($found[1] as $match) {
                    $position = $match[1];

                    // Avoid double counting same view reference if patterns overlap
                    if (in_array($position, $processedPositions)) {
                        continue;
                    }

                    $matches[] = [
                        'view' => $match[0],
                        'position' => $position,
                    ];
                    $processedPositions[] = $position;
                }
            }
        }

        return $matches;
    }

    public static function matchBladeDirective(string $content, string $directive): array
    {
        $matches = [];

        if (in_array($directive, ['includeWhen', 'includeUnless'])) {
            // View is the second argument: @includeWhen(cond, 'view')
            $pattern = '/@' . $directive . '\s*\(\s*[^,]+,\s*[\'"]([^\'"]+)[\'"]/';
            if (preg_match_all($pattern, $content, $found, PREG_OFFSET_CAPTURE)) {
                foreach ($found[1] as $match) {
                    $matches[] = [
                        'view' => $match[0],
                        'position' => $match[1],
                        'directive' => $directive,
                    ];
                }
            }
        } elseif ($directive === 'includeFirst') {
            // View is an array: @includeFirst(['view1', 'view2'])
            $pattern = '/@includeFirst\s*\(\s*\[([^\]]+)\]/';
            if (preg_match_all($pattern, $content, $found, PREG_OFFSET_CAPTURE)) {
                foreach ($found[1] as $match) {
                    $arrayContent = $match[0];
                    $basePosition = $match[1];
                    if (preg_match_all('/[\'"]([^\'"]+)[\'"]/', $arrayContent, $stringMatches, PREG_OFFSET_CAPTURE)) {
                        foreach ($stringMatches[1] as $stringMatch) {
                            $matches[] = [
                                'view' => $stringMatch[0],
                                'position' => $basePosition + $stringMatch[1],
                                'directive' => $directive,
                            ];
                        }
                    }
                }
            }
        } elseif ($directive === 'each') {
            // @each('view', $collection, 'variable', 'empty-view')
            // Match 1st argument
            $pattern1 = '/@each\s*\(\s*[\'"]([^\'"]+)[\'"]/';
            if (preg_match_all($pattern1, $content, $found1, PREG_OFFSET_CAPTURE)) {
                foreach ($found1[1] as $match) {
                    $matches[] = [
                        'view' => $match[0],
                        'position' => $match[1],
                        'directive' => $directive,
                    ];
                }
            }
            // Match 4th argument (optional empty view)
            $pattern4 = '/@each\s*\(\s*[\'"][^\'"]+[\'"]\s*,\s*[^,]+\s*,\s*[^,]+\s*,\s*[\'"]([^\'"]+)[\'"]/';
            if (preg_match_all($pattern4, $content, $found4, PREG_OFFSET_CAPTURE)) {
                foreach ($found4[1] as $match) {
                    $matches[] = [
                        'view' => $match[0],
                        'position' => $match[1],
                        'directive' => $directive,
                    ];
                }
            }
        } else {
            // Standard: @directive('view')
            $pattern = '/@' . $directive . '\s*\(\s*[\'"]([^\'"\)]+)[\'"]/';
            if (preg_match_all($pattern, $content, $found, PREG_OFFSET_CAPTURE)) {
                foreach ($found[1] as $match) {
                    $matches[] = [
                        'view' => $match[0],
                        'position' => $match[1],
                        'directive' => $directive,
                    ];
                }
            }
        }

        return $matches;
    }

    public static function matchComponentTag(string $content): array
    {
        $pattern = '/<x-([a-zA-Z0-9\-\._:]+)/';
        $matches = [];

        if (preg_match_all($pattern, $content, $found, PREG_OFFSET_CAPTURE)) {
            foreach ($found[1] as $match) {
                $tagName = $match[0];
                // Convert kebab-case and dots to view name: components.kebab-case
                $viewName = 'components.' . str_replace(':', '.', $tagName);

                $matches[] = [
                    'view' => $viewName,
                    'position' => $match[1],
                    'tag' => $tagName,
                ];
            }
        }

        return $matches;
    }

    public static function shouldExclude(string $path, array $excludePaths): bool
    {
        $normalizedPath = PathHelper::normalize($path);

        foreach ($excludePaths as $exclude) {
            $exclude = PathHelper::normalize($exclude);
            if (str_contains($normalizedPath, '/' . $exclude . '/') || str_ends_with($normalizedPath, '/' . $exclude)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a view name matches any of the given patterns (supporting wildcards).
     */
    public static function matchesViewPattern(string $viewName, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if ($pattern === $viewName) {
                return true;
            }

            $regex = str_replace(['.', '*'], ['\.', '.*'], $pattern);
            if (preg_match('/^' . $regex . '$/', $viewName)) {
                return true;
            }
        }

        return false;
    }
}
