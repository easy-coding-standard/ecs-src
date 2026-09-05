<?php

declare(strict_types=1);

namespace Symplify\EasyCodingStandard\Console\Output;

use Symplify\EasyCodingStandard\Console\Style\EasyCodingStandardStyle;
use Symplify\EasyCodingStandard\Contract\Console\Output\OutputFormatterInterface;
use Symplify\EasyCodingStandard\Exception\Configuration\OutputFormatterNotFoundException;

final class OutputFormatterCollector
{
    /**
     * Formats dropped as ECS is a fixer, not a static analyzer; each maps to a still-supported fallback.
     *
     * @var array<string, string>
     */
    private const array REMOVED_FORMATS = [
        'junit' => ConsoleOutputFormatter::NAME,
        'gitlab' => ConsoleOutputFormatter::NAME,
        'checkstyle' => ConsoleOutputFormatter::NAME,
    ];

    /**
     * @var array<string, OutputFormatterInterface>
     */
    private array $outputFormatters = [];

    /**
     * @param OutputFormatterInterface[] $outputFormatters
     */
    public function __construct(
        array $outputFormatters,
        private readonly EasyCodingStandardStyle $easyCodingStandardStyle
    ) {
        foreach ($outputFormatters as $outputFormatter) {
            $this->outputFormatters[$outputFormatter->getName()] = $outputFormatter;
        }
    }

    public function getByName(string $name): OutputFormatterInterface
    {
        if (isset($this->outputFormatters[$name])) {
            return $this->outputFormatters[$name];
        }

        if (isset(self::REMOVED_FORMATS[$name])) {
            $fallback = self::REMOVED_FORMATS[$name];
            $this->easyCodingStandardStyle->warning(sprintf(
                'The "%s" output format was removed, as ECS is a fixer, not a static analyzer. Falling back to "%s".',
                $name,
                $fallback
            ));

            return $this->outputFormatters[$fallback];
        }

        $outputFormatterKeys = array_keys($this->outputFormatters);

        $errorMessage = sprintf(
            'Output formatter "%s" not found. Use one of: "%s".',
            $name,
            implode('", "', $outputFormatterKeys)
        );
        throw new OutputFormatterNotFoundException($errorMessage);
    }
}
