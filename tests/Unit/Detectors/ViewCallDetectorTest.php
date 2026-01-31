<?php

namespace LaravelViewAnalyzer\Tests\Unit\Detectors;

use LaravelViewAnalyzer\Detectors\ViewCallDetector;
use LaravelViewAnalyzer\Tests\TestCase;

class ViewCallDetectorTest extends TestCase
{
    private ViewCallDetector $detector;

    protected function setUp(): void
    {
        parent::setUp();
        $this->detector = new ViewCallDetector();
    }

    public function test_it_detects_standard_view_calls()
    {
        $content = <<<'PHP'
<?php
    return view('pages.home');
    View::make('pages.about');
    response()->view('pages.contact');
PHP;

        $matches = $this->detector->detect($content);

        $this->assertCount(3, $matches);
        $views = array_column($matches, 'view');

        $this->assertContains('pages.home', $views);
        $this->assertContains('pages.about', $views);
        $this->assertContains('pages.contact', $views);
    }

    public function test_it_detects_dynamic_view_calls()
    {
        $content = <<<'PHP'
<?php
    $view = 'pages.' . $type;
    return view($view);
    return View::make($dynamicVar);
PHP;

        $matches = $this->detector->detectDynamic($content);

        $this->assertCount(2, $matches);

        // Check patterns found
        $patterns = array_column($matches, 'pattern');
        // Regex matches "view($view" or "View::make($dynamicVar" depending on regex
        // The regex in class matches until the variable name end

        $this->assertStringContainsString('view($view', $patterns[0]);
        $this->assertStringContainsString('View::make($dynamicVar', $patterns[1]);
    }

    public function test_it_returns_empty_array_when_no_matches()
    {
        $content = '<?php echo "Hello World";';

        $this->assertEmpty($this->detector->detect($content));
        $this->assertEmpty($this->detector->detectDynamic($content));
    }
}
