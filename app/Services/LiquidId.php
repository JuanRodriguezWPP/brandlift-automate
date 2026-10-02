<?php

namespace App\Services;

/**
 * LiquidId
 *
 * Provides reversible, obfuscated alphanumeric ID encoding/decoding
 * (Liquid IDs) so that raw sequential database IDs are never exposed in URLs.
 * Example: ID 9 => 'bl_n37E8'
 */
class LiquidId
{
    private const MOD = 2147483647;    // 2^31 - 1 (Mersenne prime)

    private const PRIME = 1580030173;  // Coprime with MOD

    private const INVERSE = 1989098352; // Modular inverse of PRIME mod MOD

    private const XOR_MASK = 0x5BF03635; // Obfuscation mask

    private const ALPHABET = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    /**
     * Encode an integer ID into a Liquid ID string.
     */
    public static function encode(int $id, string $prefix = 'bl'): string
    {
        if ($id <= 0) {
            return '';
        }

        $step1 = ($id * self::PRIME) % self::MOD;
        $scrambled = $step1 ^ self::XOR_MASK;

        $encoded = self::toBase62($scrambled);
        $encoded = str_pad($encoded, 5, '0', STR_PAD_LEFT);

        return $prefix ? "{$prefix}_{$encoded}" : $encoded;
    }

    /**
     * Decode a Liquid ID string back to an integer ID.
     * Supports both liquid IDs (e.g. 'bl_n37E8', 'n37E8') and plain numeric strings for compatibility.
     */
    public static function decode(?string $liquidId): ?int
    {
        if (! $liquidId) {
            return null;
        }

        $clean = trim($liquidId);

        // Allow fallback for plain numeric IDs
        if (is_numeric($clean) && (int) $clean > 0 && (string) (int) $clean === $clean) {
            return (int) $clean;
        }

        // Strip prefix if present (e.g. bl_...)
        if (str_contains($clean, '_')) {
            $parts = explode('_', $clean);
            $clean = end($parts);
        }

        $scrambled = self::fromBase62($clean);
        if ($scrambled === null) {
            return null;
        }

        $unmasked = $scrambled ^ self::XOR_MASK;
        if ($unmasked < 0) {
            $unmasked += self::MOD;
        }

        $id = (int) (($unmasked * self::INVERSE) % self::MOD);
        if ($id <= 0) {
            return null;
        }

        return $id;
    }

    private static function toBase62(int $num): string
    {
        $base = strlen(self::ALPHABET);
        $result = '';
        while ($num > 0) {
            $rem = $num % $base;
            $result = self::ALPHABET[$rem].$result;
            $num = intdiv($num, $base);
        }

        return $result ?: '0';
    }

    private static function fromBase62(string $str): ?int
    {
        $base = strlen(self::ALPHABET);
        $num = 0;
        for ($i = 0; $i < strlen($str); $i++) {
            $pos = strpos(self::ALPHABET, $str[$i]);
            if ($pos === false) {
                return null;
            }
            $num = $num * $base + $pos;
        }

        return $num;
    }
}
