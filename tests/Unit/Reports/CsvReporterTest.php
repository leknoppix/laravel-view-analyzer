<?php

namespace LaravelViewAnalyzer\Tests\Unit\Reports;

use LaravelViewAnalyzer\Reports\CsvReporter;
use LaravelViewAnalyzer\Results\AnalysisResult;
use LaravelViewAnalyzer\Results\UnusedView;
use LaravelViewAnalyzer\Results\ViewUsage;
use LaravelViewAnalyzer\Tests\TestCase;

class CsvReporterTest extends TestCase
{
    public function test_it_supports_csv_format()
    {
        $reporter = new CsvReporter();

        $this->assertTrue($reporter->supports('csv'));
        $this->assertFalse($reporter->supports('json'));
    }

    public function test_it_generates_csv_output()
    {
        $usedView = new ViewUsage('used.view', collect([]), null, 1, ['controller']);
        $unusedView = new UnusedView('unused.view', 'path/to/unused.blade.php', 100, now());

        $result = new AnalysisResult(
            totalViews: 2,
            usedViews: collect([$usedView]),
            unusedViews: collect([$unusedView]),
            dynamicViews: collect()
        );

        $reporter = new CsvReporter();
        $csv = $reporter->generate($result);

        $lines = explode("\n", trim($csv));

        // Header + 2 rows = 3 lines
        $this->assertCount(3, $lines);

        // Check header (fputcsv quotes fields with spaces)
        $this->assertEquals('"View Name",Status,"Reference Count","File Path",Types', $lines[0]);

        // Check content
        $this->assertStringContainsString('used.view,Used,1,,controller', $csv);
        $this->assertStringContainsString('unused.view,Unused,0,path/to/unused.blade.php,', $csv);
    }
}
