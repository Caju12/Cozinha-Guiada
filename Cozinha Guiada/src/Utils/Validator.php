<?php
// ==============================================================================
// PROJETO: Cozinha Guiada
// ARQUIVO: src/Utils/Validator.php
// DESCRIÇÃO: Utilitário de validação, higienização e segurança de formulários
// ATENDE AOS REQUISITOS: RNF11, RNF12, RNF17
// ==============================================================================

class Validator {
    /**
     * Sanitiza strings removendo tags HTML maliciosas contra ataques XSS (RNF11)
     */
    public static function sanitizeString(?string $value): string {
        if ($value === null) return '';
        return trim(strip_tags($value));
    }

    /**
     * Valida formato de endereço de e-mail
     */
    public static function isValidEmail(string $email): bool {
        return (bool)filter_var(trim($email), FILTER_VALIDATE_EMAIL);
    }

    /**
     * Valida comprimento mínimo de senha
     */
    public static function isValidPassword(string $password, int $minLength = 6): bool {
        return mb_strlen($password) >= $minLength;
    }

    /**
     * Valida se um número está dentro de um intervalo inclusivo
     */
    public static function inRange(int|float $value, int|float $min, int|float $max): bool {
        return $value >= $min && $value <= $max;
    }

    /**
     * Gera slug padronizado a partir de um texto
     */
    public static function slugify(string $text): string {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        return strtolower($text ?: 'n-a');
    }
}
