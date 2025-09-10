<?php
namespace App\Service;

use Symfony\Component\Yaml\Yaml;

class DepartementProvider
{
    private array $departements;

    public function __construct(string $departementsFile)
    {
        $data = Yaml::parseFile($departementsFile);

        $this->departements = $data['parameters']['departements'] ?? [];
    }

    public function getDepartements(): array
    {
        return $this->departements;
    }
}