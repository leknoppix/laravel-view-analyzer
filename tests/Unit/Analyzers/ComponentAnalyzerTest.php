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
        // On utilise la nouvelle clé 'component_path' pour pointer vers un dossier inexistant
        $analyzer = new ComponentAnalyzer(['component_path' => '/non/existent/components']);
        $results = $analyzer->analyze();

        $this->assertCount(0, $results);
    }

    public function test_it_skips_unreadable_files()
    {
        $tempDir = sys_get_temp_dir() . '/view_test_comp_unreadable_' . uniqid();
        mkdir($tempDir);
        $file = $tempDir . '/UnreadableComponent.php';
        touch($file);
        chmod($file, 0000);

        $analyzer = new ComponentAnalyzer(['component_path' => $tempDir]);
        $results = $analyzer->analyze();

        $this->assertCount(0, $results);

        chmod($file, 0644);
        unlink($file);
        rmdir($tempDir);
    }

    public function test_it_skips_empty_component_files()
    {
        $path = app_path('View/Components/EmptyComp.php');
        touch($path);

        $analyzer = new ComponentAnalyzer();
        $results = $analyzer->analyze();

        $this->assertCount(0, $results);
        unlink($path);
    }

    public function test_it_returns_empty_collection_if_directory_exists_but_is_empty(): void
    {
        $tempDir = sys_get_temp_dir() . '/view_test_comp_empty_dir_' . uniqid();
        mkdir($tempDir);

        $analyzer = new ComponentAnalyzer(['component_path' => $tempDir]);
        $results = $analyzer->analyze();

        $this->assertCount(0, $results);
        rmdir($tempDir);
    }

    public function test_it_uses_default_app_path_when_no_config_provided()
    {
        // On s'assure qu'un dossier de composants existe dans l'environnement Orchestra
        $path = app_path('View/Components');
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        $analyzer = new ComponentAnalyzer();
        $results = $analyzer->analyze();

        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $results);
    }
}
