<?php

namespace Tests\app\Infrastructure\Controller;

use App\Application\CoinLoreCryptoDataSource\CoinLoreCryptoDataSource;
use App\Infrastructure\Cache\WalletCache;
use App\Domain\Coin;
use Illuminate\Http\Response;
use Tests\TestCase;
use Exception;
use Mockery;

class BuyCoinControllerTest extends TestCase
{
    private CoinLoreCryptoDataSource $CoinLoreCryptoDataSource;

    /**
     * @setUp
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->CoinLoreCryptoDataSource = Mockery::mock(CoinLoreCryptoDataSource::class);

        $this->app->bind(CoinLoreCryptoDataSource::class, fn () => $this->CoinLoreCryptoDataSource);
    }
    /**
     * @test
     */
    public function genericError()
    {

        $data = ['coin_id' => '90','wallet_id'=>'1', 'amount_usd'=> 0];

        $this->CoinLoreCryptoDataSource
            ->expects('getCoin')
            ->with(90)
            ->once()
            ->andThrow(new Exception('Service unavailable',503));

        $response = $this->post('api/coin/buy', $data);

        $response->assertStatus(Response::HTTP_SERVICE_UNAVAILABLE)->assertExactJson(['error' => 'Service unavailable']);
    }

    /**
     * @test
     */
    public function buyCoinSuccessful()
    {
        $wallet = new WalletCache();
        $wallet->open();

        $data = ['coin_id' => '90','wallet_id'=> '1', 'amount_usd'=> 0];
        $coin = new Coin('1','1','1','1','1',1);

        $this->CoinLoreCryptoDataSource
            ->expects('getCoin')
            ->with(90)
            ->once()
            ->andReturn($coin);

        $response = $this->post('api/coin/buy', $data);

        $response->assertStatus(Response::HTTP_OK)->assertExactJson(['successful operation']);
    }

    /**
     * @test
     */
    public function coinNotExists()
    {
        $data = ['coin_id' => '90','wallet_id'=>'1', 'amount_usd'=> 0];

        $this->CoinLoreCryptoDataSource
            ->expects('getCoin')
            ->with(90)
            ->once()
            ->andThrow(new Exception('A coin with specified ID was not found.',404));

        $response = $this->post('api/coin/buy', $data);

        $response->assertStatus(Response::HTTP_NOT_FOUND)->assertExactJson(['error' => 'A coin with specified ID was not found.']);
    }
}
