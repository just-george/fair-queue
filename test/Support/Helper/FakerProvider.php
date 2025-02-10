<?php

declare(strict_types=1);

namespace Fair\Queue\Test\Support\Helper;

use Codeception\Module;
use Faker\Factory as FakerFactory;
use Faker\Generator as FakerGenerator;

class FakerProvider extends Module
{
    public function getFaker(): FakerGenerator
    {
        $faker = FakerFactory::create();

        return $faker;
    }
}
