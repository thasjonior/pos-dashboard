<?php
namespace App\Services;
use DateTime;

enum TimeRange: string
{
    case TODAY = 'today';
    case YESTERDAY = 'yesterday';
    case THIS_WEEK = 'this_week';
    case PREVIOUS_WEEK = 'previous_week';
    case THIS_MONTH = 'this_month';
    case THIS_YEAR = 'this_year';
    case PREVIOUS_MONTH = 'previous_month';
    case PREVIOUS_YEAR = 'previous_year';

    public function getStartDate(): DateTime
    {
        $now = new DateTime();

        return match ($this) {
            self::TODAY => (clone $now)->setTime(0, 0),
            self::YESTERDAY => (clone $now)->modify('-1 day')->setTime(0, 0),
            self::THIS_WEEK => (clone $now)->modify('monday this week')->setTime(0, 0),
            self::PREVIOUS_WEEK => (clone $now)->modify('monday previous week')->setTime(0, 0),
            self::THIS_MONTH => (clone $now)->modify('first day of this month')->setTime(0, 0),
            self::THIS_YEAR => (clone $now)->setDate((int)$now->format('Y'), 1, 1)->setTime(0, 0),
            self::PREVIOUS_MONTH => (clone $now)->modify('first day of previous month')->setTime(0, 0),
            self::PREVIOUS_YEAR => (clone $now)->setDate((int)$now->format('Y') - 1, 1, 1)->setTime(0, 0),
        };
    }

    public function getEndDate(): DateTime
    {
        $now = new DateTime();

        return match ($this) {
            self::TODAY => (clone $now)->setTime(23, 59, 59),
            self::YESTERDAY => (clone $now)->modify('-1 day')->setTime(23, 59, 59),
            self::THIS_WEEK => (clone $now)->modify('sunday this week')->setTime(23, 59, 59),
            self::PREVIOUS_WEEK => (clone $now)->modify('sunday previous week')->setTime(23, 59, 59),
            self::THIS_MONTH => (clone $now)->modify('last day of this month')->setTime(23, 59, 59),
            self::THIS_YEAR => (clone $now)->setDate((int)$now->format('Y'), 12, 31)->setTime(23, 59, 59),
            self::PREVIOUS_MONTH => (clone $now)->modify('last day of previous month')->setTime(23, 59, 59),
            self::PREVIOUS_YEAR => (clone $now)->setDate((int)$now->format('Y') - 1, 12, 31)->setTime(23, 59, 59),
        };
    }

    public function getPreviousTimeRange(): ?TimeRange
    {
        return match ($this) {
            self::TODAY => self::YESTERDAY,
            self::YESTERDAY => null,
            self::THIS_WEEK => self::PREVIOUS_WEEK,
            self::PREVIOUS_WEEK => null,
            self::THIS_MONTH => self::PREVIOUS_MONTH,
            self::PREVIOUS_MONTH => null,
            self::THIS_YEAR => self::PREVIOUS_YEAR,
            self::PREVIOUS_YEAR => null,
        };
    }
}