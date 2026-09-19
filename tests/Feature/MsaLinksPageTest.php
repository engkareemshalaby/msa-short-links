<?php

namespace Tests\Feature;

use Tests\TestCase;

class MsaLinksPageTest extends TestCase
{
    public function test_official_msa_links_page_contains_all_linktree_destinations(): void
    {
        $this->get('/msauniversity/links')->assertOk()
            ->assertSee('Apply now for 2026/2027')
            ->assertSee('https://newcomers.msa.edu.eg/faces/admission', false)
            ->assertSee('MSA University Admission Guide')
            ->assertSee('MSA Admissions Gateway')
            ->assertSee('MSA University Website')
            ->assertSee('https://www.instagram.com/msauniversityofficial', false)
            ->assertSee('https://www.facebook.com/msauniversity', false)
            ->assertSee('https://www.youtube.com/@msauniversity', false)
            ->assertSee('https://www.tiktok.com/@msauniversity', false)
            ->assertSee('https://www.threads.net/@msauniversity', false)
            ->assertSee('https://www.linkedin.com/school/modern-sciences-and-arts-university-msa-university/', false)
            ->assertSee('https://x.com/msauniversity', false);

        $this->get('/msauniversity/links')->assertOk()
            ->assertSee('utm_source=msa_go&amp;utm_medium=owned_web&amp;utm_campaign=go_contact_page', false)
            ->assertDontSee('Instagram-App', false)
            ->assertDontSee('sem=91', false)
            ->assertDontSee('Admission-Schools-Pull', false)
            ->assertDontSee('British education · Since 1996', false)
            ->assertSee('social instagram', false)
            ->assertSee('social youtube', false)
            ->assertSee('property="og:type" content="website"', false)
            ->assertSee('property="og:url" content="'.route('msa.links').'"', false)
            ->assertSee('name="twitter:card" content="summary_large_image"', false)
            ->assertSee('rel="canonical" href="'.route('msa.links').'"', false);
    }
}
