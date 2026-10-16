<?php

namespace Tests\Feature;

use App\Services\CampaignManagerService;
use App\Services\GoogleWorkspaceService;
use Mockery;
use Tests\TestCase;

class BrandliftSubmitResponseTest extends TestCase
{
    public function test_submit_response_sends_row_as_sequential_list_of_strings_when_fields_are_empty(): void
    {
        $this->mock(CampaignManagerService::class);

        $capturedRow = null;

        $this->mock(GoogleWorkspaceService::class, function ($mock) use (&$capturedRow): void {
            $mock->shouldReceive('appendRowToSheet')
                ->once()
                ->with('sheet-123', Mockery::on(function (array $values) use (&$capturedRow): bool {
                    $capturedRow = $values;

                    return true;
                }))
                ->andReturn(true);
        });

        $response = $this->post('/api/brandlift/submit?sheetId=sheet-123', [
            'userId' => '1700000000000123',
            'q1' => '¿Conoces la marca?',
            'a1' => 'Sí',
            'q2' => '¿La recomendarías?',
            'a2' => 'No',
            'campaign' => '2026_10_Test_Campaign',
            'market' => 'MEX',
            'group' => '',
        ]);

        $response->assertOk()->assertJson(['success' => true]);

        $this->assertIsArray($capturedRow);
        $this->assertTrue(array_is_list($capturedRow), 'Row must be a sequential list, not an associative array.');
        $this->assertCount(15, $capturedRow);
        $this->assertContainsOnly('string', $capturedRow);

        $this->assertSame('1700000000000123', $capturedRow[0]);
        $this->assertSame('¿Conoces la marca?', $capturedRow[1]);
        $this->assertSame('Sí', $capturedRow[2]);
        $this->assertSame('¿La recomendarías?', $capturedRow[3]);
        $this->assertSame('No', $capturedRow[4]);
        $this->assertSame('', $capturedRow[5]);
        $this->assertSame('MEX', $capturedRow[11]);
        $this->assertSame('2026_10_Test_Campaign', $capturedRow[12]);
        $this->assertSame('', $capturedRow[13]);

        $this->assertSame(
            json_encode([array_values($capturedRow)]),
            json_encode([$capturedRow]),
            'Row must serialize as a JSON array.'
        );
    }
}
