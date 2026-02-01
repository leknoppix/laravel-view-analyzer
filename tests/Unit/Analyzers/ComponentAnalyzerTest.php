<?php

namespace LaravelViewAnalyzer\Tests\Unit\Analyzers;

use LaravelViewAnalyzer\Analyzers\ComponentAnalyzer;
use LaravelViewAnalyzer\Tests\TestCase;

class ComponentAnalyzerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (! is_dir(app_path('View/Components'))) {
            mkdir(app_path('View/Components'), 0777, true);
        }
    }

    protected function tearDown(): void
    {
        if (is_dir(app_path('View/Components'))) {
            $files = glob(app_path('View/Components') . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
        parent::tearDown();
    }

    public function test_it_detects_component_views(): void
    {
        $content = <<<'PHP'
<?php
namespace App\View\Components;
use Illuminate\View\Component;
class Alert extends Component {
    public function render() {
        return view('components.alert');
    }
}
PHP;
        file_put_contents(app_path('View/Components/Alert.php'), $content);

        $analyzer = new ComponentAnalyzer();
        $results = $analyzer->analyze();

        $this->assertTrue($results->contains('viewName', 'components.alert'));
    }

    public function test_it_detects_component_views_with_additional_data(): void
    {
        $content = <<<'PHP'
<?php
namespace App\View\Components;
use Illuminate\View\Component;
class Alert extends Component {
    public function render() {
        return view('components.alert-v2', ['foo' => 'bar']);
    }
}
PHP;
        file_put_contents(app_path('View/Components/AlertV2.php'), $content);

        $analyzer = new ComponentAnalyzer();
        $results = $analyzer->analyze();

        $this->assertTrue($results->contains('viewName', 'components.alert-v2'));
    }

    public function test_it_has_correct_name()
    {
        $analyzer = new ComponentAnalyzer();
        $this->assertEquals('Component Analyzer', $analyzer->getName());
    }

    public function test_it_has_correct_priority()
    {
        $analyzer = new ComponentAnalyzer();
        $this->assertEquals(40, $analyzer->getPriority());
    }

    public function test_it_respects_enabled_config()
    {
        $analyzer = new ComponentAnalyzer(['analyzers' => ['component' => ['enabled' => false]]]);
        $this->assertFalse($analyzer->isEnabled());

        $analyzer = new ComponentAnalyzer(['analyzers' => ['component' => ['enabled' => true]]]);
        $this->assertTrue($analyzer->isEnabled());
    }

    public function test_it_returns_empty_if_directory_missing()
    {
        $componentPath = app_path('View/Components');
        if (is_dir($componentPath)) {
            $files = glob($componentPath . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            // Supprimer le dossier parent aussi si vide
            @rmdir($componentPath);
            @rmdir(app_path('View'));
        }

        $analyzer = new ComponentAnalyzer();
        $results = $analyzer->analyze();

        $this->assertCount(0, $results);
    }
}
