<?php

namespace App\PHPStan;

use PHPStan\Reflection\ConstantReflection;
use PHPStan\Rules\Constants\AlwaysUsedClassConstantsExtension;

class MyAlwaysUsedClassConstantsExtension implements AlwaysUsedClassConstantsExtension
{
    public function isAlwaysUsed(ConstantReflection $constant): bool
    {
        return
            $constant->getName() === 'HEADERS' ||
            $constant->getName() === 'HYDRA_MEMBER' ||
            $constant->getName() === 'HYDRA_DESCRIPTION' ||
            $constant->getName() === 'HYDRA_TOTAL_ITEMS' ||

            $constant->getName() === 'USERS_URL' ||
            $constant->getName() === 'ADMIN_MAIL' ||
            $constant->getName() === 'EDITOR_MAIL' ||
            $constant->getName() === 'USER_MAIL' ||
            $constant->getName() === 'PLAIN_PASSWORD' ||
            $constant->getName() === 'EDITOR_ID' ||
            $constant->getName() === 'USER_ID' ||

            $constant->getName() === 'CARTS_URL' ||
            $constant->getName() === 'CART_ID' ||

            $constant->getName() === 'ITEMS_URL' ||
            $constant->getName() === 'CART_ITEM_ID' ||
            $constant->getName() === 'NON_EXISTING_CART_ITEM_ID' ||

            $constant->getName() === 'PRODUCTS_URL' ||
            $constant->getName() === 'PRODUCT_ID_1' ||
            $constant->getName() === 'PRODUCT_ID_2'
        ;
    }
}
