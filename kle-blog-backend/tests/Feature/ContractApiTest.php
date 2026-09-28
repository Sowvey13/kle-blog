<?php

namespace Tests\Feature;

use App\Models\Contract;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContractApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_active_contracts_are_listed(): void
    {
        Contract::factory()->create(['title' => 'KVKK Aydınlatma Metni']);
        Contract::factory()->inactive()->create(['title' => 'Eski Sözleşme']);

        $this->getJson('/api/contracts')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'KVKK Aydınlatma Metni')
            ->assertJsonPath('data.0.slug', 'kvkk-aydinlatma-metni')
            ->assertJsonMissing(['title' => 'Eski Sözleşme']);
    }

    public function test_active_contract_is_shown_by_slug(): void
    {
        Contract::factory()->create(['title' => 'Kullanıcı Sözleşmesi']);

        $this->getJson('/api/contracts/kullanici-sozlesmesi')
            ->assertOk()
            ->assertJsonPath('data.title', 'Kullanıcı Sözleşmesi')
            ->assertJsonStructure(['data' => ['id', 'title', 'slug', 'content', 'is_active', 'created_at']]);
    }

    public function test_inactive_contract_detail_returns_not_found(): void
    {
        Contract::factory()->inactive()->create(['title' => 'Pasif Sözleşme']);

        $this->getJson('/api/contracts/pasif-sozlesme')->assertNotFound();
    }

    public function test_unknown_contract_returns_not_found(): void
    {
        $this->getJson('/api/contracts/olmayan')->assertNotFound();
    }
}
