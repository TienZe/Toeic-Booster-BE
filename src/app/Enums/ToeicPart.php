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
}
