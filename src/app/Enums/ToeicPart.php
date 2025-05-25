<?php

namespace App\Enums;

enum ToeicPart: string
{
   case PART_1 = 'part1';
   case PART_2 = 'part2';
   case PART_3 = 'part3';
   case PART_4 = 'part4';
   case PART_5 = 'part5';
   case PART_6 = 'part6';
   case PART_7 = 'part7';

   public static function values(): array
   {
      return array_column(self::cases(), 'value');
   }

   public static function isListening(string $part): bool
   {
      return in_array($part, array_column([self::PART_1, self::PART_2, self::PART_3, self::PART_4], 'value'));
   }

   public static function isReading(string $part): bool
   {
      return in_array($part, array_column([self::PART_5, self::PART_6, self::PART_7], 'value'));
   }
}
