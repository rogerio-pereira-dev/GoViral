<?php

use App\Contracts\ReportGenerator;
use App\Services\Llm\GeminiReportGenerator;

it('binds ReportGenerator contract to GeminiReportGenerator', function (): void {
    $instance = app(ReportGenerator::class);

    expect($instance)->toBeInstanceOf(GeminiReportGenerator::class);
});
