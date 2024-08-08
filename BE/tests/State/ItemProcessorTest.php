<?php

namespace App\Tests\State;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\State\ItemProcessor;
use App\Tests\SetUpTrait;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ItemProcessorTest extends ApiTestCase
{
    use SetUpTrait;

    protected function setUp(): void
    {
        $this->setUpRepositories();
        $this->itemProcessor = new ItemProcessor($this->cartRepository, $this->cartItemRepository);
    }

    public function testProcessDeleteValidItem(): void
    {
        $this->assertNotNull($this->cartItemRepository->find(self::CART_ITEM_ID));

        $uriVariables = ['id' => self::CART_ITEM_ID];
        $this->itemProcessor->process(null, new Delete(), $uriVariables);

        $this->assertNull($this->cartItemRepository->find(self::CART_ITEM_ID));
    }

    public function testProcessDeleteNonExistingItem(): void
    {
        $uriVariables = ['id' => self::NON_EXISTING_CART_ITEM_ID];

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Item not found.');

        $this->itemProcessor->process(null, new Delete(), $uriVariables);
    }

    public function testProcessDeleteWithoutId(): void
    {
        $uriVariables = [];

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Id of item not found.');

        $this->itemProcessor->process(null, new Delete(), $uriVariables);
    }
}
