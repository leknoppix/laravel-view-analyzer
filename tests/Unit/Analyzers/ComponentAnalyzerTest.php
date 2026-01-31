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
}
