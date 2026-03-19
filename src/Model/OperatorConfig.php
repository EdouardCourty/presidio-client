<?php

declare(strict_types=1);

namespace Ecourty\PresidioClient\Model;

use Ecourty\PresidioClient\Enum\OperatorType;

final readonly class OperatorConfig
{
    /**
     * @param OperatorType         $type   The anonymization/deanonymization operator (replace, mask, hash, encrypt, etc.)
     * @param array<string, mixed> $params Operator-specific parameters (e.g. "new_value", "masking_char", "key", "hash_type")
     */
    public function __construct(
        private OperatorType $type,
        private array $params = [],
    ) {
    }

    public function getType(): OperatorType
    {
        return $this->type;
    }

    /**
     * @return array<string, mixed>
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = ['type' => $this->type->value];

        if ($this->params !== []) {
            foreach ($this->params as $key => $value) {
                $data[$key] = $value;
            }
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        /** @var string $typeValue */
        $typeValue = $data['type'];
        $type = OperatorType::from($typeValue);

        $params = $data;
        unset($params['type']);

        return new self(
            type: $type,
            params: $params,
        );
    }
}
