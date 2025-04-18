<?php

namespace App\Dto\GeoJson;

use App\Dto\Coordinate;

class Feature implements \JsonSerializable
{
    private array  $coordinates;
    private string $type;
    private ?int   $statusId;

    public function __construct(array $coordinates, string $type = 'LineString', int $statusId = null) {
        $this->coordinates = $coordinates;
        $this->type        = $type;
        $this->statusId    = $statusId;
    }

    public static function fromCoordinate(Coordinate $coordinate) {
        return new self([$coordinate->longitude, $coordinate->latitude], 'Point');
    }

    public function toArray(): array {
        $response = [
            'type'     => 'Feature',
            'geometry' => [
                'type'        => $this->type,
                'coordinates' => $this->coordinates
            ],
        ];
        if ($this->statusId) {
            $response['properties'] = ['statusId' => $this->statusId];
        }
        return $response;
    }

    public function jsonSerialize(): array {
        return $this->toArray();
    }
}
