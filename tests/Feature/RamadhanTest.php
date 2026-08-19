<?php

namespace Tests\Feature;

use Tests\TestCase;

class RamadhanTest extends TestCase
{
    /**
     * Test that the Ramadhan schedule page loads successfully
     */
    public function test_ramadhan_page_can_be_rendered(): void
    {
        $response = $this->get('/ramadhan');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Public/Ramadhan')
            ->has('scheduleData')
            ->has('scheduleData.schedule', 30)
            ->has('currentParams')
        );
    }

    /**
     * Test that the Ramadhan schedule page accepts custom city parameters
     */
    public function test_ramadhan_page_accepts_location_parameters(): void
    {
        $response = $this->get('/ramadhan?lat=-6.9175&lng=107.6191&city=Bandung&year=1446');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Public/Ramadhan')
            ->where('scheduleData.city', 'Bandung')
            ->has('scheduleData.schedule', 30)
        );
    }

    /**
     * Test that the PDF export generates a valid response
     */
    public function test_ramadhan_pdf_can_be_exported(): void
    {
        $response = $this->get('/ramadhan/pdf?city=Jakarta&year=1446');

        $response->assertStatus(200);
        $this->assertNotEmpty($response->getContent());
    }
}
