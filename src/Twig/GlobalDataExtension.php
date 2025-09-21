<?php

namespace App\Twig;

use App\Repository\CategoryRepository;
use App\Repository\CityRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class GlobalDataExtension extends AbstractExtension
{
    public function __construct(
        private CategoryRepository $categoryRepository,
        private CityRepository $cityRepository
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_all_categories', [$this, 'getAllCategories']),
            new TwigFunction('get_all_cities', [$this, 'getAllCities']),
        ];
    }

    public function getAllCategories(): array
    {
        return $this->categoryRepository->findBy([], ['position' => 'ASC']);
    }

    public function getAllCities(): array
    {
        return $this->cityRepository->findBy([], ['name' => 'ASC']);
    }
}
